<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Gallery;
use \Datetime;

class GalleryController extends Controller
{

    /**
     * @Route("/gallery", name="getGalleryLists")
     * @Method({"GET","POST"})
     */
    public function galleryActions(Request $request)
    {   
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser()->getId();
        
        $galleries = $em->getRepository('AppBundle:Gallery')
                            ->findBy(
                                ['createdBy' => $user],
                                ['createdAt' => 'DESC']
                            );
                        
        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $name = $data['name'];

            $slug = $this->slugify($name);
            
            $date = new Datetime();
            try{
                $gallery = new Gallery();
                $gallery->setName($name);
                $gallery->setSlug($slug);                
                $gallery->setCreatedBy($user);                
                $gallery->setCreatedAt($date);
                $gallery->setUpdatedAt($date);                
                $gallery->setEnabled(true);
                
                $em = $this->getDoctrine()->getManager();
                $em->persist($gallery);
                $em->flush();

                return $this->redirect('/gallery');
            } catch(\Doctrine\DBAL\DBALException $e) {
                $this->get('session')->getFlashBag()->add('error', 'Can\'t insert entity.');

            }
        }

        return $this->render('default/gallery.html.twig', array(
            'galleries' => $galleries
        ));

    }

    /**
     * @Route("/gallery/{slug}", name="galleryOne")
     */
    public function show($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser()->getId();
        
        $gallery = $em->getRepository('AppBundle:Gallery')
                            ->findOneBy(
                                ['slug' => $slug],
                                ['createdAt' => 'DESC']                   
                            );
        if($gallery->getCreatedBy() === $user){
            return $this->render('default/gallery-one.html.twig', array(
                'gallery' => $gallery
            ));
        }
    }

    static public function slugify($text)
    {
      // replace non letter or digits by -
      $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    
      // transliterate
      $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    
      // remove unwanted characters
      $text = preg_replace('~[^-\w]+~', '', $text);
    
      // trim
      $text = trim($text, '-');
    
      // remove duplicate -
      $text = preg_replace('~-+~', '-', $text);
    
      // lowercase
      $text = strtolower($text);
    
      if (empty($text)) {
        return 'n-a';
      }
    
      return $text;
    }

}
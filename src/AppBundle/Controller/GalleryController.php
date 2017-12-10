<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Gallery;
use AppBundle\Entity\Image;
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
                                ['owned_by' => $user],
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
                $gallery->setOwnedBy($user);                
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

        /**
         * Get all gallery owned_by = user_id
         * Get all image_ids from GalleryMedia where gallery_id = gallery_id
         * Get all images where in images_ids
         */
        $gallery = $em->getRepository('AppBundle:Gallery')
            ->findOneBy(
                ['slug' => $slug],
                ['createdAt' => 'DESC']                   
            );
        $galleryId = $gallery->getId();
        
        
        //$images = $this->get('sonata.media.manager.media')->find(7);
        // $images = $this->get('sonata.media.manager.media')->findOneBy(array('providerReference' => 'd93010ef2e96c4b25d19c0cf426ab89902bfadcd.jpeg'));
        
                // $images = $this->get('sonata.media.manager   .media')->findAll();
        // print_r($images->getId());
        // print_r(sizeof($images));
        // for ($i=0; $i < ; $i++) { 
        //     # code...
        // }
        // foreach ($images as $key => $value) {
            # code...
            // print_r($value->id);
            // echo "{$key} => {$value} ";            
        // }
        // print_r($images);

        // $images = $em->getRepository('AppBundle:Image')
        //         ->findBy(
        //             ['galleryId' => $galleryId],
        //             ['createdAt' => 'DESC']                   
        //         );
        
        /**
         * get all media_id  
         */

        $images = null;
        if($gallery->getOwnedBy() === $user){
            return $this->render('default/gallery-one.html.twig', array(
                'gallery' => $gallery,
                'images' => $images,                
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
<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Gallery;
use AppBundle\Entity\Image;
use AppBundle\Entity\GalleryMedia;
use Application\Sonata\MediaBundle\Entity\Media;
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
        $gallery_id = $gallery->getId();
        
        // fetch all gallery_media and get all gallery_ids
        $gallery_media_fetch = $em->getRepository('AppBundle:GalleryMedia')
            ->findAll();    
        
        // push alle image ids where belong to this gallery
        // #imageIdArray
        $imageIdInGallery = array();            
        foreach ($gallery_media_fetch as $key => $value) {
            $stringGalleryId = $value->getGalleryId();
            $arrayGalleryId = unserialize( $stringGalleryId );

            $media_id = $value->getMediaId();
            
            if (in_array($gallery_id, $arrayGalleryId)) {
                array_push($imageIdInGallery, $media_id);                
            }                
        }
        
        // populate alle images from media__media where id included #imageIdArray
        $images = array(); 
        for ($i=0; $i <sizeof($imageIdInGallery); $i++) { 
            $images[$i] = $this->get('sonata.media.manager.media')
                ->findOneBy(
                    ['id'=>$imageIdInGallery[$i]]
                );
        }

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
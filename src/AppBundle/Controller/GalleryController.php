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
    function status($code){
        $message = array(
            301=>'Accepted image file extensions: jpg, jpeg.',
            302=>'Broken one or some of uploaded images.',
        );
        return $message[$code];
    }

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
        
        $createdAt = array();
        foreach ($galleries as $key => $value) {
            $created_At = $this->time_elapsed_string($value->getCreatedAt()->format("Y-m-d H:i:s"));
            array_push($createdAt, $created_At);
        }

        $gallerie_medias = $em->getRepository('AppBundle:GalleryMedia')
            ->findBy(
                ['owned_by' => $user],
                ['createdAt' => 'DESC']
            );
        
        $images = array();
        foreach ($gallerie_medias as $key => $value) {
            $images[$key] = $this->get('sonata.media.manager.media')
            ->findOneBy(
                ['id'=>$value->getMediaId()]
            );
        }
        
        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $name = $data['name'];

            $slug = $this->slugify($name);
            
            $gallery = $em->getRepository('AppBundle:Gallery')
                ->findOneBy(
                    ['slug' => $slug]
                );

            $date = new Datetime();

            if($gallery){
                $slug = $slug.'-'.$date->getTimestamp();
            }

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

        return $this->render('default/galleries.html.twig', array(
            'galleries' => $galleries,
            'images' => $images,
            'createdAt' => $createdAt
        ));

    }

    /**
     * @Route("/gallery/delete/{id}", name="galleryDelete")
     */
    public function deleteGallery($id){
        $em = $this->getDoctrine()->getManager();

        $gallerie_medias = $em->getRepository('AppBundle:GalleryMedia')
            ->findBy(
                ['gallery_id' => $id],
                ['createdAt' => 'DESC']
            );
        
        $gallery = $em->getRepository('AppBundle:Gallery')
                ->findOneBy(
                    ['id' => $id]
                );
        try{
            $query = $em->createQuery('DELETE AppBundle:GalleryMedia gm WHERE gm.gallery_id = '.$id);
            $query->execute(); 

            try{
                if(!empty($gallery)){
                    $em->remove($gallery);
                    $em->flush();
                }
            } catch(\Doctrine\DBAL\DBALException $e) {
                $this->get('session')->getFlashBag()->add('error', 'Can\'t delete gallery.');
            }

        } catch(\Doctrine\DBAL\DBALException $e) {
            $this->get('session')->getFlashBag()->add('error', 'Can\'t delete media.');
        }

        return $this->redirect('/gallery');
    }

    /**
     * @Route("/gallery/image/delete/{media_id}/{slug}", name="imageGalleryDelete")
     */
    public function deleteImage($media_id, $slug){
        $em = $this->getDoctrine()->getManager();

        $gallery = $em->getRepository('AppBundle:Gallery')
            ->findOneBy(
                ['slug' => $slug],
                ['createdAt' => 'DESC']                   
            );
        $gallery_id = $gallery->getId();

        $gallery_media = $em->getRepository('AppBundle:GalleryMedia')
            ->findOneBy(
                ['gallery_id' => $gallery_id, 'media_id'=>$media_id]
            ); 

        if (!$gallery_media) {
            throw $this->createNotFoundException(
                'No Image found for id '.$id
            );
        }else{
            // Remove image will remove only image in GalleryMedia by its media_id
            $em->remove($gallery_media);
            $em->flush();
        }

        return $this->redirect('/gallery/'.$slug);   
    }

    /**
     * @Route("/gallery/{slug}", name="galleryOne")
     */
    public function show(Request $request,$slug)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser()->getId();
        
        $message = null;
        $status = 'default';

        $query = $request->query->all();
        if(count($query)>0){
            $status = $query['status'];
            $message = $this->status($query['code']); 
        }

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
        
        // fetch all gallery_media
        $gallery_media_fetch = $em->getRepository('AppBundle:GalleryMedia')
            ->findBy(
                ['gallery_id' => $gallery_id],
                ['createdAt' => 'DESC']                   
            ); 
        
        // push alle image ids where belong to this gallery
        // #imageIdArray
        $imageIdInGallery = array();            
        foreach ($gallery_media_fetch as $key => $value) {
            $media_id = $value->getMediaId();
            array_push($imageIdInGallery, $media_id); 
        }
        
        // populate alle images from media__media where id included #imageIdArray
        $images = array(); 
        $images_size = array();
        for ($i=0; $i <sizeof($imageIdInGallery); $i++) { 
            $images[$i] = $this->get('sonata.media.manager.media')
                ->findOneBy(
                    ['id'=>$imageIdInGallery[$i]]
                );
            
            if ($images[$i]) {
                $size = $this->filesize_formatted($images[$i]->getSize());
                array_push($images_size, $size); 
            }
        }

        $createdAt = array();
        if ($images) {
            foreach ($images as $key => $value) {
                $created_At = $this->time_elapsed_string($value->getCreatedAt()->format("Y-m-d H:i:s"));
                array_push($createdAt, $created_At);
            }
        }
        
        if($gallery->getOwnedBy() === $user){
            return $this->render('default/gallery.html.twig', array(
                'gallery' => $gallery,
                'images' => $images,
                'slug' => '/gallery/'.$slug,
                'createdAt' => $createdAt,
                'images_size'=>$images_size,
                'message'=> $message,
                'status' => $status           
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

    public function filesize_formatted($size)
    {
        $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }

    function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);
    
        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;
    
        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }
    
        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

}
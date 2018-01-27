<?php


namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Mail;
use AppBundle\Form\MailType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class ShopownerController extends Controller
{
    /**
     * @Route("/photographers/shopowner/", name="myshopowner")
     */
    public function myShopownerAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $shopownerID = $this->getUser()->getId();

        $shopowner = $em->getRepository('AppBundle:Photographers')->findOneBy(
            ['userID' => $shopownerID]
        );

        $products = $em->getRepository('AppBundle:Product')->findBy(
            ['owned_by' => $shopownerID,
                'enabled'=>true]
        );

        foreach ($products as $product){
            $galleries = $product->getGallery();
            $imageIdInGallery = array();
            foreach ($galleries as $key => $value) {
                // fetch all gallery_media
                $gallery_media_fetch = $em->getRepository('AppBundle:GalleryMedia')
                ->findBy(
                    ['gallery_id' => $value],
                    ['createdAt' => 'DESC']                   
                );
    
                foreach ($gallery_media_fetch as $key => $value) {
                    $media_id = $value->getMediaId();
                    array_push($imageIdInGallery, $media_id); 
                }
            }
            $product->setImage($imageIdInGallery[0]);
        }

        return $this->render('default/shopowner.html.twig', array(
            'shopowner' => $shopowner,
            'products' => $products,
            'numberOfProducts' => count($products)
        ));
    }

    /**
     * @Route("/photographers/shopowner/{shopownerID}", name="shopowner")
     */
    public function shopownerAction(Request $request, $shopownerID)
    {
        $em = $this->getDoctrine()->getManager();

        $shopowner = $em->getRepository('AppBundle:Photographers')->findOneBy(
            ['userID' => $shopownerID]
        );

        $products = $em->getRepository('AppBundle:Product')->findBy(
            ['owned_by' => $shopownerID,
             'enabled'=>true]
        );

        foreach ($products as $product){
            $galleries = $product->getGallery();
            $imageIdInGallery = array();
            foreach ($galleries as $key => $value) {
                // fetch all gallery_media
                $gallery_media_fetch = $em->getRepository('AppBundle:GalleryMedia')
                ->findBy(
                    ['gallery_id' => $value],
                    ['createdAt' => 'DESC']                   
                );

                foreach ($gallery_media_fetch as $key => $value) {
                    $media_id = $value->getMediaId();
                    array_push($imageIdInGallery, $media_id); 
                }
            }
            $product->setImage($imageIdInGallery[0]);
        }

        

        return $this->render('default/shopowner.html.twig', array(
            'shopowner' => $shopowner,
            'products' => $products,
            'numberOfProducts' => count($products)
        ));
    }

    public function getPreviewImgPathForProduct($product)
    {
        $em = $this->getDoctrine()->getManager();
        $mediaIds = $em->getRepository('AppBundle:GalleryMedia')->findOneBy(
            ['gallery_id' => $product->getGallery()]
        );
        $media = $this->get('sonata.media.manager.media')->findBy(
            ['id' => $mediaIds->getMediaId()]
		);
		
        return $media[0]->getProviderReference();
	}

}
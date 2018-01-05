<?php


namespace AppBundle\Controller;

use AppBundle\Entity\KursTermine;
use AppBundle\Entity\KursTermineTemp;
use AppBundle\Entity\Markers;
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
     * @Route("/photographers/shopowner/{shopownerID}", name="shopowner")
     */
    public function shopownerAction(Request $request, $shopownerID)
    {
        $em = $this->getDoctrine()->getManager();

        $shopowner = $em->getRepository('AppBundle:Photographers')->findOneBy(
            ['userID' => $shopownerID]
        );

        $products = $em->getRepository('AppBundle:Product')->findBy(
            ['owned_by' => $shopownerID]
        );


        foreach ($products as $product){
            $product->setImage($this->getPreviewImgPathForProduct($product));
        }



        return $this->render('default/shopowner.html.twig', array(
            'shopowner' => $shopowner,
            'products' => $products
        ));
    }

    public function getPreviewImgPathForProduct($product)
    {
        $em = $this->getDoctrine()->getManager();
        $mediaIds = $em->getRepository('AppBundle:GalleryMedia')->findBy(
            ['gallery_id' => $product->getGallery()]
        );

        $media = $this->get('sonata.media.manager.media')->findBy(
            ['id' => $mediaIds]
        );

        return $media[0]->getProviderReference();
    }


}
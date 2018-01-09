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

        return $this->render('default/shopowner.html.twig', array(
            'shopowner' => $shopowner,
            'products' => $products,
            'numberOfProducts' => count($products)
        ));
    }

}
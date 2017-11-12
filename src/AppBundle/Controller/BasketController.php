<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Form\MailType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class BasketController extends Controller
{

    /**
     * @Route("/basket", name="basket")
     */
    public function basketAction(Request $request)
    {
        $basketItems = array("12345uztukgsedgr", "ewsrgjhu45erg", "324z5hrtdv", "123rwetrz5urhsfdsfvc");
        $json = json_encode($basketItems);
        $cookie = new Cookie('basket', $json, time() + (3600 * 48));
        
        $cookie = $request->cookies->get('basket');
        $cookie = stripslashes($cookie);
        $basketItems = json_decode($cookie, true);

        $response = $this->render('default/basket.html.twig' , array(
            'basketItems' => $basketItems
        ));

        //$response->headers->setCookie($cookie);

        return $response;
    }


    public function addBasketItem(Request $request)
    {
        
    }
}
<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Form\MailType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class BasketController extends Controller
{

    /**
     * @Route("/basket", name="getBasket")
     * @Method({"GET"})
     */
    public function listBasketItems(Request $request)
    {        
        $basketItems = $this->getCookieContent($request->cookies->get('basket'));

        $response = $this->render('default/basket.html.twig' , array(
            'basketItems' => $basketItems
        ));;

        return $response;
    }

    /**
     * @Route("/basket/{id}", name="PostBasket")
     * @Method({"POST"})
     */
    public function addBasketItem(Request $request, $id)
    {
        //TODO: check in database if id exists
        //TODO: ids have to be unique in the basket

        $response = new Response();
        $response->setStatusCode(Response::HTTP_OK);

        $basketItems = $this->getCookieContent($request->cookies->get('basket'));

        if (!$basketItems)
        {
            $basketItems = array($id);
        }
        else
        {
            array_push($basketItems, $id);
        }

        $response->headers->setCookie($this->createCookie($basketItems, "basket"));

        return $response;
    }

    /**
     * @Route("/basket/{id}", name="DeleteBasket")
     * @Method({"DELETE"})
     */
    public function deleteBasketItem(Request $request, $id)
    {
        $response = new Response();
        $basketItems = $this->getCookieContent($request->cookies->get('basket'));
        if (!$basketItems) //if there is no basket
        {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $response;
        }

        $basketItemKey = array_search($id, $basketItems);
        if (!$basketItemKey) //if the item could not be found
        {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $response;
        }

        array_splice($basketItems, $basketItemKey, 1);

        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->setCookie($this->createCookie($basketItems, "basket"));

        return $response;
    }

    private function getCookieContent($cookie)
    {
        $cookie = stripslashes($cookie);
        $basketItems = json_decode($cookie, true);
        return $basketItems;
    }

    private function createCookie($array , $key)
    {
        $json = json_encode($array);
        $cookie = new Cookie($key, $json, time() + (3600 * 48));
        return $cookie;
    }

}
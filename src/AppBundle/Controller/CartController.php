<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class CartController extends Controller
{

    /**
     * @Route("/cart", name="get")
     * @Method({"GET"})
     */
    public function cartLists(Request $request)
    {        
        return $this->render(
            'default/cart.html.twig'
        );

    }

    /**
     * @Route("/cart/{id}", name="post")
     * @Method({"POST"})
     */
    public function addCart(Request $request, $id)
    {
        $response = new Response();
        return $response;
    }

    /**
     * @Route("/cart/{id}", name="delete")
     * @Method({"DELETE"})
     */
    public function deleteCart(Request $request, $id)
    {
        $response = new Response();
        return $response;
    }

}
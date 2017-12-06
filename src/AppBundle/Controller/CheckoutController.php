<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Form\MailType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Product;

class CheckoutController extends Controller
{

    /**
     * @Route("/checkout", name="getCheckout")
     * @Method({"GET"})
     */
    public function listBasketItems(Request $request)
    {        
        $cookie = $this->getCookieContent($request->cookies->get('basket'));
        $basketItems = array();

        print_r($cookie);
        if ($cookie) {
            foreach ($cookie as $id) {
                $product = $this->getDoctrine()
                    ->getRepository(Product::class)
                    ->find($id);
                $item = array(
                    'id' => $product->getId(),
                    'name' => $product->getImage()->getName(),
                    'price' => $product->getPrice(),
                    'image' => $product->getImage(),
                );
                array_push($basketItems, $item);
            }
        }

        return $this->render(
            'default/basket.html.twig', 
            array(
                'basketItems' => $basketItems
            )
        );

    }

    /**
     * @Route("/checkout/{id}", name="postCheckout")
     * @Method({"POST"})
     */
    public function addBasketItem(Request $request, $id)
    {
        $response = new Response();

        $product = $this->getDoctrine()
            ->getRepository(Product::class)
            ->find($id);

        if (!$product) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $response;
        }

        $basketItems = $this->getCookieContent($request->cookies->get('basket'));
        if (!$basketItems)
        {
            $basketItems = array();
        }

        $basketItemKey = array_search($id, $basketItems); // search for item
        if ($basketItemKey === false) // cannot use shortform because we also have to check the type 
        {
            array_push($basketItems, $id);
            $response->setStatusCode(Response::HTTP_OK);
            $response->headers->setCookie($this->createCookie($basketItems, "basket"));
        }
        else
        {
            $response->setStatusCode(Response::HTTP_PRECONDITION_FAILED);
        }

        return $response;
    }

    /**
     * @Route("/checkout/{id}", name="deleteCheckout")
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

        $basketItemKey = array_search($id, $basketItems); // search for item
        if ($basketItemKey === false) // cannot use shortform because we also have to check the type 
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
<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Purchase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Form\MailType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Product;
use \Datetime;

class CheckoutController extends Controller
{

    /**
     * @Route("/checkout", name="checkout")
     * @Method({"GET"})
     */
    public function getCheckout(Request $request)
    {   
        $cookie = $this->getCookieContent($request->cookies->get('cart'));
        $cartItems = array();

        $sum = 0;
        if ($cookie)
        {
            foreach ($cookie as $id)
            {
                $product = $this->getDoctrine()
                    ->getRepository(Product::class)
                    ->find($id);

                $item = null;
                if ($product->getImage())
                {   
                    $item = $product->getImage();
                }else{
                    $product->setImage($this->getPreviewImgPathForProduct($product));
                }

                if ($product->getGallery())
                {
                    //$item = $product.getGallery();
                }

                $sum += $product->getPrice();
                $item = array(
                    'id' => $product->getId(),
                    'name' => $product->getName(), //TODO: Get name of image or gallery
                    'price' => $product->getPrice(),
                    'image' => $product->getImage(), //TODO: Get image or first image of gallery
                );
                array_push($cartItems, $item);
            }
        }

        return $this->render(
            'default/checkout.html.twig',
            array(
                'cartItems' => $cartItems,
                'sum' => $sum
            )
        );

    }

    /**
     * @Route("/post_checkout", name="postCheckout")
     */
    public function postCheckout(Request $request)
    {
        $response = new Response();

        //TODO: Check if logged in and redirect if necessary

        $cartItems = $this->getCookieContent($request->cookies->get('cart'));
        if (!$cartItems)
        {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $response;
        }

        $checkoutItems = array();

        foreach ($cartItems as $cartItem)
        {
            $product = $this->getDoctrine()
                ->getRepository(Product::class)
                ->find($cartItem);

            if ($product)
            {
                array_push($checkoutItems, $product);
            }
        }

        $datetime = new Datetime();
        $usr = $this->get('security.context')->getToken()->getUser();
        $purchase = new Purchase();
        $sum = 0;

        foreach ($checkoutItems as $product)
        {
            $sum += $product->getPrice();
            $purchase->getProducts()->add($product);
        }
        $purchase->setDateTime($datetime);
        $purchase->setSum($sum);
        $purchase->setIsPaid(false);
        $purchase->setUser($usr->getId());

        $em = $this->getDoctrine()->getManager();
        $em->persist($purchase);
        $em->flush();

        $response = $this->render(
            'default/purchase_success.html.twig'
        );
        $response->headers->setCookie($this->createCookie(array(), "cart"));

        return $response;

    }

    /**
     * @Route("/post_checkout/{id}", name="postCheckoutItem")   
     * @Method({"POST"})
     */
    public function postCheckoutItem(Request $request, $id)
    {
        $response = new Response();

        $product = $this->getDoctrine()
            ->getRepository(Product::class)
            ->find($id);

        if (!$product)
        {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $response;
        }

        $cartItems = $this->getCookieContent($request->cookies->get('cart'));
        if (!$cartItems)
        {
            $cartItems = array();
        }

        $cartItemKey = array_search($id, $cartItems); // search for item
        if ($cartItemKey === false) // cannot use shortform because we also have to check the type
        {
            array_push($cartItems, $id);
            $response->setStatusCode(Response::HTTP_OK);
            $response->headers->setCookie($this->createCookie($cartItems, "cart"));
            $response->send();
        }
        else
        {
            $response->setStatusCode(Response::HTTP_PRECONDITION_FAILED);
            $response->send();
        }

        return $response;
    }

    /**
     * @Route("/checkout_delete/{id}", name="deleteCheckoutItem")
     * @Method({"DELETE"})
     */
    public function deleteCheckoutItem(Request $request, $id)
    {
        $response = new Response();
        $cartItems = $this->getCookieContent($request->cookies->get('cart'));
        if (!$cartItems) //if there is no cart
        {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $response;
        }

        $cartItemKey = array_search($id, $cartItems); // search for item
        if ($cartItemKey === false) // cannot use shortform because we also have to check the type 
        {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            return $response;
        }

        array_splice($cartItems, $cartItemKey, 1);

        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->setCookie($this->createCookie($cartItems, "cart"));

        return $response;
    }

    private function getCookieContent($cookie)
    {
        $cookie = stripslashes($cookie);
        $cartItems = json_decode($cookie, true);
        return $cartItems;
    }

    private function createCookie($array , $key)
    {
        $json = json_encode($array);
        $cookie = new Cookie($key, $json, time() + (3600 * 48));
        return $cookie;
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
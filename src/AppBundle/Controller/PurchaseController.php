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

class PurchaseController extends Controller
{

    /**
     * @Route("/purchase", name="purchase")
     *
     */
    public function getCheckout(Request $request)
    {
        $usr = $this->get('security.context')->getToken()->getUser();

        $purchases = $this->getDoctrine()
            ->getRepository(Purchase::class)
            ->findBy( ['user' => $usr->getId()]);



        return $this->render(
            'default/purchase.html.twig',
            array(
                'purchases' => $purchases
            )
        );

    }


}
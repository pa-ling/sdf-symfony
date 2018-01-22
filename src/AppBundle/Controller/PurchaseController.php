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
    public function getPurchaseAsCustomer(Request $request)
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

    /**
     * @Route("/purchaseasseller", name="purchaseasseller")
     */
    public function getPurchaseAsSeller(Request $request)
    {
        $usrId = $this->get('security.context')->getToken()->getUser()->getId();

        $purchases = $this->getDoctrine()
            ->getRepository(Purchase::class)
            ->findAll();

        $purchsesByCustomer = array();

        foreach ($purchases as $purchase){
            if($purchase->getProducts()[0]->getOwnedBy() == $usrId ){
                array_push($purchsesByCustomer, $purchase);
            }
        }

        return $this->render(
            'default/sales.html.twig',
            array(
                'purchases' => $purchsesByCustomer
            )
        );
    }

    /**
     * @Route("/purchaseasseller/{id}", name="purchaseassellerid")
     */
    public function setPurchaseAsPaidById(Request $request, $id)
    {
        $purchase = $this->getDoctrine()
            ->getRepository(Purchase::class)
            ->findOneBy([ 'id' => $id]);

        $purchase->setIsPaid(true);

        $em = $this->getDoctrine()->getManager();
        $em->persist($purchase);
        $em->flush();

        return $this->redirectToRoute('purchaseasseller');
    }

}
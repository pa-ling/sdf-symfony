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
            'default/purchaseSeller.html.twig',
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
        // Get purchase data
        $purchase = $this->getDoctrine()
            ->getRepository(Purchase::class)
            ->findOneBy([ 'id' => $id]);

        if (!$purchase) {
            throw $this->createNotFoundException(
                'No purchase found for id '.$id
            );
        }

        // Get user data
        $userId = $purchase->getUser();
        $user = $this->get('fos_user.user_manager')->findOneBy(
            ['id' => $userId]
        );
        
        if (!$user) {
            throw $this->createNotFoundException(
                'No product found for id '.$product->Id
            );
        }

        // Params for email
        $app_url = $this->getParameter('app_url');
        $link = $app_url.'/purchase';
        $username = $user->getUsername();
        $email = $user->getEmail();

        // Update purchase paid status
        $purchase->setIsPaid(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($purchase);
        $em->flush();

        // Set email content
        $swiftMailer = (new \Swift_Message('Purchase Product'))
                ->setFrom('yoggifirmanda@gmail.com')
                ->setTo($email)
                ->setBody(
                    $this->renderView(
                        'emails/purchase_product.html.twig',
                        array('username' => $username, 'link'=> $link)
                    ),
                    'text/html'
                );
        
        // Send email
        $this->get('mailer')->send($swiftMailer);

        return $this->redirectToRoute('purchaseasseller');
    }

}
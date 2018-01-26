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
use ZipArchive;

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

    /**
     * @Route("/download_purchase", name="download_purchase")
     * 
     */
    public function downloadPurchase(Request $request)
    {
        $data = $request->request->all();
        $userId =  $data['userId'];
        $purchaseId =  $data['purchaseId'];

        // Get purchase data
        $purchase = $this->getDoctrine()
            ->getRepository(Purchase::class)
            ->findOneBy([ 'id' => $purchaseId, 'user'=>$userId]);

        if (!$purchase) {
            throw $this->createNotFoundException(
                'No purchase found for id '.$purchaseId
            );
        }

        $product = $purchase->getProducts()[0];

        // Check if purchase already paid
        $isPaid = $purchase->isPaid();

        if($isPaid===true){
            $productId = $product->getId();
            $product = $this->getDoctrine()
                ->getRepository('AppBundle:Product')
                ->findOneBy(
                    ['id' => $productId]
                );

            if (!$product) {
                throw $this->createNotFoundException(
                    'No product found for id '.$productId
                );
            }

            $galleries = $product->getGallery();
			$imageIdInGallery = array();
			foreach ($galleries as $key => $value) {
				// fetch all gallery_media
                $gallery_media_fetch = $this->getDoctrine()
                ->getRepository('AppBundle:GalleryMedia')
				->findBy(
					['gallery_id' => $value],
					['createdAt' => 'DESC']                   
				);
            
				foreach ($gallery_media_fetch as $key => $value) {
					$media_id = $value->getMediaId();
					array_push($imageIdInGallery, $media_id); 
				}
            }
            
            $app_url = $this->getParameter('app_url');
            $images = array(); 
            $images_url = array();
			for ($i=0; $i <sizeof($imageIdInGallery); $i++) { 
				$images[$i] = $this->get('sonata.media.manager.media')
					->findOneBy(
						['id'=>$imageIdInGallery[$i]]
                    );
                $images_url[$i] = $app_url.'/uploads/media/default/0001/01/'.$images[$i]->getProviderReference();
            }

            /**
             * TODO
             * Download as ZIP
             */
            $files = $images_url;
            $zipname = $userId.'-purchase.zip';
            $zip = new ZipArchive;
            $zip->open($zipname, ZipArchive::CREATE);
            foreach ( $files as $file) {
                $zip->addFile($file);
            }
            $zip->close();

            return $this->redirect('/purchase');
        }

    }
}
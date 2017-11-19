<?php

namespace AppBundle\Controller;

use AppBundle\Entity\KursTermine;
use AppBundle\Entity\KursTermineTemp;
use AppBundle\Entity\Markers;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Mail;
use AppBundle\Form\MailType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class FotografenController extends Controller
{

    /**
     * @Route("/fotografen", name="fotografen")
     */
    public function partnerAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $fotografenAll = $em->getRepository('AppBundle:Fotografen')->findAll();

        return $this->render('default/fotografen.html.twig', array(
            'fotografenAll' => $fotografenAll
        ));
    }
}
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

class DefaultController extends Controller
{

    /**
     * @Route("/", name="photographers")
     */
    public function partnerAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $photographersAll = $em->getRepository('AppBundle:Photographers')->findAll();

        return $this->render('default/index.html.twig', array(
            'photographersAll' => $photographersAll
        ));
    }
}
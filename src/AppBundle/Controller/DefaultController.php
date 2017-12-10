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
use AppBundle\Entity\Image;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="home")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $homeAll = $em->getRepository('AppBundle:Home')->findAll();

        // $images = $em->getRepository('AppBundle:Image')
        //     ->findBy(
        //         ['enabled'=>1],
        //         ['createdAt' => 'DESC'],
        //         6
        //     );
        
        $images = $this->get('sonata.media.manager.media')
            ->findBy(
                ['enabled'=>0],
                ['createdAt' => 'DESC'],
                6
            );

        if (!$images) {
            throw $this->createNotFoundException(
                'No images found'
            );
        }
        
        return $this->render('default/index.html.twig', array(
            'homeAll' => $homeAll,
            'images' => $images
        ));

    }
}
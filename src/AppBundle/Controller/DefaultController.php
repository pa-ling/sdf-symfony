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
use AppBundle\Entity\UserData;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="photographers")
     */
    public function partnerAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $users = $this->get('fos_user.user_manager')->findAll();

        $photographers = array();
        if($users){
            foreach($users as $user){
                $roles = $user->getRoles();
                if( in_array( "ROLE_PHOTOGRAPH" ,$roles ) )
                {
                    $userData = $em->getRepository('AppBundle:UserData')
                        ->findOneBy(
                            ['userid' => $user->getId()]
                        );
                    array_push($photographers,$userData);
                }
            }
        }

        return $this->render('default/index.html.twig', array(
            'photographers' => $photographers
        ));
    }
}
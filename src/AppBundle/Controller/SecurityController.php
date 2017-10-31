<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class SecurityController extends Controller
{
    /**
     * @Route("/secure/login", name="login")
     */
    public function loginAction(Request $request)
    {

        $message = null;
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();

        if ($error) {
            $message = "Name oder Passwort waren falsch!";
        }

        return $this->render(
            'security/login.html.twig',
            array(
                'message' => $message
            )
        );
    }

}
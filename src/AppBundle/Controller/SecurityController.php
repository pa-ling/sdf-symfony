<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


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

    /**
     * @Route("/forgot/password", name="forgot_password")
     * @Method({"GET","POST"})
     */
    public function forgotPassword(Request $request)
    {
        $message= null;

        $app_url = $this->getParameter('app_url');

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $email = $data['email'];

            $user = $this->get('fos_user.user_manager')->findOneBy(
                ['email' => $email]
            );

            if (!$user) {
                throw $this->createNotFoundException(
                    'No product found for id '.$product->Id
                );
            }

            $salt = $user->getSalt();
            $username = $user->getUsername();

            $link = $app_url.'/reset/password/'.$email.'/'.$salt;

            $swiftMailer = (new \Swift_Message('Forgot Password'))
                ->setFrom('yoggifirmanda@gmail.com')
                ->setTo($email)
                ->setBody(
                    $this->renderView(
                        'emails/forgot_password.html.twig',
                        array('username' => $username, 'link'=> $link)
                    ),
                    'text/html'
                )
            ;

            $this->get('mailer')->send($swiftMailer);
        
            $message = 'Please check your email. If you do not receive email from us, please submit again';
            
            return $this->render('emails/info.html.twig',
                array(
                    'message' => $message
                )    
            );
        }

        return $this->render('security/forgot_password.html.twig',
            array(
                'message' => $message
            )    
        );
    }


    /**
     * @Route("/reset/password/{email}/{salt}", name="reset_password")
     */
	public function resetPassword($email, $salt)
	{      
        $message = null;

        $user = $this->get('fos_user.user_manager')->findOneBy(
            ['email' => $email]
        );

        if (!$user) {
            throw $this->createNotFoundException(
                'No product found for id '.$product->Id
            );
        }

        if($user->getSalt()!==$salt){
            return $this->render('emails/info.html.twig',
                array(
                    'message' => 'Salt not valid'
                )    
            );
        }else{
            return $this->render('security/reset_password.html.twig',
                array(
                    'salt' => $salt,
                    'email' => $email,
                    'message' => $message
                )
            );
        }
    } 

    /**
     * @Route("/change/password", name="change_password")
     */
    public function changePassword(Request $request)
    {
        $message = null;

        $data = $request->request->all();
        $password1 = $data['password1'];
        $password2 = $data['password2'];
        $salt = $data['salt'];
        $email = $data['email'];

        if($password1===$password2){
            $em = $this->getDoctrine()->getManager();
            $user = $this->get('fos_user.user_manager')
                ->findOneBy(
                ['email' => $email]
            );

            if (!$user) {
                throw $this->createNotFoundException(
                    'No product found for id '.$product->Id
                );
            }
        
            $password = $this->get('security.password_encoder')->encodePassword($user, $password1);
            $new_salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
            $user_id = $user->getId();
            
            $user->setSalt($new_salt);
            $user->setPassword($password);
            $em->flush();

            return $this->redirect('/login');
        }else{
            $message = 'Password not matched';

            return $this->render('security/reset_password.html.twig',
                array(
                    'salt' => $salt,
                    'email' => $email,
                    'message' => $message
                )
            );
        }        
    }
}
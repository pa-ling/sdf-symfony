<?php

namespace AppBundle\Controller;

use AppBundle\Form\UserType;
use Application\Sonata\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Photographers;

class RegisterController extends Controller
{
    /**
     * @Route("/signup", name="signup")
     */
    public function userRegisterAction(Request $request)
    {
        $user = new User();

        $form = $this->createForm(new UserType(), $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

                $password = $this->get('security.password_encoder')->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($password);

                $user->setRoles(array('ROLE_USER'));
                $user->addRole('ROLE_USER');
                $user->setEnabled(false);

                $salt = $user->getSalt();
                $username = $user->getUsername();
                $email = $user->getEmail();

                try{
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();

                    $app_url = $this->getParameter('app_url');
                    $link = $app_url.'/user/confirm/'.$email.'/'.$salt;

                    $swiftMailer = (new \Swift_Message('Confirm Registration'))
                        ->setFrom('yoggifirmanda@gmail.com')
                        ->setTo($email)
                        ->setBody(
                            $this->renderView(
                                'emails/user_confirm.html.twig',
                                array('username' => $username, 'link'=> $link)
                            ),
                            'text/html'
                        )
                    ;

                    $this->get('mailer')->send($swiftMailer);
                } catch(\Doctrine\DBAL\DBALException $e) {
                    $this->get('session')->getFlashBag()->add('error', 'Can\'t insert entity.');

                }
                
            return $this->redirect('/login?code=201');
        }

        return $this->render(
            'security/user_register.html.twig',
            array(
                'form' => $form->createView(),

            )
        );
    }

     /**
     * @Route("/user/confirm/{email}/{salt}", name="user_confirm")
     */
	public function condirmUser($email, $salt)
	{      
        $message = null;

        $user = $this->get('fos_user.user_manager')->findOneBy(
            ['email' => $email]
        );

        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for email '.$email
            );
        }

        if($user->getSalt()!==$salt){
            $message = "Salt not valid";
            $status = true;
            return $this->render('emails/info.html.twig',
                array(
                    'message' => $message,
                    'status' => $status
                )    
            );
        }else{
            $user->setEnabled(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $message = "Your account has been activated.";
            $status = true;
            return $this->render('security/login.html.twig',
                array(
                    'message' => $message,
                    'status' => $status
                )
            );
        }
    } 

    /**
     * @Route("/photograph_registration", name="photograph_registration")
     */
    public function photographRegisterAction(Request $request)
    {

        $user = new User();

        $form = $this->createForm(new UserType(), $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $request->request->all();
            $shortDesc = $data['shortDesc'];
            $longDesc = $data['longDesc'];
            $firstname = $data['firstname'];
            $lastname = $data['lastname'];

            $password = $this->get('security.password_encoder')->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setRoles(array('ROLE_PHOTOGRAPH'));
            $user->addRole('ROLE_PHOTOGRAPH');
            $user->setEnabled(true);

            try{
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

            } catch(\Doctrine\DBAL\DBALException $e) {
                $this->get('session')->getFlashBag()->add('error', 'Can\'t insert entity.');

            }

            $photographers = new Photographers();
            $photographers->setUserId($user->getId());
            $photographers->setLongdescr($longDesc);
            $photographers->setShortdescr($shortDesc);
            $photographers->setSurname($firstname);
            $photographers->setFirstname($lastname);

            $em = $this->getDoctrine()->getManager();
            $em->persist($photographers);
            $em->flush();

            return $this->redirect('/login');   
        }

        return $this->render(
            'security/photograph_register.html.twig',
            array(
                'form' => $form->createView(),

            )
        );
    }

}

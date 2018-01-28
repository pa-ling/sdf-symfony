<?php

namespace AppBundle\Controller;

use AppBundle\Form\UserType;
use Application\Sonata\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\UserData;

class RegisterController extends Controller
{

    function status($code){
        $message = array(
            301=>'Username already registered.',
            302=>'Email already registered.'
        );
        return $message[$code];
    }

    /**
     * @Route("/signup", name="signup")
     */
    public function userRegisterAction(Request $request)
    {
        $message = null;
        $status = 'default';

        $user = new User();

        $form = $this->createForm(new UserType(), $user);
        $form->handleRequest($request);

        $query = $request->query->all();
        if(count($query)>0){
            $status = $query['status'];
            $message = $this->status($query['code']); 
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $salt = $user->getSalt();
            $username = strtolower($user->getUsername());
            $email = $user->getEmail();

            $user_name = $this->get('fos_user.user_manager')->findOneBy(
                ['username' => $username]
            );

            if($user_name){
                return $this->redirect('/signup?code=301&status=warning');
            }

            $user_email = $this->get('fos_user.user_manager')->findOneBy(
                ['email' => $email]
            );

            if($user_email){
                return $this->redirect('/signup?code=302&status=warning');
            }

            $password = $this->get('security.password_encoder')->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            $user->setRoles(array('ROLE_USER'));
            $user->addRole('ROLE_USER');
            $user->setEnabled(false);

            try{
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $app_url = $this->getParameter('app_url');
                $link = $app_url.'/user/confirm/'.$email.'/'.$salt;

                $swiftMailer = (new \Swift_Message('[Symfoto] Confirm User Registration'))
                    ->setFrom('yoggifirmanda@gmail.com')
                    ->setTo($email)
                    ->setBody(
                        $this->renderView(
                            'emails/user_confirm.html.twig',
                            array('username' => $username, 'link'=> $link)
                        ),
                        'text/html'
                    );

                $this->get('mailer')->send($swiftMailer);

                return $this->redirect('/login?code=201');

            } catch(\Doctrine\DBAL\DBALException $e) {
                $this->get('session')->getFlashBag()->add('error', 'Can\'t insert entity user.');
            }
        }

        return $this->render(
            'security/user_register.html.twig',
            array(
                'form' => $form->createView(),
                'message'=>$message,
                'status' => $status
            )
        );
    }

    /**
     * @Route("/photograph_registration", name="photograph_registration")
     */
    public function photographRegisterAction(Request $request)
    {
        $message = null;
        $status = 'default';

        $user = new User();

        $form = $this->createForm(new UserType(), $user);
        $form->handleRequest($request);

        $query = $request->query->all();
        if(count($query)>0){
            $status = $query['status'];
            $message = $this->status($query['code']); 
        }
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all();
            $shortDesc = $data['shortDesc'];
            $longDesc = $data['longDesc'];
            $firstname = $data['firstname'];
            $lastname = $data['lastname'];

            $salt = $user->getSalt();
            $username = strtolower($user->getUsername());
            $email = $user->getEmail();

            $user_name = $this->get('fos_user.user_manager')->findOneBy(
                ['username' => $username]
            );

            if($user_name){
                return $this->redirect('/photograph_registration?code=301&status=warning');
            }

            $user_email = $this->get('fos_user.user_manager')->findOneBy(
                ['email' => $email]
            );

            if($user_email){
                return $this->redirect('/photograph_registration?code=302&status=warning');
            }

            $password = $this->get('security.password_encoder')->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setRoles(array('ROLE_PHOTOGRAPH'));
            $user->addRole('ROLE_PHOTOGRAPH');
            $user->setEnabled(false);

            try{
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $app_url = $this->getParameter('app_url');
                $link = $app_url.'/user/confirm/'.$email.'/'.$salt;

                $swiftMailer = (new \Swift_Message('[Symfoto] Confirm Photographer Registration'))
                    ->setFrom('yoggifirmanda@gmail.com')
                    ->setTo($email)
                    ->setBody(
                        $this->renderView(
                            'emails/user_confirm.html.twig',
                            array('username' => $username, 'link'=> $link)
                        ),
                        'text/html'
                    );

                $this->get('mailer')->send($swiftMailer);
                
                try{
                    $userData = new UserData();
                    $userData->setUserId($user->getId());
                    $userData->setLongdescr($longDesc);
                    $userData->setShortdescr($shortDesc);
                    $userData->setFirstname($firstname);
                    $userData->setLastname($lastname);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($userData);
                    $em->flush();

                    return $this->redirect('/login?code=201');

                } catch(\Doctrine\DBAL\DBALException $e) {
                    $this->get('session')->getFlashBag()->add('error', 'Can\'t insert entity photographer.');
                }
            } catch(\Doctrine\DBAL\DBALException $e) {
                $this->get('session')->getFlashBag()->add('error', 'Can\'t insert entity user.');
            }
        }

        return $this->render(
            'security/photograph_register.html.twig',
            array(
                'form' => $form->createView(),
                'message' => $message,
                'status' => $status
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

}

<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Entity\UserData;
use \Datetime;

class SecurityController extends Controller
{
    function status($code){
        $message = array(
            201=>'Thank you for joining us. Please check your email and click the confirmation link to finish your registration.',
            202=>'You have successfully reset your password.',
            203=>'Please check your email. If you do not receive email from us, please submit again.',
            204=>'You have succesfully updated your profile.',

            301=>'No user found',
            302=>'No user data found'
        );
        return $message[$code];
    }

    /**
     * @Route("/secure/login", name="login")
     */
    public function loginAction(Request $request)
    {
        $message = null;
        $status = null; // true=success, false=error

        $helper = $this->get('security.authentication_utils');
        $error = $helper->getLastAuthenticationError();

        if ($error) {
            $status = false;
            $message = $error->getMessage();
        }

        $query = $request->query->all();
        if(count($query)>0){
            $status = true;
            $message = $this->status($query['code']); 
        }

        return $this->render(
            'security/login.html.twig',
            array(
                'message' => $message,
                'status' => $status
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

            $swiftMailer = (new \Swift_Message('[Symfoto] Forgot Password'))
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
                        
            return $this->redirect('/login?code=203');
            
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
                'No user found for email '.$email
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

            return $this->redirect('/login?code=202');
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

    /**
     * @Route("/myprofile", name="showProfile")
     */
    public function showProfile(Request $request)
	{  
        $status = 'default';
        $message = null;

        $user = $this->getUser();

        $userId = $user->getId();

        $roles = $user->getRoles();
        $photographer = false;
        if( in_array( "ROLE_PHOTOGRAPH" ,$roles ) )
        {
            $photographer= true;
        }

        if (!$user) {
            return $this->redirect('/info?code=301&status=warning');
        }else{
            $em = $this->getDoctrine()->getManager();
            $userData = $em->getRepository('AppBundle:UserData')
                ->findOneBy(
                    ['userid' => $userId]
                );

            $date = new Datetime();

            if (!$userData) {
                $userData = [];
                $userData['userid'] = $userId;
                $userData['gender'] = null;
                $userData['firstname'] = null;
                $userData['lastname'] = null;
                $userData['location'] = null;
                $userData['phone'] = null;
                $userData['updatedAt'] = $date;
                $updatedAt = $this->time_elapsed_string($date->format("Y-m-d H:i:s"));
            }else{
                $updatedAt = $this->time_elapsed_string($userData->getUpdatedAt()->format("Y-m-d H:i:s"));
            }

            return $this->render('security/show_profile.html.twig',
                array(
                    'user' => $user,
                    'userData' => $userData,
                    'status' => $status,
                    'message' => $message,
                    'updatedAt' => $updatedAt,
                    'photographer'=>$photographer
                )
            );
        }
    }

    /**
     * @Route("/profile/edit", name="editProfile")
     * @Method({"GET","POST"})
     */
    public function editProfile(Request $request)
	{      
        $status = 'default';
        $message = null;

        $query = $request->query->all();
        if(count($query)>0){
            $status = $query['status']; 
            $message = $this->status($query['code']); 
        }

        $user = $this->getUser();
        $userId = $user->getId();

        $roles = $user->getRoles();
        $photographer = false;
        if( in_array( "ROLE_PHOTOGRAPH" ,$roles ) )
        {
            $photographer= true;
        }

        $em = $this->getDoctrine()->getManager();

        if (!$user) {
            return $this->redirect('/info?code=301&status=warning');
        }else{
            $date = new Datetime();

            if ($request->getMethod() == 'GET') {
                $genders = ['Male','Female'];
        
                $userData = $em->getRepository('AppBundle:UserData')
                    ->findOneBy(
                        ['userid' => $userId]
                    );
                if (!$userData) {
                    $userid = $user;
                    $userData = [];
                    $userData['userid'] = $userid;
                    $userData['gender'] = null;
                    $userData['firstname'] = null;
                    $userData['lastname'] = null;
                    $userData['location'] = null;
                    $userData['phone'] = null;
                    $userData['updatedAt'] = $date;
                    $updatedAt = $this->time_elapsed_string($date->format("Y-m-d H:i:s"));
                }else{
                    $updatedAt = $this->time_elapsed_string($userData->getUpdatedAt()->format("Y-m-d H:i:s"));
                }

                return $this->render('security/edit_profile.html.twig',
                    array(
                        'userData' => $userData,
                        'genders' => $genders,
                        'status' => $status,
                        'message' => $message,
                        'updatedAt' => $updatedAt,
                        'photographer' => $photographer
                    )
                );

            }else{
                $data = $request->request->all();
                $firstname = $data['firstname'];
                $lastname = $data['lastname'];
                $gender = $data['gender'];
                $location = $data['location'];
                $phone = $data['phone'];
                $userid = $data['userid'];
                if($photographer){
                    $shortdescr = $data['shortdescr'];
                    $longdescr = $data['longdescr'];
                }
                $userData = $em->getRepository('AppBundle:UserData')
                    ->findOneBy(
                        ['userid' => $userid]
                    );

                if (!$userData) {
                    $userData = new UserData();

                    $userData->setUserId($userid);
                    $userData->setFirstname($firstname);
                    $userData->setLastname($lastname);
                    $userData->setGender($gender);
                    $userData->setLocation($location);
                    $userData->setPhone($phone);
                    if($photographer){
                        $userData->setLongdescr($longdescr);
                        $userData->setShortdescr($shortdescr);
                    }
                    $userData->setUpdatedAt($date);
                    $em->persist($userData);
                }else{
                    $userData->setFirstname($firstname);
                    $userData->setLastname($lastname);
                    $userData->setGender($gender);
                    $userData->setLocation($location);
                    $userData->setPhone($phone);
                    if($photographer){
                        $userData->setLongdescr($longdescr);
                        $userData->setShortdescr($shortdescr);
                    }
                    $userData->setUpdatedAt($date);
                }
                $em->flush();
                return $this->redirect('/profile/edit?status=success&code=204');
            }
        }
    }

    /**
     * @Route("/info", name="info")
     */
	public function infoAction(Request $request)
	{      
        $message = null;
        $status = 'default';

        $query = $request->query->all();
        if(count($query)>0){
            $status = $query['status']; 
            $message = $this->status($query['code']); 
        }

        return $this->render('security/info.html.twig',
            array(
                'status' => $status,
                'message' => $message
            )
        );
    }

    function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);
    
        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;
    
        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }
    
        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
}
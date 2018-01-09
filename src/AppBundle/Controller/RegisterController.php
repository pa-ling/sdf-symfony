<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Image;
use AppBundle\Entity\GalleryMedia;
use AppBundle\Form\ImageType;
use AppBundle\Service\FileUploader;
use Application\Sonata\MediaBundle\Entity\Media;
use AppBundle\Form\UserType;
use Application\Sonata\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Photographers;

class RegisterController extends Controller
{
    /**
     * @Route("/register", name="register")
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
                $user->setEnabled(true);

                try{
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();

                } catch(\Doctrine\DBAL\DBALException $e) {
                    $this->get('session')->getFlashBag()->add('error', 'Can\'t insert entity.');

                }

        }

        return $this->render(
            'security/user_register.html.twig',
            array(
                'form' => $form->createView(),

            )
        );
    }

    /**
     * @Route("/photograph_registration", name="photograph_registration")
     */
    public function photographRegisterAction(Request $request)
    {

        $user = new User();

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $email = $data['email'];
            $username = $data['username'];

            $password = $this->get('security.password_encoder')->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setEmail($email);
            $user->setUsername($username);
            $user->setRoles(array('ROLE_PHOTOGRAPH'));
            $user->addRole('ROLE_PHOTOGRAPH');
            $user->setEnabled(true);

            $firstname = $data['firstname'];
            $lastname = $data['lastname'];

            try{
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $photographers = new Photographers();
                $photographers->setUserId($user->getId());
                $photographers->setLongdescr("long des");
                $photographers->setShortdescr("long des");
                $photographers->setSurname($lastname);
                $photographers->setFirstname($firstname);

                $em = $this->getDoctrine()->getManager();
                $em->persist($photographers);
                $em->flush();

                return $this->redirect('/login');
            } catch(\Doctrine\DBAL\DBALException $e) {
                $this->get('session')->getFlashBag()->add('error', 'Can\'t insert entity.');
            }

        }

        return $this->render('security/photograph_register.html.twig');
    }

}

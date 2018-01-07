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

}

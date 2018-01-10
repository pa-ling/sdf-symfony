<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Category;
use AppBundle\Entity\Image;

class CategoryController extends Controller
{

    /**
     * @Route("/showCategories", name="showCategories")
     */
    public function showActions(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $categories = $em->getRepository('AppBundle:Category')->findAll();

        return $this->render('default/category.html.twig', array(
            'categories' => $categories,
        ));

    }

    /**
     * @Route("/addCategory", name="addCategory")
     */
    public function addCategory(Request $request){

        $data = $request->request->all();
        $categoryName = $data['categoryName'];

        $category = new Category();
        $category->setName($categoryName);

        $em = $this->getDoctrine()->getManager();
        $em->persist($category);
        $em->flush();

        return $this->redirectToRoute('showCategories');
    }

}
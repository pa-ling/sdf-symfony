<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Category;
use AppBundle\Entity\Image;

class SearchController extends Controller
{
    /**
     * @Route("/search", name="search")
     */
    public function search(Request $request){

        $data = $request->request->all();
        $keyword = $data['keyword'];

        if ("" === $keyword)
        {
            return $this->redirectToRoute('photographers');
        }

        $em = $this->getDoctrine()->getManager();

        $products = $em->getRepository('AppBundle:Product')
            ->searchByCategoryAndDescription($keyword);

        return $this->render('default/search.html.twig', array(
            'products' => $products
        ));

    }

}
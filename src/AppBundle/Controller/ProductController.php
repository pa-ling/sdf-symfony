<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Product;
use Application\Sonata\MediaBundle\Entity\Media;

class ProductController extends Controller
{

	/**
     * @Route("/product", name="product")
	 * @Method({"GET", "POST"})
     */
	public function createProduct(Request $request)
	{
		$em = $this->getDoctrine()->getManager();

		$image = $this->getDoctrine()
			->getRepository(Media::class)
			->find(2);

		if ($request->getMethod() == 'POST') {
			$product = new Product();
			$product->setPrice(19.99);
			$product->setImage($image);

			$em->persist($product);

			$em->flush();

			return $this->redirect('/product');
		}else if ($request->getMethod() == 'GET') {
			$products = $this->getDoctrine()
				->getRepository(Product::class)
				->findAll();
		}

		return $this->render('member/product/product.html.twig', array(
			'products' =>$products
		));
	}

	/**
     * @Route("/product/new", name="newProduct")
     */
	public function newProduct()
	{
		return $this->render('member/product/new-product.html.twig', array());
	}

	/**
     * @Route("/product/{id}", name="product2")
     */
	public function showProduct($id)
	{
		$product = $this->getDoctrine()
        	->getRepository(Product::class)
        	->find($id);

    	if (!$product) {
        	throw $this->createNotFoundException(
            	'No product found for id '.$productId
        	);
    	}

    	return new Response('Product with id '.$product->getId().' and price '. $product->getPrice());
	}
}
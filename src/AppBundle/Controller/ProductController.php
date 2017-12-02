<?php

namespace AppBundle\Controller;

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
     */
	public function createProduct()
	{
		$em = $this->getDoctrine()->getManager();

		$image = $this->getDoctrine()
			->getRepository(Media::class)
			->find(1);

		$product = new Product();
        $product->setPrice(19.99);
        $product->setImage($image);

        $em->persist($product);

        $em->flush();

        return new Response('Saved new product with id '.$product->getId());
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
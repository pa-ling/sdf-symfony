<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Product;
use Application\Sonata\MediaBundle\Entity\Media;
use \Datetime;

class ProductController extends Controller
{

	/**
     * @Route("/myproduct", name="product")
	 * @Method({"GET", "POST"})
     */
	public function indexProduct(Request $request)
	{
		$em = $this->getDoctrine()->getManager();

		// $image = $this->getDoctrine()
		// 	->getRepository(Media::class)
		// 	->find(2);

		if ($request->getMethod() == 'POST') {
			$data = $request->request->all();
			$price = $data['price'];
			$gallery = $data['galleries'];
			$name= $data['name'];
            $slug = $this->slugify($name);
            $date = new Datetime();
			$user = $this->getUser()->getId();

			$product = new Product();
			$product->setName($name);
			$product->setSlug($slug);
			$product->setPrice($price);
			$product->setGallery($gallery);
			$product->setEnabled(false);
			$product->setOwnedBy($user);                
			$product->setCreatedAt($date);
			$product->setUpdatedAt($date);  

			$em->persist($product);
			$em->flush();

			return $this->redirect('/myproduct');
		}else if ($request->getMethod() == 'GET') {
			$products = $this->getDoctrine()
				->getRepository(Product::class)
				->findAll();
			
			return $this->render('member/product/product.html.twig', array(
				'products' =>$products
			));
		}
	}

	/**
     * @Route("/myproduct/new", name="newProduct")
     */
	public function newProduct()
	{	
		$em = $this->getDoctrine()->getManager();
		$user = $this->getUser()->getId();
		$galleries = $em->getRepository('AppBundle:Gallery')
            ->findBy(
                ['owned_by' => $user],
                ['createdAt' => 'DESC']
			);

		return $this->render('member/product/new-product.html.twig', array(
			'galleries'=>$galleries
		));
	}

		/**
     * @Route("/myproduct/{id}/{state}", name="productPublish")
     */
	public function publishProduct($id,$state)
	{
		$em = $this->getDoctrine()->getManager();
		$product = $em->getRepository('AppBundle:Product')
		->findOneBy(
			['id' => $id]
		);

		// Check owner
		$user = $this->getUser()->getId();
		$owner = $product->getOwnedBy();
		if($user === $owner){
			$enable= true;
			if($state == 0){
				$enable = false;
			}
			$product->setEnabled($enable);
			$em->flush();
			return $this->redirect('/myproduct');
		}else{
			throw $this->createNotFoundException(
            	'You are not authorize to do some action for '.$product->getName()
        	);
		}
	}

	/**
     * @Route("/myproduct/{slug}", name="myproductShow")
     */
	public function showMyProduct($slug)
	{
		$em = $this->getDoctrine()->getManager();
		$product = $em->getRepository('AppBundle:Product')
		->findOneBy(
			['slug' => $slug]
		);

    	if (!$product) {
        	throw $this->createNotFoundException(
            	'No product found for id '.$productId
        	);
    	}
		
		print_r('Product with id: '.$product->getId().', name: '. $product->getName().', price: '. $product->getPrice().', galleries:');
		print_r($product->getGallery());
    	return new Response();}

	/**
     * @Route("/product/{slug}", name="productShow")
     */
	public function showProduct($slug)
	{
		$em = $this->getDoctrine()->getManager();
		$product = $em->getRepository('AppBundle:Product')
		->findOneBy(
			['slug' => $slug]
		);

    	if (!$product) {
        	throw $this->createNotFoundException(
            	'No product found for id '.$productId
        	);
    	}
		print_r('Product with id: '.$product->getId().', name: '. $product->getName().', price: '. $product->getPrice().', galleries:');
		print_r($product->getGallery());
    	return new Response();
	}



	static public function slugify($text)
    {
      // replace non letter or digits by -
      $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    
      // transliterate
      $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    
      // remove unwanted characters
      $text = preg_replace('~[^-\w]+~', '', $text);
    
      // trim
      $text = trim($text, '-');
    
      // remove duplicate -
      $text = preg_replace('~-+~', '-', $text);
    
      // lowercase
      $text = strtolower($text);
    
      if (empty($text)) {
        return 'n-a';
      }
    
      return $text;
    }
}
<?php

namespace Application\Controller;

use Application\Sonata\MediaBundle\Entity\Media as Media;

namespace AppBundle\Controller;

use Application\Sonata\MediaBundle\Entity\Media;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Product;




use \Datetime;



class ProductController extends Controller
{

	/**
     * @Route("/myproduct", name="product")
	 * @Method({"GET", "POST"})
     */
	public function indexProduct(Request $request)
	{
        if ($this->get('security.authorization_checker')->isGranted('ROLE_PHOTOGRAPH')) {




            $em = $this->getDoctrine()->getManager();

            // $image = $this->getDoctrine()
            // 	->getRepository(Media::class)
            // 	->find(2);

            if ($request->getMethod() == 'POST') {
                $data = $request->request->all();
                $price = $data['price'];
                $galleryId = $data['galleries'];
                $name= $data['name'];
                $slug = $this->slugify($name);
                $date = new Datetime();
                $user = $this->getUser()->getId();

                $mediaIds = $em->getRepository('AppBundle:GalleryMedia')->findOneBy(
                    ['gallery_id' => $galleryId]
                );

                $media = $this->get('sonata.media.manager.media')->findOneBy(
                    ['id' => $mediaIds]
                );

                $product = new Product();
                $product->setName($name);
                $product->setSlug($slug);
                $product->setImage($media->getProviderReference());
                $product->setPrice($price);
                $product->setGallery($galleryId);
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
        } else {
            return $this->redirectToRoute('home');                  // redirect to controller name = home
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
				'No product found for id '.$product->Id
			);
		}

		// Check owner
		$user = $this->getUser()->getId();
		$owner = $product->getOwnedBy();

		if($user !== $owner){
			throw $this->createNotFoundException(
            	'You are not authorize to do some action for '.$product->getName()
        	);
		}else{


			print_r('Product with id: '.$product->getId().', name: '. $product->getName().', price: '. $product->getPrice(). ''.$this->getPreviewImgPathForProduct($product));


			return new Response();
		}
	}

    /**
     * @Route("/productdetails/{slug}", name="productdetails")
     */
    public function showProductDetails($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('AppBundle:Product')
            ->findOneBy(
                ['slug' => $slug]
            );

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$product->Id
            );
        }


        $mediaIDs = $em->getRepository('AppBundle:GalleryMedia')
            ->findBy(
                ['gallery_id' => $product->getGallery()]
            );



        $media = $this->get('sonata.media.manager.media')
            ->findBy(
                ['id' => $mediaIDs]
            );


        return $this->render('member/product/details.html.twig', array(
            'product' => $product,
            'media' => $media,

        ));

    }


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
            	'No product found for id '.$product->Id
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
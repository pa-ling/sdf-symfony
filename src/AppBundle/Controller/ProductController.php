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
     * @Route("/myproduct", name="products")
	 * @Method({"GET", "POST"})
     */
	public function indexProduct(Request $request)
	{
        if ($this->get('security.authorization_checker')->isGranted('ROLE_PHOTOGRAPH')) {

            $em = $this->getDoctrine()->getManager();

            if ($request->getMethod() == 'POST') {
                $data = $request->request->all();
                $price = $data['price'];
                $galleryId = $data['galleries'];
                $name= $data['name'];
                $slug = $this->slugify($name);
                $date = new Datetime();
                $user = $this->getUser()->getId();
                $category = $data['category'];
                $description = $data['description'];


                $mediaIds = $em->getRepository('AppBundle:GalleryMedia')->findOneBy(
                    ['gallery_id' => $galleryId]
                );

                $media = $this->get('sonata.media.manager.media')->findOneBy(
                    ['id' => $mediaIds]
                );

                $product = new Product();
                $product->setDescription($description);
                $product->setName($name);
                $product->setSlug($slug);
                $product->setImage($media->getProviderReference());
                $product->setPrice($price);
                $product->setCategory($category);
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

				foreach ($products as $product){
					$product->setImage($this->getPreviewImgPathForProduct($product));
				}



                return $this->render('default/product.html.twig', array(
                    'products' => $products
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
        $categories = $em->getRepository('AppBundle:Category')->findAll();

		return $this->render('default/create_product.html.twig', array(
			'galleries'=>$galleries,
            'categories' => $categories
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
			$preview_image = $this->getPreviewImgPathForProduct($product);
			$created_At = $product->getCreatedAt()->format('d/m/Y');

			$galleries = $product->getGallery();
			$imageIdInGallery = array();
			foreach ($galleries as $key => $value) {
				// fetch all gallery_media
				$gallery_media_fetch = $em->getRepository('AppBundle:GalleryMedia')
				->findBy(
					['gallery_id' => $value],
					['createdAt' => 'DESC']                   
				);

				foreach ($gallery_media_fetch as $key => $value) {
					$media_id = $value->getMediaId();
					array_push($imageIdInGallery, $media_id); 
				}
			}

			$images = array(); 
			$images_size = array();
			for ($i=0; $i <sizeof($imageIdInGallery); $i++) { 
				$images[$i] = $this->get('sonata.media.manager.media')
					->findOneBy(
						['id'=>$imageIdInGallery[$i]]
					);
				$size = $this->filesize_formatted($images[$i]->getSize());
				array_push($images_size, $size); 
			}

			return $this->render('default/one-product.html.twig', array(
				'product'=>$product,
				'preview_image'=>$preview_image,
				'created_At'=>$created_At,
				'images'=>$images,
				'images_size'=>$images_size
			));
		}    	
	}

    /**
     * @Route("/productdetails/{slug}", name="productdetails")
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
		
		$preview_image = $this->getPreviewImgPathForProduct($product);
		$created_At = $product->getCreatedAt()->format('d/m/Y');

		$galleries = $product->getGallery();
		$imageIdInGallery = array();
		foreach ($galleries as $key => $value) {
			// fetch all gallery_media
			$gallery_media_fetch = $em->getRepository('AppBundle:GalleryMedia')
			->findBy(
				['gallery_id' => $value],
				['createdAt' => 'DESC']                   
			);

			foreach ($gallery_media_fetch as $key => $value) {
				$media_id = $value->getMediaId();
				array_push($imageIdInGallery, $media_id); 
			}
		}

		$images = array(); 
		$images_size = array();
		for ($i=0; $i <sizeof($imageIdInGallery); $i++) { 
			$images[$i] = $this->get('sonata.media.manager.media')
				->findOneBy(
					['id'=>$imageIdInGallery[$i]]
				);
			$size = $this->filesize_formatted($images[$i]->getSize());
			array_push($images_size, $size); 
		}
		
		return $this->render('default/one-product.html.twig', array(
			'product'=>$product,
			'preview_image'=>$preview_image,
			'created_At'=>$created_At,
			'images'=>$images,
			'images_size'=>$images_size
		));
		
	}

	public function getPreviewImgPathForProduct($product)
    {
        $em = $this->getDoctrine()->getManager();
        $mediaIds = $em->getRepository('AppBundle:GalleryMedia')->findOneBy(
            ['gallery_id' => $product->getGallery()]
        );
        $media = $this->get('sonata.media.manager.media')->findBy(
            ['id' => $mediaIds->getMediaId()]
		);
		
        return $media[0]->getProviderReference();
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
	
	public function filesize_formatted($size)
    {
        $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }
}

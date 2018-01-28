<?php

namespace Application\Controller;

use Application\Sonata\MediaBundle\Entity\Media as Media;

namespace AppBundle\Controller;

use Application\Sonata\MediaBundle\Entity\Media;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Null_;
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
			$user = $this->getUser()->getId();

            if ($request->getMethod() == 'POST') {
                $data = $request->request->all();
                $price = $data['price'];
                $galleryId = $data['galleries'];
				$name= $data['name'];
				$date = new Datetime();

				$slug = $this->slugify($name);
				$product = $em->getRepository('AppBundle:Product')
					->findOneBy(
						['slug' => $slug]
					);
				if($product){
					$slug = $slug.'-'.$date->getTimestamp();
				}
			
                $user = $this->getUser()->getId();
                $category = $data['category'];
                $description = $data['description'];


                $mediaIds = $em->getRepository('AppBundle:GalleryMedia')->findOneBy(
                    ['gallery_id' => $galleryId]
                );

                $media = $this->get('sonata.media.manager.media')->findOneBy(
                    ['id' => $mediaIds->getMediaId()]
                );

                $product = new Product();
                $product->setDescription($description);
                $product->setName($name);
                $product->setSlug($slug);
                $product->setImage($media->getProviderReference());
                $product->setPrice($price);
                $product->setCategory($category);
                $product->setGallery($galleryId);
                $product->setEnabled(true);
                $product->setOwnedBy($user);
                $product->setCreatedAt($date);
                $product->setUpdatedAt($date);

                $em->persist($product);
                $em->flush();

                return $this->redirect('/myproduct');
            }else if ($request->getMethod() == 'GET') {
                $products = $this->getDoctrine()
                    ->getRepository(Product::class)
					->findBy(
						['owned_by' => $user],
						['createdAt' => 'DESC']
					);

				foreach ($products as $product){
					$galleries = $product->getGallery();
					$imageIdInGallery = array();
					$prev = array();
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
					$product->setImage($imageIdInGallery[0]);
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

        $galleriesWithImages = array();

        foreach ($galleries as $gallery) {

            $galleryMedia = $em->getRepository('AppBundle:GalleryMedia')->findBy(
                ['gallery_id' => $gallery->getId()]
            );
            if(count($galleryMedia) > 0){
                array_push($galleriesWithImages, $gallery);
            }
        }
        
		return $this->render('default/create_product.html.twig', array(
			'galleries'=>$galleriesWithImages,
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
			$created_At = $this->time_elapsed_string($product->getCreatedAt()->format('Y-m-d H:i:s'));

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
			$product->setImage($imageIdInGallery[0]);

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
     * @Route("/productdetails/{id}", name="productdetails")
     */
	public function showProduct($id)
	{
		$em = $this->getDoctrine()->getManager();
		$product = $em->getRepository('AppBundle:Product')
		->findOneBy(
			['id' => $id]
		);

    	if (!$product) {
        	throw $this->createNotFoundException(
            	'No product found for id '.$product->Id
        	);
		}
		
		// $category_id = $product->getCategory();
		// $category = $em->getRepository('AppBundle:Category')
		// ->findOneBy(
		// 	['id' => $category_id]
		// );

		// $product->setCategory($category->getName());

		$preview_image = $this->getPreviewImgPathForProduct($product);
		$created_At = $this->time_elapsed_string($product->getCreatedAt()->format("Y-m-d H:i:s"));

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
		$product->setImage($imageIdInGallery[0]);

		$images = array(); 
		$images_size = array();
		$images_prev = array();
		for ($i=0; $i <sizeof($imageIdInGallery); $i++) { 
			$images[$i] = $this->get('sonata.media.manager.media')
				->findOneBy(
					['id'=>$imageIdInGallery[$i]]
				);

			$provider_reference_without_ext = preg_replace('/\\.[^.\\s]{3,4}$/', '', $images[$i]->getProviderReference());
			$image_prev = $images[$i]->getId().$provider_reference_without_ext.'.png';
			array_push($images_prev, $image_prev); 
			
			$size = $this->filesize_formatted($images[$i]->getSize());
			array_push($images_size, $size); 
		}
		
		return $this->render('default/one-product.html.twig', array(
			'product'=>$product,
			'preview_image'=>$preview_image,
			'created_At'=>$created_At,
			'images'=>$images,
			'images_size'=>$images_size,
			'images_prev'=>$images_prev
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

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
        }else{
            $em = $this->getDoctrine()->getManager();

            $products = $em->getRepository('AppBundle:Product')
                ->searchByCategoryAndDescription($keyword);

            foreach ($products as $product){
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
            }

            return $this->render('default/search.html.twig', array(
                'products' => $products,
                'keyword' => $keyword
            ));
        }

    }

    /**
     * @Route("/sort", name="sort")
     */
    public function sort(Request $request){
        $query = $request->query->all();
        if(count($query)>0){
            $keyword= $query['q'];
            
            $em = $this->getDoctrine()->getManager();
            if($keyword=='null'){
                $products = $em->getRepository('AppBundle:Product')
                    ->findBy(
                        ['category'=>'']
                    );
            }else{
                $products = $em->getRepository('AppBundle:Product')
                    ->findBy(
                        ['category'=>$keyword]
                    );
            }            

            foreach ($products as $product){
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
            }

            return $this->render('default/search.html.twig', array(
                'products' => $products,
                'keyword' => $keyword
            ));
        }else{
            return $this->redirectToRoute('photographers');
        }
    }
}
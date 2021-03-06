<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use \Datetime;
use AppBundle\Entity\Image;
use AppBundle\Entity\GalleryMedia;
use AppBundle\Form\ImageType;
use AppBundle\Service\FileUploader;
use Application\Sonata\MediaBundle\Entity\Media;

class ImageController extends Controller
{
    /**
     * @Route("/image", name="")
     * @Method({"GET"})
     */
    public function imageActions(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser()->getId();

        $gallerie_medias = $em->getRepository('AppBundle:GalleryMedia')
            ->findBy(
                ['owned_by' => $user],
                ['createdAt' => 'ASC']
            );

        $images = array();
        $categorix = array();
        $catArr = array();
        foreach ($gallerie_medias as $key => $value) {
            $images[$value->getMediaId()] = $this->get('sonata.media.manager.media')
                ->findOneBy(
                    ['id' => $value->getMediaId()]
                );

            // Collect all galleries from each gallery_media
            if (!empty($value->getGalleryId())) {
                $gallery = $em->getRepository('AppBundle:Gallery')
                    ->findOneBy(
                        ['id' => $value->getGalleryId()]
                    );
                if (!empty($gallery)) {
                    if (empty($categorix[$value->getMediaId()])) {
                        $categorix[$value->getMediaId()][] = '<a href="/gallery/' . $gallery->getSlug() . '">' . $gallery->getName() . '</a>';
                    } else {
                        array_push($categorix[$value->getMediaId()], '<a href="/gallery/' . $gallery->getSlug() . '">' . $gallery->getName() . '</a>');
                    }
                }
            } else {
                $categorix[$value->getMediaId()][] = '-';
            }
        }

        $current_galleries = array();
        foreach ($categorix as $key => $value) {
            $current_galleries[$key] = implode(", ", $value);
        }

        $createdAt = array();
        foreach ($images as $key => $value) {
            $created_At = $this->time_elapsed_string($value->getCreatedAt()->format("Y-m-d H:i:s"));
            array_push($createdAt, $created_At);
        }

        return $this->render('default/images.html.twig', array(
            'images' => $images,
            'current_galleries' => $current_galleries,
            'createdAt' => $createdAt
        ));
    }

    /**
     * @Route("/image/{providerReference}", name="imageOne")
     */
    public function show($providerReference)
    {
        $em = $this->getDoctrine()->getManager();
        $image = $this->get('sonata.media.manager.media')
            ->findOneBy(
                ['providerReference' => $providerReference]
            );

        $gallerie_image = $em->getRepository('AppBundle:GalleryMedia')
            ->findBy(
                ['media_id' => $image->getId()],
                ['createdAt' => 'DESC']
            );

        $current_galleries = array();
        if (isset($gallerie_image)) {
            $categorix = array();
            foreach ($gallerie_image as $key => $value) {
                // Collect all galleries from each gallery_media
                if (!empty($value->getGalleryId())) {
                    $gallery = $em->getRepository('AppBundle:Gallery')
                        ->findOneBy(
                            ['id' => $value->getGalleryId()]
                        );
                    if (!empty($gallery)) {
                        if (empty($categorix[$value->getMediaId()])) {
                            // $categorix[$value->getMediaId()][] = '<a href="/gallery/' . $gallery->getSlug() . '">' . $gallery->getName() . '</a>';
                            $categorix[$value->getMediaId()][] = $gallery->getName();
                        } else {
                            // array_push($categorix[$value->getMediaId()], '<a href="/gallery/' . $gallery->getSlug() . '">' . $gallery->getName() . '</a>');
                            array_push($categorix[$value->getMediaId()], $gallery->getName());
                        }
                    }
                } else {
                    $categorix[$value->getMediaId()][] = '-';
                }
            }
            foreach ($categorix as $key => $value) {
                $current_galleries[$key] = implode(", ", $value);
            }
        }
        $created_At = $this->time_elapsed_string($image->getCreatedAt()->format("Y-m-d H:i:s"));
        $size = $this->filesize_formatted($image->getSize());

        if (!$image) {
            throw $this->createNotFoundException(
                'No Image found for id ' . $id
            );
        }

        $user = $this->getUser()->getId();

        $galleries = $em->getRepository('AppBundle:Gallery')
            ->findBy(
                ['owned_by' => $user],
                ['createdAt' => 'DESC']
            );

        return $this->render('default/image_details.html.twig', array(
            'image' => $image,
            'created_At' => $created_At,
            'size' => $size,
            'slug' => '/image/' . $providerReference,
            'current_galleries' => $current_galleries,
            'galleries' => $galleries
        ));
    }

    /**
     * @Route("/image/{providerReference}/gallery", name="imageGallery")
     * @Method({"GET", "POST"})
     */
    public function imageGallery(Request $request, $providerReference)
    {
        $em = $this->getDoctrine()->getManager();
        $image = $this->get('sonata.media.manager.media')
            ->findOneBy(
                ['providerReference' => $providerReference]
            );
        $user = $this->getUser()->getId();
        $media_id = $image->getId();

        if ($request->getMethod() == 'POST') {
            // Delete all from GalleryMedia by media_id
            $query = $em->createQuery(
                'DELETE 
                    AppBundle:GalleryMedia gm 
                WHERE 
                    gm.media_id = :media_id'
            )
                ->setParameter("media_id", $media_id);
            $result = $query->execute();

            $data = $request->request->all();
            if ($data) {
                $galleries = $data['select-gallery'];
                // Insert all actuall GalleryMedias
                foreach ($galleries as $key => $value) {
                    $gallery_id = $value;

                    $image = new GalleryMedia();
                    $image->preUpdate();
                    $image->setMediaId($media_id);
                    $image->setGalleryId($gallery_id);
                    $image->setOwnedBy($user);
                    $image->setEnabled(false);

                    $em->persist($image);
                    $em->flush();
                }
            } else {
                // Set gallery_id GalleryMedia to NULL
                $image = new GalleryMedia();
                $image->preUpdate();
                $image->setMediaId($media_id);
                $image->setGalleryId(NULL);
                $image->setOwnedBy($user);
                $image->setEnabled(false);

                $em->persist($image);
                $em->flush();
            }

            return $this->redirect('/image/' . $providerReference);
        } else if ($request->getMethod() == 'GET') {

            $galleries = $em->getRepository('AppBundle:Gallery')
                ->findBy(
                    ['owned_by' => $user],
                    ['createdAt' => 'DESC']
                );

            $gallerie_image = $em->getRepository('AppBundle:GalleryMedia')
                ->findBy(
                    ['media_id' => $image->getId()],
                    ['createdAt' => 'DESC']
                );

            $current_galleries = array();
            if (isset($gallerie_image)) {
                $current_galleries = array();
                if (isset($gallerie_image)) {
                    $categorix = array();
                    foreach ($gallerie_image as $key => $value) {
                        // Collect all galleries from each gallery_media
                        if (!empty($value->getGalleryId())) {
                            $gallery = $em->getRepository('AppBundle:Gallery')
                                ->findOneBy(
                                    ['id' => $value->getGalleryId()]
                                );
                            if (!empty($gallery)) {
                                if (empty($categorix[$value->getMediaId()])) {
                                    $categorix[$value->getMediaId()][] = '<a href="/gallery/' . $gallery->getSlug() . '">' . $gallery->getName() . '</a>';
                                } else {
                                    array_push($categorix[$value->getMediaId()], '<a href="/gallery/' . $gallery->getSlug() . '">' . $gallery->getName() . '</a>');
                                }
                            }
                        } else {
                            $categorix[$value->getMediaId()][] = '-';
                        }
                    }

                    foreach ($categorix as $key => $value) {
                        $current_galleries[$key] = implode(", ", $value);
                    }
                }
            }

            return $this->render('default/image-gallery.html.twig', array(
                'image' => $image,
                'galleries' => $galleries,
                'current_galleries' => $current_galleries
            ));
        }
    }

    /**
     * @Route("/image/delete/{id}", name="imageDelete")
     */
    public function deleteImage($id)
    {
        $em = $this->getDoctrine()->getManager();
        $gallerie_media = $em->getRepository('AppBundle:GalleryMedia')
            ->findBy(
                ['media_id' => $id]
            );

        if (!$gallerie_media) {
            throw $this->createNotFoundException(
                'No Image found for id ' . $id
            );
        } else {
            // Delete all from GalleryMedia by media_id
            $query = $em->createQuery(
                'DELETE 
                    AppBundle:GalleryMedia gm 
                WHERE 
                    gm.media_id = :media_id'
            )
                ->setParameter("media_id", $id);
            $result = $query->execute();
        }

        return $this->redirect('/image');
    }

    public function filesize_formatted($size)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }

    /**
     * @Route("/image/new/{slug}", name="imageNew")
     */
    public function uploadImage($slug)
    {
        $em = $this->getDoctrine()->getManager();

        $gallery_media['galleryId'] = null;
        $redirectUrl = '/image';

        if ($slug != 'null') {
            $gallery = $em->getRepository('AppBundle:Gallery')
                ->findOneBy(
                    ['slug' => $slug],
                    ['createdAt' => 'DESC']
                );
            if (!empty($gallery)) {
                $gallery_media['galleryId'] = $gallery->getId();
                $toSlug = $slug;
                $redirectUrl = '/gallery/' . $slug;
            }
        }


        $context = 'default';

        $gallery_media['owned_by'] = $this->getUser()->getId();
        $gallery_media['position'] = null;

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_FILES)) {
            $total_upload = count($_FILES["images"]["name"]);

            $statusUpload = array();

            for ($i = 0; $i < count($_FILES["images"]["name"]); $i++) {
                $tmpFilePath = $_FILES["images"]['tmp_name'];
                if ($tmpFilePath != "") {
                    $media['name'] = $_FILES["images"]["name"][$i];
                    $filename = pathinfo($_FILES["images"]["name"][$i], PATHINFO_FILENAME);

                    // $media['description'] = null;
                    $media['enabled'] = 0;
                    $media['provider_name'] = 'sonata.media.provider.image';
                    $media['provider_status'] = 1;

                    $provider_reference = strtolower($this->generateRandomString(40));
                    $ext = strtolower(pathinfo($media['name'], PATHINFO_EXTENSION));
                    $media['provider_reference'] = $provider_reference . '.' . $ext;
                    
                    $acceptedExt = ['jpg','jpeg'];

                    if (!in_array($ext, $acceptedExt))
                    {
                        return $this->redirect($redirectUrl.'?code=301&status=warning');
                    }

                    $media['provider_metadata'] = (object)array('filename' => $filename);
                    
                    if(empty($_FILES["images"]["tmp_name"][$i])){
                        return $this->redirect($redirectUrl.'?code=302&status=warning');
                    }

                    $image_info = getimagesize($_FILES["images"]["tmp_name"][$i]);
                    $media['width'] = $image_info[0];
                    $media['height'] = $image_info[1];

                    // $media['length'] = null;
                    $media['content_type'] = $_FILES["images"]["type"][$i];
                    $media['content_size'] = $_FILES["images"]["size"][$i];
                    // $media['copyright'] = null;
                    // $media['author_name'] = null;

                    $media['context'] = $context;

                    // $media['cdn_is_flushable'] = null;
                    // $media['cdn_flush_at'] = null;
                    // $media['cdn_status'] = null;
                    // $media['cdn_flush_identifier'] = null;

                    $mediax = new Media();
                    $mediax->preUpdate();
                    $mediax->setName($filename);
                    $mediax->setEnabled($media['enabled']);
                    $mediax->setProviderName($media['provider_name']);
                    $mediax->setProviderStatus($media['provider_status']);
                    $mediax->setProviderReference($media['provider_reference']);
                    $mediax->setProviderMetadata(array('filename' => $filename));
                    $mediax->setWidth($media['width']);
                    $mediax->setHeight($media['height']);
                    $mediax->setContentType($media['content_type']);
                    $mediax->setSize($media['content_size']);
                    $mediax->setContext($media['context']);

                    try {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($mediax);
                        $em->flush();

                        $uploadedMedia = $this->get('sonata.media.manager.media')->findOneBy(array('providerReference' => $media['provider_reference']));

                        if ($uploadedMedia) {
                            $gallery_media['media_id'] = $uploadedMedia->getId();
                            //insert to database
                            $image = new GalleryMedia();
                            $image->preUpdate();
                            $image->setMediaId($gallery_media['media_id']);
                            $image->setGalleryId($gallery_media['galleryId']);
                            $image->setOwnedBy($gallery_media['owned_by']);
                            $image->setEnabled(false);

                            try {
                                $em = $this->getDoctrine()->getManager();
                                $em->persist($image);
                                $em->flush();

                                /**
                                 * Store image in folder
                                 * 5 files
                                 * thumb_<id>_admin.<ext>
                                 * thumb_<id>_default_nav.<ext>
                                 * thumb_<id>_default_navsec.<ext>
                                 * thumb_<id>_default_small.<ext>
                                 * <reference>.<ext>
                                 */
                                $pathfolder = getcwd() . "/uploads/media/" . $context . "/0001/01/";
                                $newFilePath = $pathfolder . '/' . $media['provider_reference'];
                                $newFilePathThumbAdmin = $pathfolder . "/thumb_" . $uploadedMedia->getId() . "_admin." . $ext;
                                $newFilePathThumbDefaultNav = $pathfolder . "/thumb_" . $uploadedMedia->getId() . "_default_nav." . $ext;
                                $newFilePathThumbDefaultNavSec = $pathfolder . "/thumb_" . $uploadedMedia->getId() . "_default_navsec." . $ext;
                                $newFilePathThumbDefaultSmall = $pathfolder . "/thumb_" . $uploadedMedia->getId() . "_default_small.png";

                                $provider_reference_without_ext = preg_replace('/\\.[^.\\s]{3,4}$/', '', $media['provider_reference']);
                                // $newFileWaterMark = $pathfolder . '/'. $uploadedMedia->getId() .$provider_reference_without_ext.'.png';

                                $uploads = move_uploaded_file($tmpFilePath[$i], $newFilePath);
                                if ($uploads) {
                                    $file = $newFilePath;

                                    $im = imagecreatefromjpeg($file);

                                    // wir erstellen ein Wasserzeichen mit GD
                                    $stamp = imagecreatetruecolor(100, 70);
                                    imagefilledrectangle($stamp, 0, 0, 99, 69, 0x0000FF);
                                    imagefilledrectangle($stamp, 9, 9, 90, 60, 0xFFFFFF);
                                    imagestring($stamp, 5, 20, 20, 'SDF', 0x0000FF);
                                    imagestring($stamp, 3, 20, 40, '(c) 2018', 0x0000FF);

                                    // Ränder setzen, Dimensionen ermitteln
                                    $marge_right = 10;
                                    $marge_bottom = 10;
                                    $sx = imagesx($stamp);
                                    $sy = imagesy($stamp);

                                    // Wasserzeichen mit einer Transparenz von 50% über das Foto legen
                                    imagecopymerge($im, $stamp, imagesx($im) - $sx - $marge_right, imagesy($im) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp), 50);

                                    // Bild speichern, aufräumen
                                    imagepng($im, $newFilePathThumbDefaultSmall);
                                    imagedestroy($im);

                                    $this->smart_resize_image($file, null, '230', '150', false, $newFilePathThumbAdmin, false, false, 100);
                                    $this->compress($file, $newFilePathThumbAdmin, 40);
                                    $this->smart_resize_image($file, null, '230', '150', false, $newFilePathThumbDefaultNav, false, false, 100);
                                    $this->compress($file, $newFilePathThumbDefaultNav, 45);
                                    $this->smart_resize_image($file, null, '230', '150', false, $newFilePathThumbDefaultNavSec, false, false, 100);
                                    $this->compress($file, $newFilePathThumbDefaultNavSec, 30);
                                    // $this->smart_resize_image($file, null, '230', '150', false, $newFilePathThumbDefaultSmall, false, false, 100);
                                    // $this->compress($file, $newFilePathThumbDefaultSmall, 50);
                                }
                            } catch (Exception $e) {
                                print_r($e);
                            }
                        }
                    } catch (Exception $e) {
                        print_r($e);
                    }
                }
            }
            if (in_array(false, $statusUpload)) {
                print_r('Upload failed');
            } else {
                print_r('Upload success');
            }

        }
        return $this->redirect($redirectUrl);
    }

    public function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        // $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow)); 

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function generateRandomString($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    #compress
    public function compress($source, $destination, $quality)
    {

        $info = getimagesize($source);

        if ($info['mime'] == 'image/jpeg')
            $image = imagecreatefromjpeg($source);

        elseif ($info['mime'] == 'image/gif')
            $image = imagecreatefromgif($source);

        elseif ($info['mime'] == 'image/png')
            $image = imagecreatefrompng($source);

        imagejpeg($image, $destination, $quality);

        return $destination;
    }

    #smart_resize_image
    public function smart_resize_image($file,
                                       $string = null,
                                       $width = 0,
                                       $height = 0,
                                       $proportional = false,
                                       $output = 'file',
                                       $delete_original = true,
                                       $use_linux_commands = false,
                                       $quality = 100
    )
    {

        if ($height <= 0 && $width <= 0) return false;
        if ($file === null && $string === null) return false;

# Setting defaults and meta
        $info = $file !== null ? getimagesize($file) : getimagesizefromstring($string);
        $image = '';
        $final_width = 0;
        $final_height = 0;
        list($width_old, $height_old) = $info;
        $cropHeight = $cropWidth = 0;

# Calculating proportionality
        if ($proportional) {
            if ($width == 0) $factor = $height / $height_old;
            elseif ($height == 0) $factor = $width / $width_old;
            else                    $factor = min($width / $width_old, $height / $height_old);

            $final_width = round($width_old * $factor);
            $final_height = round($height_old * $factor);
        } else {
            $final_width = ($width <= 0) ? $width_old : $width;
            $final_height = ($height <= 0) ? $height_old : $height;
            $widthX = $width_old / $width;
            $heightX = $height_old / $height;

            $x = min($widthX, $heightX);
            $cropWidth = ($width_old - $width * $x) / 2;
            $cropHeight = ($height_old - $height * $x) / 2;
        }

# Loading image to memory according to type
        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                $file !== null ? $image = imagecreatefromjpeg($file) : $image = imagecreatefromstring($string);
                break;
            case IMAGETYPE_GIF:
                $file !== null ? $image = imagecreatefromgif($file) : $image = imagecreatefromstring($string);
                break;
            case IMAGETYPE_PNG:
                $file !== null ? $image = imagecreatefrompng($file) : $image = imagecreatefromstring($string);
                break;
            default:
                return false;
        }


# This is the resizing/resampling/transparency-preserving magic
        $image_resized = imagecreatetruecolor($final_width, $final_height);
        if (($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG)) {
            $transparency = imagecolortransparent($image);
            $palletsize = imagecolorstotal($image);

            if ($transparency >= 0 && $transparency < $palletsize) {
                $transparent_color = imagecolorsforindex($image, $transparency);
                $transparency = imagecolorallocate($image_resized, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                imagefill($image_resized, 0, 0, $transparency);
                imagecolortransparent($image_resized, $transparency);
            } elseif ($info[2] == IMAGETYPE_PNG) {
                imagealphablending($image_resized, false);
                $color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
                imagefill($image_resized, 0, 0, $color);
                imagesavealpha($image_resized, true);
            }
        }
        imagecopyresampled($image_resized, $image, 0, 0, $cropWidth, $cropHeight, $final_width, $final_height, $width_old - 2 * $cropWidth, $height_old - 2 * $cropHeight);


# Taking care of original, if needed
        if ($delete_original) {
            if ($use_linux_commands) exec('rm ' . $file);
            else @unlink($file);
        }

# Preparing a method of providing result
        switch (strtolower($output)) {
            case 'browser':
                $mime = image_type_to_mime_type($info[2]);
                header("Content-type: $mime");
                $output = NULL;
                break;
            case 'file':
                $output = $file;
                break;
            case 'return':
                return $image_resized;
                break;
            default:
                break;
        }

# Writing image according to type to the output destination and image quality
        switch ($info[2]) {
            case IMAGETYPE_GIF:
                imagegif($image_resized, $output);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($image_resized, $output, $quality);
                break;
            case IMAGETYPE_PNG:
                $quality = 9 - (int)((0.9 * $quality) / 10.0);
                imagepng($image_resized, $output, $quality);
                break;
            default:
                return false;
        }

        return true;
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
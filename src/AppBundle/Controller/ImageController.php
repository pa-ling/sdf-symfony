<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use \Datetime;
use AppBundle\Entity\Image;
use AppBundle\Form\ImageType;
use AppBundle\Service\FileUploader;

class ImageController extends Controller
{
    /**
     * @Route("/image/new/{slug}", name="imageNew")
     */
    public function uploadImage($slug){
        $em = $this->getDoctrine()->getManager();            
        $gallery = $em->getRepository('AppBundle:Gallery')
                    ->findOneBy(
                        ['slug' => $slug],
                        ['createdAt' => 'DESC']                   
                    );
        $galleryId = $gallery->getId();

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_FILES)) {
            $total_upload = count($_FILES["images"]["name"]);

            $user = $this->getUser()->getId();

            $statusUpload = array();

            for($i=0; $i<count($_FILES["images"]["name"]); $i++) {
              $tmpFilePath = $_FILES["images"]['tmp_name'];
              if ($tmpFilePath != ""){
                    $date = time();
                    $newImage = $user."/".$slug;
                    $newfolder = getcwd()."/uploads/".$newImage;
                    if(!is_dir($newfolder)){
                        mkdir($newfolder, 0777, true);
                        chmod($newfolder,0777);
                    }
                    $newCover = $user."/".$slug."/cover";                    
                    $newfolderCover = getcwd()."/uploads/".$newCover;
                    if(!is_dir($newfolderCover)){
                        mkdir($newfolderCover, 0777, true);
                        chmod($newfolderCover,0777);
                    }
                    $size = $_FILES["images"]["size"][$i];
                    $newFilePath = $newfolder.'/'.$date.'_'.$_FILES["images"]["name"][$i];
                    $newUrlPath = $newImage.'/'.$date.'_'.$_FILES["images"]["name"][$i];
                    $newFilePathCover = $newfolderCover."/".$date.'_'.$_FILES["images"]["name"][$i];
                    $newCoverPath = $newCover .'/'.$date.'_'.$_FILES["images"]["name"][$i];
                    $foto_name = $_FILES["images"]["name"][$i];
                    $upload['title'] = preg_replace('/\\.[^.\\s]{3,4}$/', '', $foto_name);
                    
                    $uploads = move_uploaded_file($tmpFilePath[$i], $newFilePath);
                 
                    //resize file
                    $file = $newFilePath;
                    //indicate the path and name for the new resized file
                    $resizedFile = $newFilePathCover;
                    //call the function (when passing path to pic)
                    $img = $this->smart_resize_image($file , null, '230' , '150' , false , $resizedFile , false , false ,100 );
                    $img = $this->compress($file , $resizedFile , 50 );
                    if($uploads){
                        $date = new Datetime();
                        
                        //insert to database
                        $image = new Image();
                        $image->setUrl($newUrlPath);
                        $image->setCover($newCoverPath);                        
                        $image->setGalleryId($galleryId);
                        $image->setCreatedBy($user);
                        $image->setCreatedAt($date);
                        $image->setUpdatedAt($date);                
                        $image->setEnabled(true);

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($image);
                        $em->flush();

                        $statusUpload[$i] = true;                       
                    }else{
                        $statusUpload[$i] = false;                       
                    }
                }
            }
            if(in_array(false, $statusUpload)){
                print_r('Upload failed');                 
            }else{
                print_r('Upload success');                 
            }

        }

        return $this->redirect('/gallery/'.$slug);        
    }

    #compress
    public function compress($source, $destination, $quality) {
    
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
  $string             = null,
  $width              = 0,
  $height             = 0,
  $proportional       = false,
  $output             = 'file',
  $delete_original    = true,
  $use_linux_commands = false,
    $quality = 100
) {

if ( $height <= 0 && $width <= 0 ) return false;
if ( $file === null && $string === null ) return false;

# Setting defaults and meta
$info                         = $file !== null ? getimagesize($file) : getimagesizefromstring($string);
$image                        = '';
$final_width                  = 0;
$final_height                 = 0;
list($width_old, $height_old) = $info;
$cropHeight = $cropWidth = 0;

# Calculating proportionality
if ($proportional) {
if      ($width  == 0)  $factor = $height/$height_old;
elseif  ($height == 0)  $factor = $width/$width_old;
else                    $factor = min( $width / $width_old, $height / $height_old );

$final_width  = round( $width_old * $factor );
$final_height = round( $height_old * $factor );
}
else {
$final_width = ( $width <= 0 ) ? $width_old : $width;
$final_height = ( $height <= 0 ) ? $height_old : $height;
$widthX = $width_old / $width;
$heightX = $height_old / $height;

$x = min($widthX, $heightX);
$cropWidth = ($width_old - $width * $x) / 2;
$cropHeight = ($height_old - $height * $x) / 2;
}

# Loading image to memory according to type
switch ( $info[2] ) {
case IMAGETYPE_JPEG:  $file !== null ? $image = imagecreatefromjpeg($file) : $image = imagecreatefromstring($string);  break;
case IMAGETYPE_GIF:   $file !== null ? $image = imagecreatefromgif($file)  : $image = imagecreatefromstring($string);  break;
case IMAGETYPE_PNG:   $file !== null ? $image = imagecreatefrompng($file)  : $image = imagecreatefromstring($string);  break;
default: return false;
}


# This is the resizing/resampling/transparency-preserving magic
$image_resized = imagecreatetruecolor( $final_width, $final_height );
if ( ($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) ) {
$transparency = imagecolortransparent($image);
$palletsize = imagecolorstotal($image);

if ($transparency >= 0 && $transparency < $palletsize) {
$transparent_color  = imagecolorsforindex($image, $transparency);
$transparency       = imagecolorallocate($image_resized, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
imagefill($image_resized, 0, 0, $transparency);
imagecolortransparent($image_resized, $transparency);
}
elseif ($info[2] == IMAGETYPE_PNG) {
imagealphablending($image_resized, false);
$color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
imagefill($image_resized, 0, 0, $color);
imagesavealpha($image_resized, true);
}
}
imagecopyresampled($image_resized, $image, 0, 0, $cropWidth, $cropHeight, $final_width, $final_height, $width_old - 2 * $cropWidth, $height_old - 2 * $cropHeight);


# Taking care of original, if needed
if ( $delete_original ) {
if ( $use_linux_commands ) exec('rm '.$file);
else @unlink($file);
}

# Preparing a method of providing result
switch ( strtolower($output) ) {
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
switch ( $info[2] ) {
case IMAGETYPE_GIF:   imagegif($image_resized, $output);    break;
case IMAGETYPE_JPEG:  imagejpeg($image_resized, $output, $quality);   break;
case IMAGETYPE_PNG:
$quality = 9 - (int)((0.9*$quality)/10.0);
imagepng($image_resized, $output, $quality);
break;
default: return false;
}

return true;
}

}
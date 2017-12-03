<?php

namespace AppBundle\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Validator\Constraints\File;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Kernel;

class FileUploader
{
   // Entity Manager
   private $em;
   
    // The request
    private $request;
    
    // Validator Service
    private $validator;
    
    // Kernel
    private $kernel;

    // The files from the upload
    private $files;

    // Directory for the uploads
    private $directory;

    // File pathes array
    private $paths;

    // Constraint array
    private $constraints;

    // Array of file constraint object
    private $fileConstraints;

    // Error array
    private $errors;

    public function __construct(EntityManager $em, RequestStack $requestStack, Validator $validator, Kernel $kernel)
    {
        $this->em = $em;
        $this->request = $requestStack->getCurrentRequest();
        $this->validator = $validator;
        $this->kernel = $kernel;
        $this->directory = 'web/uploads';
        $this->paths = array();
        $this->errors = array();
    }
    
    // Create FileUploader object with constraints
    public function create($files, $constraints = NULL)
    {
        $this->files = $files;
        $this->constraints = $constraints;
        if($this->constraints)
        {
            $this->fileConstraints = $this->createFileConstraint($this->constraints);
        }
        return $this;
    }

    // Upload the file / handle errors
    // Returns boolean
    public function upload()
    {   
        if(!$this->files) {
            return true;
        }

        foreach($this->files as $file) {
            if(isset($file)) {
                if($this->fileConstraints) {
                    $this->errors[] = $this->validator->validateValue($file, $this->fileConstraints);
                }

                $extension = $file->guessExtension();
                if(!$extension) {
                    $extension = 'bin';
                }
                $fileName = $this->createName().'.'.$extension;
                $this->paths[] = $fileName;

                if(!$this->hasErrors()) {   
                    $file->move($this->getUploadRootDir(), $fileName);   
                } else {
                    foreach($this->paths as $path) {
                        $fullpath = $this->kernel->getRootDir() . '/../' . $path;

                        if(file_exists($fullpath)) {
                            unlink($fullpath);
                        }
                    }

                    $this->paths = null;
                    return false;
                }
            }           
        }
        return true;
    }

    // Get array of relative file paths
    public function getFilePaths()
    {
        return $this->paths;
    }

    // Get array of error messages
    public function getErrors()
    {   
        $errors = array();

        foreach($this->errors as $errorListItem) {
            foreach($errorListItem as $error) {
                $errors[] = $error->getMessage();
            }
        }
        return $errors;
    }

    // Get full file path
    private function getUploadRootDir()
    {
        return $this->kernel->getRootDir() . '/../'. $this->directory;
    }

    // Generate random string for file name
    private function createName()
    {
        // Entity manager
        $em = $this->em;
        
        // Get Form request
        $form_data = $this->request->request->get('kmv_ampbundle_review');
        
        // Get brand name
        $brand_name = $em->getRepository('KmvAmpBundle:Brand')->find($form_data['brand'])->getName();
        $brand_name = str_replace(' ', '-', $brand_name);
        
        // Get model name
        $model_name = $em->getRepository('KmvAmpBundle:Model')->find($form_data['model'])->getName();
        $model_name = str_replace(' ', '-', $model_name);
        
        // Create name
        $image_name = strtolower($brand_name.'-'.$model_name).'-'.mt_rand(0,9999);
        return $image_name;
    }

    // Create array of file constraint objects
    private function createFileConstraint($constraints)
    {
        $fileConstraints = array();
        foreach($constraints as $constraintKey => $constraint) {
            $fileConstraint = new File();
            $fileConstraint->$constraintKey = $constraint;
            if($constraintKey == "mimeTypes") {
                $fileConstraint->mimeTypesMessage = "The file type you tried to upload is invalid.";
            }
            $fileConstraints[] = $fileConstraint;
        }

        return $fileConstraints;
    }

    // Check if there are constraint violations
    private function hasErrors()
    {   
        if(count($this->errors) > 0) {
            foreach($this->errors as $error) {
                if($error->__toString()) {
                    return true;
                }
            }
        }
        return false;
    }
}
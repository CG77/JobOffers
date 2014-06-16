<?php
/**
 * Created by PhpStorm.
 * User: a.haddad
 * Date: 09/04/14
 * Time: 10:59
 */

namespace Job\OffersBundle\Services;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\ExceptionInterface;

class SendService {

    private $fileSystem;
    function __construct(){
        $this->fileSystem = new Filesystem();
    }

    /**
     * @param $file
     * @param $uploadDir
     * @param $name
     * @param $extension
     * @return string
     * @throws \Symfony\Component\Config\Definition\Exception\Exception
     */
    public function upload($file,$uploadDir,$name,$extension){

        try{
            if(!$this->fileSystem->exists($uploadDir)){
                $this->fileSystem->mkdir($uploadDir);
            }
        }catch(IOExceptionInterface $e) {
            throw new Exception($e->getMessage());
        }
        if($file != null){
            $date = new \DateTime();
            $fileName = $name.'-'.$date->getTimestamp().'.'.$extension;
            $file->move($uploadDir,$fileName);
            return $fileName;
        }
    }
} 
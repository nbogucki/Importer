<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private $targetDirectory;
    private $slugger;

    public function __construct($targetDirectory, SluggerInterface $slugger)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
    }

    public function upload(UploadedFile $file)
    {
        try {
            $fileDirectory = $this->getTargetDirectory().'/'.$file->getClientOriginalExtension();
            if (!is_dir($fileDirectory)) {
                mkdir($fileDirectory, 0755);
            }
            $file->move($fileDirectory, $file->getClientOriginalName());
        } catch (FileException $e) {
            throw new FileException("Can't upload a file -> ".$e);
        }

        return $file->getClientOriginalName();
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
?>
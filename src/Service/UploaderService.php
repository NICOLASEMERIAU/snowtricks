<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploaderService
{
//    ON va lui passer un objet de type uploadedFIle
// elle doit nous retourner le nom de ce fichier
    public function __construct(private SluggerInterface $slugger) {}
    public function uploadFile(
        UploadedFile $file,
        string $directoryFolder
    ) : string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        // this is needed to safely include the file name as part of the URL
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = sprintf("%s-%s.%s", $safeFilename, uniqid('', true), $file->guessExtension());

        // Move the file to the directory where brochures are stored
        try {
            $file->move(
                $directoryFolder,
                $newFilename
            );
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }
        return $newFilename;
    }

}
<?php

namespace PJM\AppBundle\Services;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManager
{
    protected $kernelRootDir;

    public function __construct($kernelRootDir)
    {
        $this->kernelRootDir = $kernelRootDir;
    }

    /**
     * Upload a file on the server filesystem.
     *
     * @param object  UploadedFile $file   The file to be uploaded
     * @param string               $name   The name of the file, which will be suffixed by a random string
     * @param bool                 $public If public, the root folder will be /web/uploads/, if not it will be /tmp/uploads/.
     * @param string               $path   The folder name
     *
     * @return string The complete path of the file
     */
    public function upload(UploadedFile $file, $name = null, $public = false, $path = null)
    {
        // on vérifie que le fichier est valide
        if (null === $file || !$file->isValid()) {
            return;
        }

        // on définit son chemin
        $fileFolder = $this->getRootDir().($public ? 'web/' : 'tmp/').'uploads/'.(isset($path) ? $path.'/' : '');
        $fileName = (isset($name) ? $name.'-' : '').mt_rand().'.'.$file->guessExtension();

        // on l'upload
        $file->move($fileFolder, $fileName);

        // on l'enlève de la mémoire
        unset($file);

        return $fileFolder.$fileName;
    }

    /**
     * Removes a file from the server filesystem.
     *
     * @param string $filePath The complete file path
     */
    public function remove($filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    protected function getRootDir()
    {
        return $this->kernelRootDir.'/../';
    }
}

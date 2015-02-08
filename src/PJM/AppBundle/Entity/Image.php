<?php

namespace PJM\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Image
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PJM\AppBundle\Entity\ImageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Image
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="ext", type="string", length=10)
     */
    private $ext;

    /**
     * @var string
     *
     * @ORM\Column(name="alt", type="string", length=255)
     */
    private $alt;


    /**
    * @Assert\Image(maxSize="500k")
    */
    private $file;

    private $tempFilename;

    public function __toString()
    {
        return "Image id:".$this->id."; alt:".$this->alt;
    }

    /**
    * @ORM\PrePersist()
    * @ORM\PreUpdate()
    */
    public function preUpload()
    {
        if (null === $this->file) {
            return;
        }

        $this->ext = $this->file->guessExtension();
        $this->alt = $this->file->getClientOriginalName();
    }

    /**
    * @ORM\PostPersist()
    * @ORM\PostUpdate()
    */
    public function upload()
    {
        if (null === $this->file) {
            return;
        }

        // si on avait un ancien fichier, on le supprime
        if (null !== $this->tempFilename) {
            $oldFile = $this->getUploadRootDir().'/'.$this->id.'.'.$this->tempFilename;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        $this->file->move(
            $this->getUploadRootDir(),
            $this->id.'.'.$this->ext
        );

        unset($this->file);
    }

    /**
    * @ORM\PreRemove()
    */
    public function preRemoveUpload()
    {
        $this->tempFilename = $this->getUploadRootDir().'/'.$this->id.'.'.$this->ext;
    }

    /**
    * @ORM\PostRemove()
    */
    public function removeUpload()
    {
        if (file_exists($this->tempFilename)) {
            unlink($this->tempFilename);
        }
    }

    public function getUploadDir() // pour le navigateur
    {
        return 'uploads/img'; // apparait dans PJM\AppBundle\Twig\IntranetExtension
    }

    protected function getUploadRootDir() // pour le code PHP
    {
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    public function getAbsolutePath()
    {
        return null === $this->ext
            ? null
            : $this->getUploadRootDir().'/'.$this->getId().'.'.$this->getExt();
    }

    public function getWebPath()
    {
        return null === $this->ext
            ? null
            : $this->getUploadDir().'/'.$this->getId().'.'.$this->getExt();
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set ext
     *
     * @param string $ext
     * @return Image
     */
    public function setExt($ext)
    {
        $this->ext = $ext;

        return $this;
    }

    /**
     * Get ext
     *
     * @return string
     */
    public function getExt()
    {
        return $this->ext;
    }

    /**
     * Set alt
     *
     * @param string $alt
     * @return Image
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * Get alt
     *
     * @return string
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * Set file
     *
     * @param UploadedFile $file
     * @return Image
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;

        // on vérifie si on avait déjà un fichier pour cette entité
        if (isset($this->ext)) {
            // on sauvegarde l'extension du fichier pour le supprimer plus tard
            $this->tempFilename = $this->id.'.'.$this->ext;

            // on réinitialise les valeurs des attributs url et alt
            $this->ext = null;
            $this->alt = null;
        }

        return $this;
    }

    /**
     * Get file
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }
}

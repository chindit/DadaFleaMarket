<?php

namespace Dada\AdvertisementBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Image
 *
 * @ORM\Table(name="image")
 * @ORM\Entity(repositoryClass="Dada\AdvertisementBundle\Repository\ImageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Image
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="width", type="integer")
     */
    private $width;

    /**
     * @var int
     *
     * @ORM\Column(name="height", type="integer")
     */
    private $height;

    /**
     * @var int
     *
     * @ORM\Column(name="weight", type="integer")
     */
    private $weight;

    /**
     * @ORM\ManyToOne(targetEntity="Advertisement", inversedBy="images")
     */
    private $advert;

    private $file; //Contains file during transfer


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Image
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set width
     *
     * @param integer $width
     *
     * @return Image
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param integer $height
     *
     * @return Image
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set weight
     *
     * @param integer $weight
     *
     * @return Image
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight
     *
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Get file
     *
     * @return mixed
     */
    public function getFile(){
        return $this->file;
    }

    /**
     * Set File
     *
     * @param UploadedFile|null $file
     * @return $this
     */
    public function setFile(UploadedFile $file){
        $this->file = $file;

        return $this;
    }

    /**
     * Set advert
     *
     * @param \Dada\AdvertisementBundle\Entity\Advertisement $advert
     *
     * @return Image
     */
    public function setAdvert(\Dada\AdvertisementBundle\Entity\Advertisement $advert = null)
    {
        $this->advert = $advert;
        $this->advert->addImage($this);
        return $this;
    }
    /**
     * Get advert
     *
     * @return \Dada\AdvertisementBundle\Entity\Advertisement
     */
    public function getAdvert()
    {
        return $this->advert;
    }

    /**
     * @ORM\PrePersist()
     */
    public function preUpload(){
        if(is_null($this->file))
            return;

        $this->name = uniqid().'.'.$this->file->guessExtension();
        $filePath = $this->file->getRealPath();
        $imageSize = getimagesize($filePath);
        $this->width = $imageSize[0];
        $this->height = $imageSize[1];
        $this->weight = filesize($filePath);
    }

    /**
     * @ORM\PostPersist()
     */
    public function upload(){
        if(is_null($this->file))
            return;

        $this->file->move('../web/uploads/adverts/', $this->name);
    }


}

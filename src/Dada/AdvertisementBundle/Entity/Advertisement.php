<?php

namespace Dada\AdvertisementBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Advertisement
 *
 * @ORM\Table(name="advertisement")
 * @ORM\Entity(repositoryClass="Dada\AdvertisementBundle\Repository\AdvertisementRepository")
 */
class Advertisement
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
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\Length(min=10)
     * @Assert\Length(max=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     * @Assert\Length(min=10)
     */
    private $description;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=true)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=255, nullable=true)
     */
    private $location;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="decimal", precision=10, scale=8, nullable=true)
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="decimal", precision=10, scale=8, nullable=true)
     */
    private $longitude;

    /**
     * @ORM\ManyToOne(targetEntity="Category")
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="Image", mappedBy="advert", cascade={"persist", "remove"})
     */
    private $images;


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
     * Set title
     *
     * @param string $title
     *
     * @return Advertisement
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Advertisement
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set price
     *
     * @param float $price
     *
     * @return Advertisement
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set location
     *
     * @param string $location
     *
     * @return Advertisement
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     *
     * @return Advertisement
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     *
     * @return Advertisement
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set category
     *
     * @param \Dada\AdvertisementBundle\Entity\Category $category
     *
     * @return Advertisement
     */
    public function setCategory(\Dada\AdvertisementBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Dada\AdvertisementBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Add image
     *
     * @param \Dada\AdvertisementBundle\Entity\Image $image
     *
     * @return Advertisement
     */
    public function addImage(\Dada\AdvertisementBundle\Entity\Image $image)
    {
        $this->images[] = $image;

        return $this;
    }

    /**
     * Remove image
     *
     * @param \Dada\AdvertisementBundle\Entity\Image $image
     */
    public function removeImage(\Dada\AdvertisementBundle\Entity\Image $image)
    {
        $this->images->removeElement($image);
    }

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImages()
    {
        return $this->images;
    }
}

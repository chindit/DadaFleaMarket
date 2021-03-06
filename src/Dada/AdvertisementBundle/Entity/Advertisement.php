<?php

namespace Dada\AdvertisementBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

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
     * @var integer
     *
     * @ORM\Column(name="vues", type="integer")
     */
    private $views;

    /**
     * @ORM\ManyToMany(targetEntity="Category")
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="Image", mappedBy="advert", cascade={"persist", "remove"})
     */
    private $images;

    /**
     * @ORM\ManyToOne(targetEntity="Dada\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="Town", mappedBy="advert", cascade={"persist", "remove"})
     */
    private $town;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var \DateTime $published
     *
     * @ORM\Column(name="published", type="datetime")
     */
    private $published;

    /**
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $public;

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
     * Constructor
     */
    public function __construct($user = null)
    {
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
        $this->town = new \Doctrine\Common\Collections\ArrayCollection();
        $this->views = 0;
        $this->price = 0; //Default price is 0
        $this->published = new \DateTime();
        if(!is_null($user))
            $this->user = $user;
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
        $image->setAdvert($this); //DON'T WORK
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

    /**
     * Set user
     *
     * @param Dada\UserBundle\Entity\User $user
     *
     * @return Advertisement
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return Dada\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Advertisement
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set published
     *
     * @param \DateTime $published
     *
     * @return Advertisement
     */
    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    /**
     * Get published
     *
     * @return \DateTime
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Advertisement
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set views
     *
     * @param integer $views
     *
     * @return Advertisement
     */
    public function setViews($views)
    {
        $this->views = $views;

        return $this;
    }

    /**
     * Get views
     *
     * @return integer
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * Set public
     *
     * @param boolean $public
     *
     * @return Advertisement
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Get public
     *
     * @return boolean
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Add category
     *
     * @param \Dada\AdvertisementBundle\Entity\Category $category
     *
     * @return Advertisement
     */
    public function addCategory(\Dada\AdvertisementBundle\Entity\Category $category)
    {
        $this->category[] = $category;

        return $this;
    }

    /**
     * Remove category
     *
     * @param \Dada\AdvertisementBundle\Entity\Category $category
     */
    public function removeCategory(\Dada\AdvertisementBundle\Entity\Category $category)
    {
        $this->category->removeElement($category);
    }

    /**
     * Add town
     *
     * @param \Dada\AdvertisementBundle\Entity\Town $town
     *
     * @return Advertisement
     */
    public function addTown(\Dada\AdvertisementBundle\Entity\Town $town)
    {
        $this->town[] = $town;

        return $this;
    }

    /**
     * Remove town
     *
     * @param \Dada\AdvertisementBundle\Entity\Town $town
     */
    public function removeTown(\Dada\AdvertisementBundle\Entity\Town $town)
    {
        $this->town->removeElement($town);
    }

    /**
     * Get town
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTown()
    {
        return $this->town;
    }
}

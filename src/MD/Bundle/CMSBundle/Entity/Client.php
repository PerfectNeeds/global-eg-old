<?php

namespace MD\Bundle\CMSBundle\Entity;

use MD\Bundle\MediaBundle\Entity\Image as Image;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Client
 * @ORM\Table("client")
 * @ORM\Entity
 */
class Client {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var text
     *
     * @ORM\Column(name="name", type="text", type="string", length=45, nullable = false)
     */
    private $name;

    /**
     * @var text
     *
     * @ORM\Column(name="note", type="text", nullable = true)
     */
    private $note;

    /**
     * @ORM\ManyToMany(targetEntity="\MD\Bundle\MediaBundle\Entity\Image")
     */
    protected $images;

    /**
     * @var integer
     *
     * @ORM\Column(name="placement", type="integer")
     */
    protected $placement;

    /**
     * @var integer
     *
     * @ORM\Column(name="home_page", type="boolean")
     */
    protected $homePage;

    public function __toString() {
        return $this->getNote();
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Photo
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set note
     *
     * @param string $note
     * @return Photo
     */
    public function setNote($note) {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return string 
     */
    public function getNote() {
        return $this->note;
    }

    /**
     * Add images
     *
     * @param \MD\Bundle\MediaBundle\Entity\Image $images
     * @return Photo
     */
    public function addImage(\MD\Bundle\MediaBundle\Entity\Image $images) {
        $this->images[] = $images;

        return $this;
    }

    /**
     * Remove images
     *
     * @param \MD\Bundle\MediaBundle\Entity\Image $images
     */
    public function removeImage(\MD\Bundle\MediaBundle\Entity\Image $images) {
        $this->images->removeElement($images);
    }

    /**
     * Set image
     *
     * @param \MD\Bundle\MediaBundle\Entity\Image $image
     * @return DynamicPage
     */
    public function setImage(\MD\Bundle\MediaBundle\Entity\Image $image = null) {
        $this->images[0] = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \MD\Bundle\MediaBundle\Entity\Image
     */
    public function getMainImage() {
        return $this->images[0];
    }

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getImages() {
        return $this->images;
    }

    /**
     * Set placement
     *
     * @param integer $placement
     * @return Client
     */
    public function setPlacement($placement) {
        $this->placement = $placement;

        return $this;
    }

    /**
     * Get placement
     *
     * @return integer 
     */
    public function getPlacement() {
        return $this->placement;
    }


    /**
     * Set homePage
     *
     * @param boolean $homePage
     * @return Client
     */
    public function setHomePage($homePage)
    {
        $this->homePage = $homePage;
    
        return $this;
    }

    /**
     * Get homePage
     *
     * @return boolean 
     */
    public function getHomePage()
    {
        return $this->homePage;
    }
}
<?php

namespace MD\Bundle\CMSBundle\Entity;

use MD\Bundle\MediaBundle\Entity\Image as Image;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Poto
 * @ORM\Table("photo")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity
 */
class Photo {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="PhotoAlbum", inversedBy="photos")
     */
    protected $photoAlbum;

    /**
     * @var text
     *
     * @ORM\Column(name="note", type="text", nullable = true)
     */
    private $note;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\ManyToMany(targetEntity="\MD\Bundle\MediaBundle\Entity\Image")
     */
    protected $images;

    /**
     * Now we tell doctrine that before we persist or update we call the updatedTimestamps() function.
     *
     * @ORM\PrePersist
     */
    public function updatedTimestamps() {
        $this->setCreated(new \DateTime(date('Y-m-d H:i:s')));

        if ($this->getCreated() == null) {
            $this->setCreated(new \DateTime(date('Y-m-d H:i:s')));
        }
    }

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
     * Set photoAlbum
     *
     * @param \MD\Bundle\CMSBundle\Entity\PhotoAlbum $photoAlbum
     * @return Photo
     */
    public function setPhotoAlbum(\MD\Bundle\CMSBundle\Entity\PhotoAlbum $photoAlbum = null) {
        $this->photoAlbum = $photoAlbum;

        return $this;
    }

    /**
     * Get photoAlbum
     *
     * @return \MD\Bundle\CMSBundle\Entity\PhotoAlbum 
     */
    public function getPhotoAlbum() {
        return $this->photoAlbum;
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
    public function getImage() {
        return $this->images[0];
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return CareerApplication
     */
    public function setCreated($created) {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated() {
        return $this->created;
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
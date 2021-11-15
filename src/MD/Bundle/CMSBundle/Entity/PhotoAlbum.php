<?php

namespace MD\Bundle\CMSBundle\Entity;

use MD\Bundle\MediaBundle\Entity\Image as Image;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * PhotoAlbum
 * @ORM\Table("photo_album")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="MD\Bundle\CMSBundle\Repository\PhotoAlbumRepository")
 */
class PhotoAlbum {

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
     * @Assert\NotBlank()
     * @ORM\Column(name="name", type="string", length=45)
     */
    private $name;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="date", type="string", length=45)
     */
    private $date;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="client", type="string", length=45)
     */
    private $client;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="place", type="string", length=45)
     */
    private $place;

    /**
     * @ORM\OneToOne(targetEntity="Seo", inversedBy="photoAlbum")
     */
    protected $seo;

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
     * @ORM\OneToMany(targetEntity="Photo", mappedBy="photoAlbum")
     */
    protected $photos;

    /**
     * @var integer
     *
     * @ORM\Column(name="placement", type="integer")
     */
    protected $placement;

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
        return $this->getName();
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->photos = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return PhotoAlbum
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
     * @return PhotoAlbum
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
     * Add photos
     *
     * @param \MD\Bundle\CMSBundle\Entity\Photo $photos
     * @return PhotoAlbum
     */
    public function addPhoto(\MD\Bundle\CMSBundle\Entity\Photo $photos) {
        $this->photos[] = $photos;

        return $this;
    }

    /**
     * Remove photos
     *
     * @param \MD\Bundle\CMSBundle\Entity\Photo $photos
     */
    public function removePhoto(\MD\Bundle\CMSBundle\Entity\Photo $photos) {
        $this->photos->removeElement($photos);
    }

    /**
     * Get photos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPhotos() {
        return $this->photos;
    }

    /**
     * Set seo
     *
     * @param \MD\Bundle\CMSBundle\Entity\Seo $seo
     * @return PhotoAlbum
     */
    public function setSeo(\MD\Bundle\CMSBundle\Entity\Seo $seo = null) {
        $this->seo = $seo;

        return $this;
    }

    /**
     * Get seo
     *
     * @return \MD\Bundle\CMSBundle\Entity\Seo 
     */
    public function getSeo() {
        return $this->seo;
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
     * Set date
     *
     * @param string $date
     * @return PhotoAlbum
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return string 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set client
     *
     * @param string $client
     * @return PhotoAlbum
     */
    public function setClient($client)
    {
        $this->client = $client;
    
        return $this;
    }

    /**
     * Get client
     *
     * @return string 
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set place
     *
     * @param string $place
     * @return PhotoAlbum
     */
    public function setPlace($place)
    {
        $this->place = $place;
    
        return $this;
    }

    /**
     * Get place
     *
     * @return string 
     */
    public function getPlace()
    {
        return $this->place;
    }
}
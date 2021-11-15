<?php

namespace MD\Bundle\CMSBundle\Entity;

use MD\Utils\General;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Seo
 * @ORM\Table("seo")
 * @ORM\Entity(repositoryClass="MD\Bundle\CMSBundle\Repository\SeoRepository")
 */
class Seo {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string|translation
     * 
     * @Assert\NotBlank()
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     */
    protected $slug;

    /**
     * @var string|translation
     * 
     * @ORM\Column(name="meta_tag", type="string" , nullable=true)
     */
    protected $metaTag;

    /**
     * @ORM\OneToOne(targetEntity="DynamicPage", mappedBy="seo")
     */
    protected $dynamicPage;

    /**
     * @ORM\OneToOne(targetEntity="Portfolio", mappedBy="seo")
     */
    protected $portfolio;

    /**
     * @ORM\OneToOne(targetEntity="PhotoAlbum", mappedBy="seo")
     */
    protected $photoAlbum;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Seo
     */
    public function setSlug($slug) {
        if ($slug != NULL AND strstr($slug, '/') != FALSE) {
            $slug = explode('/', $slug);
            $this->slug = $slug[0] . '/' . General::seoUrl($slug[1]);
        } else {
            $this->slug = $slug;
        }
        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getRawSlug() {
        return $this->slug;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug() {
        if ($this->slug != NULL AND strstr($this->slug, '/') != FALSE) {
            $slug = explode('/', $this->slug);
            return $slug[1];
        } else {
            return $this->slug;
        }
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Seo
     */
    public function setTitle($title) {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set metaTag
     *
     * @param string $metaTag
     * @return Seo
     */
    public function setMetaTag($metaTag) {
        $this->metaTag = $metaTag;

        return $this;
    }

    /**
     * Get metaTag
     *
     * @return string 
     */
    public function getMetaTag() {
        return $this->metaTag;
    }

    /**
     * Set dynamicPage
     *
     * @param \MD\Bundle\CMSBundle\Entity\DynamicPage $dynamicPage
     * @return Seo
     */
    public function setDynamicPage(\MD\Bundle\CMSBundle\Entity\DynamicPage $dynamicPage = null) {
        $this->dynamicPage = $dynamicPage;

        return $this;
    }

    /**
     * Get dynamicPage
     *
     * @return \MD\Bundle\CMSBundle\Entity\DynamicPage 
     */
    public function getDynamicPage() {
        return $this->dynamicPage;
    }

    /**
     * Set portfolio
     *
     * @param \MD\Bundle\CMSBundle\Entity\Portfolio $portfolio
     * @return Seo
     */
    public function setPortfolio(\MD\Bundle\CMSBundle\Entity\Portfolio $portfolio = null) {
        $this->portfolio = $portfolio;

        return $this;
    }

    /**
     * Get portfolio
     *
     * @return \MD\Bundle\CMSBundle\Entity\Portfolio 
     */
    public function getPortfolio() {
        return $this->portfolio;
    }


    /**
     * Set photoAlbum
     *
     * @param \MD\Bundle\CMSBundle\Entity\PhotoAlbum $photoAlbum
     * @return Seo
     */
    public function setPhotoAlbum(\MD\Bundle\CMSBundle\Entity\PhotoAlbum $photoAlbum = null)
    {
        $this->photoAlbum = $photoAlbum;
    
        return $this;
    }

    /**
     * Get photoAlbum
     *
     * @return \MD\Bundle\CMSBundle\Entity\PhotoAlbum 
     */
    public function getPhotoAlbum()
    {
        return $this->photoAlbum;
    }
}
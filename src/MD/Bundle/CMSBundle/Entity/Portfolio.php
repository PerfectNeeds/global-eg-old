<?php

namespace MD\Bundle\CMSBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Portfolio
 * @ORM\Table("portfolio")
 * @ORM\Entity(repositoryClass="MD\Bundle\CMSBundle\Repository\PortfolioRepository")
 */
class Portfolio {

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
     * @ORM\Column(name="title", type="string", length=45)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="youtube_url", type="string", length=255)
     */
    private $youtubeUrl;

    /**
     * @ORM\OneToOne(targetEntity="Seo", inversedBy="portfolio")
     */
    protected $seo;

    /**
     * @ORM\OneToOne(targetEntity="Post", inversedBy="portfolio")
     */
    protected $post;

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

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Portfolio
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
     * Set seo
     *
     * @param \MD\Bundle\CMSBundle\Entity\Seo $seo
     * @return Portfolio
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
     * Set post
     *
     * @param \MD\Bundle\CMSBundle\Entity\Post $post
     * @return Portfolio
     */
    public function setPost(\MD\Bundle\CMSBundle\Entity\Post $post = null) {
        $this->post = $post;

        return $this;
    }

    /**
     * Get post
     *
     * @return \MD\Bundle\CMSBundle\Entity\Post 
     */
    public function getPost() {
        return $this->post;
    }

    /**
     * Set placement
     *
     * @param integer $placement
     * @return Portfolio
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
     * Set youtubeUrl
     *
     * @param string $youtubeUrl
     * @return Portfolio
     */
    public function setYoutubeUrl($youtubeUrl) {
        $this->youtubeUrl = $youtubeUrl;

        return $this;
    }

    /**
     * Get youtubeUrl
     *
     * @return string 
     */
    public function getYoutubeUrl() {
        return $this->youtubeUrl;
    }


    /**
     * Set homePage
     *
     * @param boolean $homePage
     * @return Portfolio
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
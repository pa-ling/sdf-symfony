<?php

namespace AppBundle\Entity;

use Application\Sonata\MediaBundle\Entity\Media;
use Doctrine\ORM\Mapping as ORM;

/**
 * Home
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Home
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
     * @ORM\Column(name="titel", type="string", length=64)
     */
    private $titel;

    /**
     * @var string
     *
     * @ORM\Column(name="topline", type="string", length=256, nullable=true)
     */
    private $topline;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="string", length=8192)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="botline", type="string", length=1024, nullable=true)
     */
    private $botline;

    /**
     * @var bool
     *
     * @ORM\Column(name="imageposition", type="boolean")
     */
    private $imagePosition;

    /**
     * @var movie
     *
     * @ORM\Column(name="movie", type="string", nullable=true)
     */
    private $movie;

    /**
     * @var Media
     * @ORM\ManyToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"})
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="image", referencedColumnName="id", nullable=true)
     * })
     */
    private $image;

    /**
     * Set titel
     *
     * @param string $titel
     *
     * @return Home
     */
    public function setTitel($titel)
    {
        $this->titel = $titel;

        return $this;
    }

    /**
     * Get titel
     *
     * @return string
     */
    public function getTitel()
    {
        return $this->titel;
    }

    /**
     * Set topline
     *
     * @param string $topline
     *
     * @return Home
     */
    public function setTopline($topline)
    {
        $this->topline = $topline;

        return $this;
    }

    /**
     * Get topline
     *
     * @return string
     */
    public function getTopline()
    {
        return $this->topline;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Home
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set botline
     *
     * @param string botline
     *
     * @return Home
     */
    public function setBotline($botline)
    {
        $this->botline = $botline;

        return $this;
    }

    /**
     * Get botline
     *
     * @return string
     */
    public function getBotline()
    {
        return $this->botline;
    }

    /**
     * Set imagepostion
     *
     * @param boolean $image
     *
     * @return Home
     */
    public function setImageposition($imagePosition)
    {
        $this->imagePosition = $imagePosition;

        return $this;
    }

    /**
     * Get imagepostion
     *
     * @return boolean
     */
    public function getImagePosition()
    {
        return $this->imagePosition;
    }

    /**
     * Set image
     *
     * @param string $image
     *
     * @return Home
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
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
     * Set id
     *
     * @param integer $id
     *
     * @return integer
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set movie
     *
     * @return string
     */
    public function setMovie($movie)
    {
        $this->movie = $movie;

        return $this;
    }

    /**
     * Get movie
     *
     * @return string
     */
    public function getMovie()
    {
        return $this->movie;
    }
}
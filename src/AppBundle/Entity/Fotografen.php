<?php

namespace AppBundle\Entity;

use Application\Sonata\MediaBundle\Entity\Media;
use Doctrine\ORM\Mapping as ORM;

/**
 * Fotografen
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Fotografen
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
     * @ORM\Column(name="vorname", type="string", length=64)
     */
    private $vorname;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="longdescr", type="string", length=2048)
     */
    private $longdescr;

    /**
     * @var string
     *
     * @ORM\Column(name="shortdescr", type="string", length=1024)
     */
    private $shortdescr;

    /**
     * @var Media
     *
     * @ORM\ManyToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"})
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="prev_image", referencedColumnName="id", nullable=true)
     * })
     */
    private $previmage;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getVorname()
    {
        return $this->vorname;
    }

    /**
     * @param string $vorname
     */
    public function setVorname($vorname)
    {
        $this->vorname = $vorname;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getLongDescr()
    {
        return $this->longdescr;
    }

    /**
     * @param string $long_descr
     */
    public function setLongDescr($longdescr)
    {
        $this->longdescr = $longdescr;
    }

    /**
     * @return string
     */
    public function getShortDescr()
    {
        return $this->shortdescr;
    }

    /**
     * @param string $short_descr
     */
    public function setShortDescr($shortdescr)
    {
        $this->shortdescr = $shortdescr;
    }

    /**
     * @return Media
     */
    public function getPrevImage()
    {
        return $this->previmage;
    }

    /**
     * @param Media $prev_image
     */
    public function setPrevImage($previmage)
    {
        $this->previmage = $previmage;
    }

}

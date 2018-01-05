<?php

namespace AppBundle\Entity;

use Application\Sonata\MediaBundle\Entity\Media;
use Doctrine\ORM\Mapping as ORM;

/**
 * Photographers
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Photographers
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
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64)
     */
    private $surname;

    /**
     * @var string
     *
     * @ORM\Column(name="longdescr", type="string", length=2048, nullable=true)
     */
    private $longdescr;

    /**
     * @var string
     *
     * @ORM\Column(name="shortdescr", type="string", length=1024, nullable=true)
     */
    private $shortdescr;

    /**
     * @var Media
     * @ORM\ManyToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Media", cascade={"persist"})
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="prev_image", referencedColumnName="id", nullable=true)
     * })
     */
    private $previmage;

    /**
     * @var integer
     *
     * @ORM\Column(name="userid", type="integer")
     */
    private $userID;
    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userID;
    }

    /**
     * @param int $id
     */
    public function setUserId($userID)
    {
        $this->userID = $userID;
    }

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
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * @param string $surname
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
    }

    /**
     * @return string
     */
    public function getLongdescr()
    {
        return $this->longdescr;
    }

    /**
     * @param string $longdescr
     */
    public function setLongdescr($longdescr)
    {
        $this->longdescr = $longdescr;
    }

    /**
     * @return string
     */
    public function getShortdescr()
    {
        return $this->shortdescr;
    }

    /**
     * @param string $shortdescr
     */
    public function setShortdescr($shortdescr)
    {
        $this->shortdescr = $shortdescr;
    }

    /**
     * @return Media
     */
    public function getPrevimage()
    {
        return $this->previmage;
    }

    /**
     * @param Media $previmage
     */
    public function setPrevimage($previmage)
    {
        $this->previmage = $previmage;
    }

}

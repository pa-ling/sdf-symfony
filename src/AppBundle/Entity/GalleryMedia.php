<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GalleryMedia
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class GalleryMedia
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
     * @var array
     *
     * @ORM\Column(name="gallery_id", type="array", nullable=true)
     */
    protected $gallery_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="media_id", type="integer", nullable=false)
     */
    protected $media_id;

     /**
     * @var integer
     *
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    protected $position;

    /**
     * @var bool
     * 
     * @ORM\Column(name="enabled", type="boolean", nullable=true)
     */
    protected $enabled;

    /**
     * @var integer
     *
     * @ORM\Column(name="owned_by", type="integer")
     */
    protected $owned_by;

    /**
     * @var datetime $createdAt
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var datetime $updatedAt
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    public function prePersist()
    {
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
    }

    public function preUpdate()
    {
        $this->setUpdatedAt(new \DateTime());
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
     * {@inheritdoc}
     */
    public function setGalleryId($gallery_id)
    {
        $this->gallery_id = serialize($gallery_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getGalleryId()
    {
        return unserialize($this->gallery_id);
    }

    /**
     * {@inheritdoc}
     */
    public function setMediaId($media_id)
    {
        $this->media_id = $media_id;
    }

    /**
     * {@inheritdoc}
     */
    public function getMediaId()
    {
        return $this->media_id;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwnedBy($owned_by)
    {
        $this->owned_by = $owned_by;
    }

    /**
     * {@inheritdoc}
     */
    public function getOwnedBy()
    {
        return $this->owned_by;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

}

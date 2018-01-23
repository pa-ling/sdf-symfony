<?php


namespace Application\Sonata\UserBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Sonata\UserBundle\Entity\BaseUser as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model;

class User extends BaseUser implements UserInterface
{
    /**
     * @var integer
     */
    protected $id;
    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     * @ORM\Column(name="avatar_url", type="string",nullable=true)
     */
    protected $avatarUrl;

    /**
     * @var string
     * @ORM\Column(name="salt", type="string",nullable=true)
     */
    protected $salt;

    /**
     * @var integer
     * @ORM\Column(name="nb_post", type="integer",nullable=true)
     */
    protected $nbPost;

    /**
     *
     * @var boolean
     *  @ORM\Column(name="banned", type="boolean", nullable=true)
     */
    protected $banned;

    public function getUsername()
    {
        return $this->username;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAvatarUrl()
    {
        return $this->avatarUrl;
    }


    public function setAvatarUrl($avatar_url)
    {
        $this->avatarUrl = $avatar_url;

        return $this;
    }
    public function setNbPost($nbPost)
    {
        $this->nbPost = $nbPost;

        return $this;
    }

    public function addNbPost($nbPost)
    {
        $this->nbPost += $nbPost;
        return $this;
    }

    public function getNbPost()
    {
        return $this->nbPost;
    }

    public function getBanned()
    {
        return $this->banned;
    }

    public function setBanned($banned)
    {
        $this->banned = $banned;

        return $this;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

}
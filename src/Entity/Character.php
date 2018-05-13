<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CharacterRepository")
 * @ORM\Table(name="`character`")
 */
class Character
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @ORM\Column(type="string", name="character_name") */
    private $characterName;

    /** @ORM\Column(type="string", name="character_name_key_url") */
    private $characterNameKeyUrl;

    /** @ORM\Column(type="string", name="type") */
    private $type;

    /**
     * @ORM\Column(type="string", name="photo")
     * @Assert\Image
     */
    private $photo;

    /**
     * @ORM\OneToOne(targetEntity="CharacterExtra")
     * @ORM\JoinColumn(name="extra_id", referencedColumnName="id")
     */
    private $extra;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="characters")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="CharacterPhoto", mappedBy="character")
     */
    private $photos;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getCharacterName()
    {
        return $this->characterName;
    }

    /**
     * @param mixed $characterName
     */
    public function setCharacterName($characterName): void
    {
        $this->characterName = $characterName;
    }

    /**
     * @return mixed
     */
    public function getCharacterNameKeyUrl()
    {
        return $this->characterNameKeyUrl;
    }

    /**
     * @param mixed $characterNameKeyUrl
     */
    public function setCharacterNameKeyUrl($characterNameKeyUrl): void
    {
        $this->characterNameKeyUrl = $characterNameKeyUrl;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param mixed $photo
     */
    public function setPhoto($photo): void
    {
        $this->photo = $photo;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @param mixed $extra
     */
    public function setExtra($extra): void
    {
        $this->extra = $extra;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getPhotos()
    {
        return $this->photos;
    }
}
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CharacterPhotoRepository")
 * @ORM\Table(name="`character_photo`")
 */
class CharacterPhoto
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @ORM\Column(type="string", name="path") */
    private $path;

    /** @ORM\Column(type="string", name="label", nullable=true) */
    private $label;

    /** @ORM\Column(type="datetime", name="upload_time") */
    private $uploadDate;

    /**
     * @ORM\ManyToOne(targetEntity="Character", inversedBy="photos")
     * @ORM\JoinColumn(name="character_id", referencedColumnName="id")
     */
    private $character;

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
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path): void
    {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label): void
    {
        $this->label = $label;
    }

    /**
     * @return mixed
     */
    public function getUploadDate()
    {
        return $this->uploadDate;
    }

    /**
     * @param mixed $uploadDate
     */
    public function setUploadDate($uploadDate): void
    {
        $this->uploadDate = $uploadDate;
    }

    /**
     * @return mixed
     */
    public function getCharacter()
    {
        return $this->character;
    }

    /**
     * @param mixed $character
     */
    public function setCharacter($character): void
    {
        $this->character = $character;
    }
}

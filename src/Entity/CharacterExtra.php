<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="character_extra")
 */
class CharacterExtra
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @ORM\Column(type="string", name="cover") */
    private $cover;

    /** @ORM\Column(type="string", length=14000, name="bio") */
    private $bio;

    /** @ORM\Column(type="string", length=1400, name="quote") */
    private $quote;

    /** @ORM\Column(type="string", name="quote_cite") */
    private $cite;

    /** @ORM\Column(type="string", name="sheet") */
    private $sheet;

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
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * @param mixed $cover
     */
    public function setCover($cover): void
    {
        $this->cover = $cover;
    }

    /**
     * @return mixed
     */
    public function getBio()
    {
        return $this->bio;
    }

    /**
     * @param mixed $bio
     */
    public function setBio($bio): void
    {
        $this->bio = $bio;
    }

    /**
     * @return mixed
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * @param mixed $quote
     */
    public function setQuote($quote): void
    {
        $this->quote = $quote;
    }

    /**
     * @return mixed
     */
    public function getCite()
    {
        return $this->cite;
    }

    /**
     * @param mixed $cite
     */
    public function setCite($cite): void
    {
        $this->cite = $cite;
    }

    /**
     * @return mixed
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @param mixed $sheet
     */
    public function setSheet($sheet): void
    {
        $this->sheet = $sheet;
    }
}
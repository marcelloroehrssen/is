<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="merits")
 */
class Merits
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $level;

    /**
     * @var string
     *
     * @ORM\Column(type="string", columnDefinition="text", name="`associated_downtime`")
     */
    private $associatedDowntime;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Character", mappedBy="merits")
     */
    private $characters;

    public function __construct()
    {
        $this->characters = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     */
    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    /**
     * @return string
     */
    public function getAssociatedDowntime(): string
    {
        return $this->associatedDowntime;
    }

    /**
     * @param string $associatedDowntime
     */
    public function setAssociatedDowntime(string $associatedDowntime): void
    {
        $this->associatedDowntime = $associatedDowntime;
    }

    /**
     * @return ArrayCollection
     */
    public function getCharacters(): ArrayCollection
    {
        return $this->characters;
    }

    /**
     * @param Character $character
     */
    public function addCharacter(Character $character): void
    {
        $this->characters[] = $character;
    }

    public function __toString()
    {
        return $this->name;
    }
}

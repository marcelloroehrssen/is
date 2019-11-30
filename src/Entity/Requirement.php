<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="requirement")
 */
class Requirement
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $level;

    /**
     * @ORM\ManyToOne(targetEntity="Stats")
     * @ORM\JoinColumn(name="stats_id", referencedColumnName="id")
     */
    private $stats;

    /**
     * @ORM\ManyToOne(targetEntity="Item", inversedBy="requirements")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id")
     */
    private $item;

    /**
     * @ORM\OneToOne(targetEntity="Clue", mappedBy="requirement", cascade={"persist", "remove"})
     */
    private $clue;

    /**
     * @ORM\ManyToMany(targetEntity="Character", inversedBy="handledClues")
     * @ORM\JoinTable(name="requirement_character")
     */
    private $handledBy;

    /**
     * Requirement constructor.
     */
    public function __construct()
    {
        $this->handledBy = new ArrayCollection();
    }

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
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param mixed $level
     */
    public function setLevel($level): void
    {
        $this->level = $level;
    }

    /**
     * @return mixed
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * @param mixed $stats
     */
    public function setStats($stats): void
    {
        $this->stats = $stats;
    }

    /**
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param Item $item
     */
    public function setItem(Item $item): void
    {
        $this->item = $item;
    }

    /**
     * @param Clue $clue
     */
    public function setClue(Clue $clue): void
    {
        $clue->setRequirement($this);
        $this->clue = $clue;
    }

    /**
     * @return mixed
     */
    public function getClue()
    {
        return $this->clue;
    }

    /**
     * @param ArrayCollection $handledBy
     */
    public function setHandledBy(ArrayCollection $handledBy): void
    {
        $this->handledBy = $handledBy;
    }

    /**
     * @return ArrayCollection
     */
    public function getHandledBy(): ArrayCollection
    {
        return $this->handledBy;
    }

    public function addHandledBy(Character $character)
    {
        if ($this->handledBy->contains($character)) {
            return;
        }
        $character->addHandledClues($this); // synchronously updating inverse side
        $this->handledBy[] = $character;
    }
}

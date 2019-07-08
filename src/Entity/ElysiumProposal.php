<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ElysiumProposalRepository")
 * @ORM\Table(name="elysium_proposal")
 */
class ElysiumProposal
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Character")
     * @ORM\JoinColumn(name="character_author_id", referencedColumnName="id")
     */
    private $characterAuthor;

    /**
     * @ORM\Column(type="string", name="name")
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="string", columnDefinition="longtext", name="lineup")
     * @Assert\NotBlank()
     */
    private $lineup;

    /**
     * @ORM\Column(type="string", columnDefinition="longtext", name="happening")
     * @Assert\NotBlank()
     */
    private $happening;

    /**
     * @ORM\Column(type="string", columnDefinition="longtext", name="description")
     * @Assert\NotBlank()
     */
    private $description;

    /**
     * @ORM\Column(type="boolean", name="accepted")
     */
    private $accepted = false;

    /**
     * Many Elysium have Many proposal.
     *
     * @ORM\ManyToMany(targetEntity="Elysium", inversedBy="validProposal")
     * @ORM\JoinTable(name="elysium_proposal_elysium")
     */
    private $validity;

    /**
     * @ORM\ManyToOne(targetEntity="Elysium", inversedBy="proposal")
     * @ORM\JoinColumn(name="elysium_id", referencedColumnName="id")
     */
    private $elysium;

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
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getCharacterAuthor()
    {
        return $this->characterAuthor;
    }

    /**
     * @param mixed $characterAuthor
     */
    public function setCharacterAuthor($characterAuthor)
    {
        $this->characterAuthor = $characterAuthor;
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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return boolean
     */
    public function isAccepted()
    {
        return $this->accepted;
    }

    /**
     * @param bool $accepted
     */
    public function setAccepted($accepted)
    {
        $this->accepted = $accepted;
    }

    /**
     * @return Elysium
     */
    public function getElysium()
    {
        return $this->elysium;
    }

    /**
     * @param Elysium $elysium
     */
    public function setElysium($elysium)
    {
        $this->elysium = $elysium;
    }

    /**
     * @return string
     */
    public function getLineup()
    {
        return $this->lineup;
    }

    /**
     * @param string $lineup
     */
    public function setLineup($lineup)
    {
        $this->lineup = $lineup;
    }

    /**
     * @return string
     */
    public function getHappening()
    {
        return $this->happening;
    }

    /**
     * @param string $happening
     */
    public function setHappening(string $happening)
    {
        $this->happening = $happening;
    }

    /**
     * @return mixed
     */
    public function getValidity()
    {
        return $this->validity;
    }

    /**
     * @param mixed $validity
     */
    public function setValidity($validity): void
    {
        $this->validity = $validity;
    }
}

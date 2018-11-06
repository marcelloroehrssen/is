<?php

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;

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
     * @var string
     * 
     * @ORM\Column(type="string", name="name") 
     */
    private $name;
    
    /**
     * @var string
     *
     * @ORM\Column(type="string", columnDefinition="longtext", name="lineup")
     */
    private $lineup;
    
    /**
     * @var string 
     * 
     * @ORM\Column(type="string", columnDefinition="longtext", name="description")
     */
    private $description;
    
    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="accepted")
     */
    private $accepted = false;
    
    /**
     * @var Elysium
     * 
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
     * @param boolean $accepted
     */
    public function setAccepted($accepted)
    {
        $this->accepted = $accepted;
    }

    /**
     * @return \App\Entity\Elysium
     */
    public function getElysium()
    {
        return $this->elysium;
    }

    /**
     * @param \App\Entity\Elysium $elysium
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

}

<?php

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DowntimeRepository")
 * @ORM\Table(name="downtime")
 */
class Downtime
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Character
     *
     * @ORM\ManyToOne(targetEntity="Character")
     * @ORM\JoinColumn(name="character_id", referencedColumnName="id")
     */
    private $character;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $type;
    
    
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string", columnDefinition="text", name="action")
     */
    private $text;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(type="string", columnDefinition="text", name="resolution", nullable=true)
     */
    private $resolution;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $resolvedBy = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $resolvedAt = null;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isHunt = false;

    
    /**
     * @ORM\OneToMany(targetEntity="DowntimeComment", mappedBy="downtime")
     */
    private $comments;
    
    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }
    
    /**
     * Get the value of Id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of Id
     *
     * @param mixed id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the value of Character
     *
     * @return Character
     */
    public function getCharacter()
    {
        return $this->character;
    }

    /**
     * Set the value of Character
     *
     * @param Character character
     *
     * @return self
     */
    public function setCharacter(Character $character)
    {
        $this->character = $character;
    }

    /**
     * Get the value of Text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set the value of Text
     *
     * @param string text
     *
     * @return self
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Get the value of Created At
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the value of Created At
     *
     * @param \DateTime createdAt
     *
     * @return self
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get the value of Resolution
     *
     * @return string
     */
    public function getResolution()
    {
        return $this->resolution;
    }

    /**
     * Set the value of Resolution
     *
     * @param string resolution
     *
     * @return self
     */
    public function setResolution($resolution)
    {
        $this->resolution = $resolution;
    }

    /**
     * Get the value of Resolved By
     *
     * @return User
     */
    public function getResolvedBy()
    {
        return $this->resolvedBy;
    }

    /**
     * Set the value of Resolved By
     *
     * @param User resolvedBy
     *
     * @return self
     */
    public function setResolvedBy(User $resolvedBy)
    {
        $this->resolvedBy = $resolvedBy;
    }

    /**
     * Get the value of Resolved At
     *
     * @return \DateTime
     */
    public function getResolvedAt()
    {
        return $this->resolvedAt;
    }

    /**
     * Set the value of Resolved At
     *
     * @param \DateTime resolvedAt
     *
     * @return self
     */
    public function setResolvedAt(\DateTime $resolvedAt)
    {
        $this->resolvedAt = $resolvedAt;
    }

    /**
     * Get the value of Title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the value of Title
     *
     * @param string title
     *
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;

    }

    /**
     * Get the value of Is Hunt
     *
     * @return boolean
     */
    public function getIsHunt()
    {
        return $this->isHunt;
    }

    /**
     * Set the value of Is Hunt
     *
     * @param boolean isHunt
     *
     * @return self
     */
    public function setIsHunt($isHunt)
    {
        $this->isHunt = $isHunt;

        return $this;
    }
    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
    
    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    
}

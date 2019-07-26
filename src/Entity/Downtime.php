<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DowntimeRepository")
 * @ORM\Table(name="downtime")
 */
class Downtime
{
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_UNRESOLVED = 'unresolved';

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
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string", columnDefinition="text", name="action")
     * @Assert\NotBlank()
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
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="DowntimeComment", mappedBy="downtime")
     */
    private $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->createdAt = new \DateTime();
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
     * @return Character
     */
    public function getCharacter(): ?Character
    {
        return $this->character;
    }

    /**
     * @param Character $character
     */
    public function setCharacter(Character $character): void
    {
        $this->character = $character;
    }

    /**
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return string
     */
    public function getResolution(): ?string
    {
        return $this->resolution;
    }

    /**
     * @param string $resolution
     */
    public function setResolution(string $resolution): void
    {
        $this->resolution = $resolution;
    }

    /**
     * @return User
     */
    public function getResolvedBy(): ?User
    {
        return $this->resolvedBy;
    }

    /**
     * @param User $resolvedBy
     */
    public function setResolvedBy(User $resolvedBy): void
    {
        $this->resolvedBy = $resolvedBy;
    }

    /**
     * @return \DateTime
     */
    public function getResolvedAt(): ?\DateTime
    {
        return $this->resolvedAt;
    }

    /**
     * @param \DateTime $resolvedAt
     */
    public function setResolvedAt(\DateTime $resolvedAt): void
    {
        $this->resolvedAt = $resolvedAt;
    }

    /**
     * @return bool
     */
    public function isHunt(): ?bool
    {
        return $this->isHunt;
    }

    /**
     * @param bool $isHunt
     */
    public function setIsHunt(bool $isHunt): void
    {
        $this->isHunt = $isHunt;
    }

    /**
     * @return ArrayCollection
     */
    public function getComments(): ?ArrayCollection
    {
        return $this->comments;
    }

    /**
     * @param ArrayCollection $comments
     */
    public function setComments(ArrayCollection $comments): void
    {
        $this->comments = $comments;
    }
}

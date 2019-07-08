<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 16/05/2018
 * Time: 02:25.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 * @ORM\Table(name="message")
 */
class Message
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Character")
     * @ORM\JoinColumn(name="character1_id", referencedColumnName="id")
     */
    private $user1;

    /**
     * @ORM\ManyToOne(targetEntity="Character")
     * @ORM\JoinColumn(name="character2_id", referencedColumnName="id")
     */
    private $user2;

    /**
     * @ORM\ManyToOne(targetEntity="Character")
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="id")
     */
    private $sender;

    /**
     * @ORM\Column(type="string", columnDefinition="text", name="text_content")
     * @Assert\NotBlank(message="Valore obbligatorio")
     */
    private $text;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $isLetter = false;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $isPrivate = false;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $isAnonymous = false;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $isEncoded = false;

    /**
     * Message constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
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
     * @return mixed
     */
    public function getUser1()
    {
        return $this->user1;
    }

    /**
     * @param mixed $user1
     */
    public function setUser1($user1): void
    {
        $this->user1 = $user1;
    }

    /**
     * @return mixed
     */
    public function getUser2()
    {
        return $this->user2;
    }

    /**
     * @param mixed $user2
     */
    public function setUser2($user2): void
    {
        $this->user2 = $user2;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text): void
    {
        $this->text = $text;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->isPrivate;
    }

    /**
     * @param bool $isPrivate
     */
    public function setIsPrivate(bool $isPrivate): void
    {
        $this->isPrivate = $isPrivate;
    }

    /**
     * @return bool
     */
    public function isAnonymous(): bool
    {
        return $this->isAnonymous;
    }

    /**
     * @param bool $isAnonymous
     */
    public function setIsAnonymous(bool $isAnonymous): void
    {
        $this->isAnonymous = $isAnonymous;
    }

    /**
     * @return bool
     */
    public function isEncoded(): bool
    {
        return $this->isEncoded;
    }

    /**
     * @param bool $isEncoded
     */
    public function setIsEncoded(bool $isEncoded): void
    {
        $this->isEncoded = $isEncoded;
    }

    /**
     * @return mixed
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param mixed $sender
     */
    public function setSender($sender): void
    {
        $this->sender = $sender;
    }

    /**
     * @return bool
     */
    public function isLetter(): bool
    {
        return $this->isLetter;
    }

    /**
     * @param bool $isLetter
     */
    public function setIsLetter(bool $isLetter): void
    {
        $this->isLetter = $isLetter;
    }
}

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="downtime_comment")
 */
class DowntimeComment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", columnDefinition="text", name="action")
     */
    private $comment;

    /**
     * @var Downtime
     *
     * @ORM\ManyToOne(targetEntity="Downtime")
     * @ORM\JoinColumn(name="downtime_id", referencedColumnName="id")
     */
    private $downtime;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $author;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

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
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return \App\Entity\Downtime
     */
    public function getDowntime()
    {
        return $this->downtime;
    }

    /**
     * @return \App\Entity\User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @param \App\Entity\Downtime $downtime
     */
    public function setDowntime($downtime)
    {
        $this->downtime = $downtime;
    }

    /**
     * @param \App\Entity\User $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }
}

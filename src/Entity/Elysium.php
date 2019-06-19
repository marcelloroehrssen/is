<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ElysiumRepository")
 * @ORM\Table(name="elysium")
 */
class Elysium
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="admin_author_id", referencedColumnName="id")
     */
    private $adminAuthor;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $address;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * Many Groups have Many Users.
     *
     * @ORM\ManyToMany(targetEntity="ElysiumProposal", mappedBy="validity")
     */
    private $validProposal;

    /**
     * @var ElysiumProposal[]
     *
     * @ORM\OneToMany(targetEntity="ElysiumProposal", mappedBy="elysium", cascade={"remove"})
     */
    private $proposal;

    /**
     * Elysium constructor.
     */
    public function __construct()
    {
        $this->validProposal = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \App\Entity\User
     */
    public function getAdminAuthor()
    {
        return $this->adminAuthor;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return multitype:\App\Entity\ElysiumProposal
     */
    public function getProposal()
    {
        return $this->proposal;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param \App\Entity\User $adminAuthor
     */
    public function setAdminAuthor($adminAuthor)
    {
        $this->adminAuthor = $adminAuthor;
    }

    /**
     * @param DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @param multitype:\App\Entity\ElysiumProposal $proposal
     */
    public function setProposal($proposal)
    {
        $this->proposal = $proposal;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getValidProposal()
    {
        return $this->validProposal;
    }

    /**
     * @param mixed $validProposal
     */
    public function setValidProposal($validProposal): void
    {
        $this->validProposal = $validProposal;
    }
}

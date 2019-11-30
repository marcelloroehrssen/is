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
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="admin_author_id", referencedColumnName="id")
     */
    private $adminAuthor;

    /**
     * @ORM\Column(type="string")
     */
    private $address;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="ElysiumProposal", mappedBy="validity")
     */
    private $validProposal;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ElysiumProposal", mappedBy="elysium", cascade={"remove"})
     */
    private $proposal;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Item", mappedBy="elysia")
     */
    private $items;

    /**
     * Elysium constructor.
     */
    public function __construct()
    {
        $this->validProposal = new ArrayCollection();
        $this->proposal = new ArrayCollection();
        $this->items = new ArrayCollection();
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
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return User
     */
    public function getAdminAuthor()
    {
        return $this->adminAuthor;
    }

    /**
     * @param User $adminAuthor
     */
    public function setAdminAuthor(User $adminAuthor)
    {
        $this->adminAuthor = $adminAuthor;
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
    public function setAddress(string $address)
    {
        $this->address = $address;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     */
    public function setDate(DateTime $date)
    {
        $this->date = $date;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getValidProposal()
    {
        return $this->validProposal;
    }

    /**
     * @param ArrayCollection $validProposal
     */
    public function setValidProposal($validProposal)
    {
        $this->validProposal = $validProposal;
    }

    /**
     * @return ArrayCollection
     */
    public function getProposal()
    {
        return $this->proposal;
    }

    /**
     * @param ArrayCollection $proposal
     */
    public function setProposal(ArrayCollection $proposal)
    {
        $this->proposal = $proposal;
    }

    /**
     * @param Item $item
     */
    public function addItem(Item $item)
    {
        $item->addElysium($this); // synchronously updating inverse side
        $this->items[] = $item;
    }

    /**
     * @param Item $item
     */
    public function removeItem(Item $item)
    {
        $this->items->removeElement($item);
    }
}

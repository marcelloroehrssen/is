<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EquipmentRepository")
 * @ORM\Table(name="equipment")
 * @ORM\HasLifecycleCallbacks()
 */
class Equipment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @ORM\Column(type="string", name="name") */
    private $name;

    /** @ORM\Column(type="integer", name="quantity") */
    private $quantity = 1;

    /** @ORM\Column(type="string", name="description", columnDefinition="text") */
    private $description;

    /** @ORM\Column(type="datetime") */
    private $obtainedAt;

    /**
     * @var Character
     *
     * @ORM\ManyToOne(targetEntity="Character", inversedBy="equipments")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $owner;

    /**
     * @var Character|null
     *
     * @ORM\ManyToOne(targetEntity="Character", inversedBy="equipmentsRequest")
     * @ORM\JoinColumn(name="receiver_user_id", referencedColumnName="id")
     */
    private $receiver;

    /**
     * @ORM\OneToOne(targetEntity="Item", cascade={"remove"})
     */
    private $item;

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
    public function setId($id = null): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @return Character
     */
    public function getOwner():? Character
    {
        return $this->owner;
    }

    /**
     * @param Character $owner
     */
    public function setOwner(Character $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * @return mixed
     */
    public function getObtainedAt()
    {
        return $this->obtainedAt;
    }

    /**
     * @param mixed $obtainedAt
     */
    public function setObtainedAt($obtainedAt): void
    {
        $this->obtainedAt = $obtainedAt;
    }

    /**
     * @return Character|null
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * @param Character|null $receiver
     */
    public function setReceiver(Character $receiver = null): void
    {
        $this->receiver = $receiver;
    }

    /**
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param mixed $item
     */
    public function setItem(Item $item): void
    {
        $this->item = $item;
    }

    /**
     * @ORM\PrePersist
     */
    public function setValues()
    {
        $this->obtainedAt = new \DateTime();
    }
}

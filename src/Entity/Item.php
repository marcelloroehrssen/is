<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ItemRepository")
 * @ORM\Table(name="item")
 * @ORM\HasLifecycleCallbacks()
 */
class Item
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $canKeep = false;

    /**
     * @ORM\Column(type="string")
     */
    private $hash;

    /**
     * @ORM\Column(type="string")
     */
    private $qr;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToOne(targetEntity="Equipment", cascade={"persist"})
     * @ORM\JoinColumn(name="equipment_id", referencedColumnName="id")
     */
    private $equipment;

    /**
     * Many Users have Many Groups.
     * @ORM\ManyToMany(targetEntity="Elysium", inversedBy="items")
     * @ORM\JoinTable(name="elysium_item")
     * @ORM\OrderBy({"date" = "DESC"})
     */
    private $elysia;

    /**
     * @ORM\OneToMany(targetEntity="Requirement", mappedBy="item", cascade={"remove"})
     */
    private $requirements;

    /**
     * Item constructor.
     */
    public function __construct()
    {
        $this->elysia = new ArrayCollection();
        $this->requirements = new ArrayCollection();
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
     * @return bool
     */
    public function isCanKeep(): bool
    {
        return $this->canKeep;
    }

    /**
     * @param bool $canKeep
     */
    public function setCanKeep(bool $canKeep): void
    {
        $this->canKeep = $canKeep;
    }

    /**
     * @return mixed
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param mixed $hash
     */
    public function setHash($hash): void
    {
        $this->hash = $hash;
    }

    /**
     * @return mixed
     */
    public function getQr()
    {
        return $this->qr;
    }

    /**
     * @param mixed $qr
     */
    public function setQr($qr): void
    {
        $this->qr = $qr;
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
     * @return Equipment
     */
    public function getEquipment()
    {
        return $this->equipment;
    }

    /**
     * @param mixed $equipment
     */
    public function setEquipment(?Equipment $equipment): void
    {
        if (null !== $equipment) {
            $equipment->setItem($this);
        } else {
            if ($this->getEquipment() !== null) {
                $this->getEquipment()->setItem(null);
            }
        }
        $this->equipment = $equipment;
    }

    /**
     * @return ArrayCollection
     */
    public function getElysia()
    {
        return $this->elysia;
    }

    /**
     * @param $elysia
     */
    public function setElysia($elysia)
    {
        $this->addElysium($elysia);
    }

    /**
     * @param Elysium $elysium
     */
    public function addElysium(Elysium $elysium)
    {
        $this->elysia[] = $elysium;
    }

    /**
     * @param Elysium $elysium
     */
    public function removeElysium(Elysium $elysium)
    {
        if (!$this->elysia->contains($elysium)) {
            return;
        }

        $this->elysia->removeElement($elysium);
        $elysium->removeItem($this);
    }

    /**
     * @return ArrayCollection
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * @param ArrayCollection $requirements
     */
    public function setRequirements(ArrayCollection $requirements): void
    {
        $this->requirements = $requirements;
    }

    /**
     * @ORM\PrePersist
     */
    public function setValues()
    {
        $this->hash = md5(uniqid());
        $this->createdAt = new \DateTime();
        $this->qr = '';
    }
}

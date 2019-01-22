<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="merits")
 */
class Merits
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $level;

    /**
     * @ORM\Column(type="string", columnDefinition="text", name="`associated_downtime`")
     */
    private $associatedDowntime;

    /**
     * Many Groups have Many Users.
     *
     * @ORM\ManyToMany(targetEntity="Character", mappedBy="merits")
     */
    private $characters;

    public function __construct()
    {
        $this->characters = new ArrayCollection();
    }

    /**
     * Get the value of Id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of Id.
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
     * Get the value of Name.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of Name.
     *
     * @param mixed name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get the value of Level.
     *
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set the value of Level.
     *
     * @param mixed level
     *
     * @return self
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * Get the value of Associated Downtime.
     *
     * @return mixed
     */
    public function getAssociatedDowntime()
    {
        return $this->associatedDowntime;
    }

    /**
     * Set the value of Associated Downtime.
     *
     * @param mixed associatedDowntime
     *
     * @return self
     */
    public function setAssociatedDowntime($associatedDowntime)
    {
        $this->associatedDowntime = $associatedDowntime;
    }

    /**
     * Get the value of Many Groups have Many Users.
     *
     * @return mixed
     */
    public function getCharacters()
    {
        return $this->characters;
    }

    /**
     * Set the value of Many Groups have Many Users.
     *
     * @param mixed characters
     *
     * @return self
     */
    public function addCharacter(Character $character)
    {
        $this->characters[] = $character;
    }

    public function __toString()
    {
        return $this->name;
    }
}

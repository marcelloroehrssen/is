<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 23/05/2018
 * Time: 02:41
 */

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContactRepository")
 * @ORM\Table(name="contact")
 */
class Contact
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Character
     *
     * @ORM\ManyToOne(targetEntity="Character", inversedBy="contacts")
     */
    private $character1;

    /**
     * @var Character
     *
     * @ORM\ManyToOne(targetEntity="Character", inversedBy="hasMyContact")
     */
    private $character2;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false, options={"default":true})
     */
    private $character1Confirmed = false;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false, options={"default":false})
     */
    private $character2Confirmed = false;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $character1RequestDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $character2RequestDate;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $isForced = false;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return Character
     */
    public function getCharacter1(): Character
    {
        return $this->character1;
    }

    /**
     * @param Character $character1
     */
    public function setCharacter1(Character $character1): void
    {
        $this->character1 = $character1;
    }

    /**
     * @return Character
     */
    public function getCharacter2(): Character
    {
        return $this->character2;
    }

    /**
     * @param Character $character2
     */
    public function setCharacter2(Character $character2): void
    {
        $this->character2 = $character2;
    }

    /**
     * @return bool
     */
    public function isCharacter1Confirmed(): bool
    {
        return $this->character1Confirmed;
    }

    /**
     * @param bool $character1Confirmed
     */
    public function setCharacter1Confirmed(bool $character1Confirmed): void
    {
        $this->character1Confirmed = $character1Confirmed;
    }

    /**
     * @return bool
     */
    public function isCharacter2Confirmed(): bool
    {
        return $this->character2Confirmed;
    }

    /**
     * @param bool $character2Confirmed
     */
    public function setCharacter2Confirmed(bool $character2Confirmed): void
    {
        $this->character2Confirmed = $character2Confirmed;
    }

    /**
     * @return \DateTime
     */
    public function getCharacter1RequestDate(): \DateTime
    {
        return $this->character1RequestDate;
    }

    /**
     * @param \DateTime $character1RequestDate
     */
    public function setCharacter1RequestDate(\DateTime $character1RequestDate): void
    {
        $this->character1RequestDate = $character1RequestDate;
    }

    /**
     * @return \DateTime
     */
    public function getCharacter2RequestDate(): \DateTime
    {
        return $this->character2RequestDate;
    }

    /**
     * @param \DateTime $character2RequestDate
     */
    public function setCharacter2RequestDate(\DateTime $character2RequestDate): void
    {
        $this->character2RequestDate = $character2RequestDate;
    }

    /**
     * @return bool
     */
    public function isForced(): bool
    {
        return $this->isForced;
    }

    /**
     * @param bool $isForced
     */
    public function setIsForced(bool $isForced): void
    {
        $this->isForced = $isForced;
    }
}

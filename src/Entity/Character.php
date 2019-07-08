<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CharacterRepository")
 * @ORM\Table(name="`character`")
 * @ORM\HasLifecycleCallbacks()
 */
class Character
{
    public const TYPE_PNG = 'PNG';
    public const TYPE_PG = 'PG';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="character_name")
     */
    private $characterName;

    /**
     * @ORM\Column(type="string", name="character_name_key_url")
     */
    private $characterNameKeyUrl;

    /** @ORM\Column(type="string", name="type") */
    private $type;

    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $canCreateEdict = false;

    /**
     * @ORM\Column(type="integer", options={"default":0})
     */
    private $cacophonySavy = 0;

    /**
     * @ORM\Column(type="string", name="photo", nullable=true)
     */
    private $photo;

    /**
     * @ORM\Column(type="integer", name="major_dt", options={"default":2})
     */
    private $majorDt = 2;

    /**
     * @ORM\Column(type="integer", name="minor_dt", options={"default":3})
     */
    private $minorDt = 3;

    /**
     * @ORM\OneToOne(targetEntity="CharacterExtra")
     * @ORM\JoinColumn(name="extra_id", referencedColumnName="id")
     */
    private $extra;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="characters", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="CharacterPhoto", mappedBy="character", cascade={"remove"})
     */
    private $photos;

    /**
     * @ORM\ManyToOne(targetEntity="Clan")
     * @ORM\JoinColumn(name="clan_id", referencedColumnName="id")
     */
    private $clan;

    /**
     * @ORM\ManyToOne(targetEntity="Covenant")
     * @ORM\JoinColumn(name="covenant_id", referencedColumnName="id")
     */
    private $covenant;

    /**
     * @ORM\ManyToOne(targetEntity="Rank")
     * @ORM\JoinColumn(name="rank_id", referencedColumnName="id")
     */
    private $rank;

    /**
     * @ORM\ManyToOne(targetEntity="Figs")
     * @ORM\JoinColumn(name="figs_id", referencedColumnName="id")
     */
    private $figs;

    /**
     * The people who I think are my friends.
     *
     * @ORM\OneToMany(targetEntity="Contact", mappedBy="character1")
     */
    private $contacts;

    /**
     * The people who think that I’m their friend.
     *
     * @ORM\OneToMany(targetEntity="Contact", mappedBy="character2")
     */
    private $hasMyContact;

    /**
     * Many Users have Many Groups.
     *
     * @ORM\ManyToMany(targetEntity="Merits", inversedBy="characters")
     * @ORM\JoinTable(name="characters_merits")
     */
    private $merits;

    /**
     * @ORM\OneToMany(targetEntity="Equipment", mappedBy="owner")
     */
    private $equipments;

    /**
     * @ORM\OneToMany(targetEntity="Equipment", mappedBy="receiver")
     */
    private $equipmentsRequest;

    /**
     * Character constructor.
     */
    public function __construct()
    {
        $this->contacts = new ArrayCollection();
        $this->hasMyContact = new ArrayCollection();
        $this->merits = new ArrayCollection();
        $this->equipments = new ArrayCollection();
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
    public function getCharacterName()
    {
        return $this->characterName;
    }

    /**
     * @param mixed $characterName
     */
    public function setCharacterName($characterName): void
    {
        $this->characterName = $characterName;
    }

    /**
     * @return mixed
     */
    public function getCharacterNameKeyUrl()
    {
        return $this->characterNameKeyUrl;
    }

    /**
     * @param mixed $characterNameKeyUrl
     */
    public function setCharacterNameKeyUrl($characterNameKeyUrl): void
    {
        $this->characterNameKeyUrl = $characterNameKeyUrl;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @return bool
     */
    public function canCreateEdict(): ?bool
    {
        return $this->canCreateEdict;
    }

    /**
     * @param bool $canCreateEdict
     */
    public function setCanCreateEdict($canCreateEdict): void
    {
        $this->canCreateEdict = $canCreateEdict;
    }

    /**
     * @return int
     */
    public function getCacophonySavy(): ?int
    {
        return $this->cacophonySavy;
    }

    /**
     * @param int $cacophonySavy
     */
    public function setCacophonySavy($cacophonySavy): void
    {
        $this->cacophonySavy = $cacophonySavy;
    }

    /**
     * @param mixed $photo
     */
    public function setPhoto($photo): void
    {
        $this->photo = $photo;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @param mixed $extra
     */
    public function setExtra($extra): void
    {
        $this->extra = $extra;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getPhotos()
    {
        return $this->photos;
    }

    /**
     * @return mixed
     */
    public function getClan()
    {
        return $this->clan;
    }

    /**
     * @param mixed $clan
     */
    public function setClan($clan): void
    {
        $this->clan = $clan;
    }

    /**
     * @return mixed
     */
    public function getCovenant()
    {
        return $this->covenant;
    }

    /**
     * @param mixed $covenant
     */
    public function setCovenant($covenant): void
    {
        $this->covenant = $covenant;
    }

    /**
     * @return mixed
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * @param mixed $rank
     */
    public function setRank($rank): void
    {
        $this->rank = $rank;
    }

    /**
     * @return mixed
     */
    public function getFigs()
    {
        return $this->figs;
    }

    /**
     * @param mixed $figs
     */
    public function setFigs($figs): void
    {
        $this->figs = $figs;
    }

    /**
     * @return ArrayCollection
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @param ArrayCollection $contacts
     */
    public function setContacts($contacts): void
    {
        $this->contacts = $contacts;
    }

    /**
     * @return ArrayCollection
     */
    public function getHasMyContact()
    {
        return $this->hasMyContact;
    }

    /**
     * @param ArrayCollection $hasMyContact
     */
    public function setHasMyContact($hasMyContact): void
    {
        $this->hasMyContact = $hasMyContact;
    }

    /**
     * Get the value of Many Users have Many Groups.
     *
     * @return ArrayCollection
     */
    public function getMerits()
    {
        return $this->merits;
    }

    /**
     * Set the value of Many Users have Many Groups.
     *
     * @param Merits $merits
     */
    public function addMerit(Merits $merits)
    {
        $merits->addCharacter($this);
        $this->merits[] = $merits;
    }

    public function setMerits($merits)
    {
        $this->merits = [];
        foreach ($merits as $merit) {
            $this->addMerit($merit);
        }
    }

    /**
     * @return number
     */
    public function getMajorDt()
    {
        return $this->majorDt;
    }

    /**
     * @return number
     */
    public function getMinorDt()
    {
        return $this->minorDt;
    }

    /**
     * @param number $majorDt
     */
    public function setMajorDt($majorDt)
    {
        $this->majorDt = $majorDt;
    }

    /**
     * @param number $minorDt
     */
    public function setMinorDt($minorDt)
    {
        $this->minorDt = $minorDt;
    }

    /**
     * @ORM\PrePersist
     */
    public function setValues()
    {
        $this->characterNameKeyUrl = str_replace(' ', '-', urlencode(strtolower($this->characterName)));
    }

    public function equals(Character $character)
    {
        return $this->id == $character->getId();
    }

    /**
     * @return mixed
     */
    public function getEquipments()
    {
        return $this->equipments;
    }

    public function addEquipment(Equipment $equipment)
    {
        $equipment->setOwner($this);
        $this->equipments->add($equipment);
    }

    /**
     * @return mixed
     */
    public function getEquipmentsRequest()
    {
        return $this->equipmentsRequest;
    }
}

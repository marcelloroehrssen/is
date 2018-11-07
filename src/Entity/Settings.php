<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="settings")
 */
class Settings
{
    const ALL_UP = 8191;
    const ALL_DOWN = 0;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @ORM\Column(type="string", name="site_value", nullable=true) */
    private $siteValue = self::ALL_UP;

    /** @ORM\Column(type="string", name="mail_value", nullable=true) */
    private $mailValue = self::ALL_DOWN;

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
     * @return mixed
     */
    public function getSiteValue()
    {
        return $this->siteValue;
    }

    /**
     * @param mixed $siteValue
     */
    public function setSiteValue($siteValue)
    {
        $this->siteValue = $siteValue;
    }

    /**
     * @return mixed
     */
    public function getMailValue()
    {
        return $this->mailValue;
    }

    /**
     * @param mixed $mailValue
     */
    public function setMailValue($mailValue)
    {
        $this->mailValue = $mailValue;
    }

}
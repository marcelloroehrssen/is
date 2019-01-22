<?php

namespace App\Form\ValueObject;

class ElysiumCreateVo
{
    /**
     * @var string
     */
    protected $locationName;

    /**
     * @var string
     */
    protected $address;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @return string
     */
    public function getLocationName(): ? string
    {
        return $this->locationName;
    }

    /**
     * @param string $locationName
     */
    public function setLocationName(string $locationName): void
    {
        $this->locationName = $locationName;
    }

    /**
     * @return string
     */
    public function getAddress(): ? string
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): ? \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date): void
    {
        $this->date = $date;
    }
}

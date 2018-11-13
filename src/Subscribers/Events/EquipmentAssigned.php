<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 07/11/2018
 * Time: 19:37
 */

namespace App\Subscribers\Events;


use App\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class EquipmentAssigned extends Event
{
    const NAME = 'equipment.assigned';


    private $equipment;

    private $method;

    /**
     * PublishNewCharacterEvent constructor.
     * @param $equipment
     * @param $method
     */
    public function __construct($equipment, $method)
    {
        $this->equipment = $equipment;
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getEquipment()
    {
        return $this->equipment;
    }

    /**
     * @param mixed $equipment
     */
    public function setEquipment($equipment): void
    {
        $this->equipment = $equipment;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method): void
    {
        $this->method = $method;
    }
}
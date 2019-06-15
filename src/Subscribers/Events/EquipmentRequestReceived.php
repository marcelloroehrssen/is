<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 12/11/2018
 * Time: 00:38.
 */

namespace App\Subscribers\Events;

use Symfony\Component\EventDispatcher\Event;

class EquipmentRequestReceived extends Event
{
    const NAME = 'equipment.request.assigned';

    private $equipment;

    private $method;

    /**
     * EquipmentRequestReceived constructor.
     *
     * @param $equipment
     * @param $sender
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

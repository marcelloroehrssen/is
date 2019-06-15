<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 12/11/2018
 * Time: 01:02.
 */

namespace App\Subscribers\Events;

use Symfony\Component\EventDispatcher\Event;

class EquipmentRequestDenied extends Event
{
    const NAME = 'equipment.request.denied';

    private $equipment;

    private $sender;

    private $method;

    /**
     * EquipmentRequestReceived constructor.
     *
     * @param $equipment
     * @param $sender
     * @param $method
     */
    public function __construct($equipment, $sender, $method)
    {
        $this->equipment = $equipment;
        $this->sender = $sender;
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
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param mixed $sender
     */
    public function setSender($sender): void
    {
        $this->sender = $sender;
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

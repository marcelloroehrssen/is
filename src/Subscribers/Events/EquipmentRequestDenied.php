<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 12/11/2018
 * Time: 01:02.
 */

namespace App\Subscribers\Events;

use App\Entity\Character;
use App\Entity\Equipment;
use Symfony\Component\EventDispatcher\Event;

class EquipmentRequestDenied extends Event
{
    const NAME = 'equipment.request.denied';

    /**
     * @var Equipment
     */
    private $equipment;

    /**
     * @var Character
     */
    private $sender;

    /**
     * @var string
     */
    private $method;

    /**
     * EquipmentRequestReceived constructor.
     *
     * @param Equipment $equipment
     * @param Character $sender
     * @param string $method
     */
    public function __construct(Equipment $equipment, Character $sender, string $method)
    {
        $this->equipment = $equipment;
        $this->sender = $sender;
        $this->method = $method;
    }

    /**
     * @return Equipment
     */
    public function getEquipment()
    {
        return $this->equipment;
    }

    /**
     * @return Character
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 12/11/2018
 * Time: 00:38.
 */

namespace App\Subscribers\Events;

use App\Entity\Equipment;
use Symfony\Contracts\EventDispatcher\Event;

class EquipmentRequestReceived extends Event
{
    const NAME = 'equipment.request.assigned';

    /**
     * @var Equipment
     */
    private $equipment;

    /**
     * @var string
     */
    private $method;

    /**
     * EquipmentRequestReceived constructor.
     *
     * @param Equipment $equipment
     * @param string $method
     */
    public function __construct(Equipment $equipment, string $method)
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
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }
}

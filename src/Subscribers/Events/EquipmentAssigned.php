<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 07/11/2018
 * Time: 19:37.
 */

namespace App\Subscribers\Events;

use App\Entity\Equipment;
use Symfony\Contracts\EventDispatcher\Event;

class EquipmentAssigned extends Event
{
    const NAME = 'equipment.assigned';

    /**
     * @var Equipment
     */
    private $equipment;

    /**
     * @var string
     */
    private $method;

    /**
     * PublishNewCharacterEvent constructor.
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
     * @return Equipment
     */
    public function getEquipment()
    {
        return $this->equipment;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
}

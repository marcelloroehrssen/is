<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 07/11/2018
 * Time: 19:37.
 */

namespace App\Subscribers\Events;

use App\Entity\Elysium;
use Symfony\Contracts\EventDispatcher\Event;

class EventAssigned extends Event
{
    const NAME = 'event.assigned';

    /**
     * @var Elysium
     */
    private $elysium;

    /**
     * @var string
     */
    private $method;

    /**
     * NewEventCreated constructor.
     *
     * @param Elysium $elysium
     * @param string $method
     */
    public function __construct(Elysium $elysium, string $method)
    {
        $this->elysium = $elysium;
        $this->method = $method;
    }

    /**
     * @return Elysium
     */
    public function getElysium()
    {
        return $this->elysium;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
}

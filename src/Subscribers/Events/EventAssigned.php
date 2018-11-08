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

class EventAssigned extends Event
{
    const NAME = 'event.assigned';

    private $elysium;

    private $method;

    /**
     * NewEventCreated constructor.
     * @param $elysium
     * @param $method
     */
    public function __construct($elysium, $method)
    {
        $this->elysium = $elysium;
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getElysium()
    {
        return $this->elysium;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }
}
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

class DowntimeResolvedEvent extends Event
{
    const NAME = 'downtime.resolved';

    private $character;

    private $downtime;

    private $method;

    /**
     * DowntimeResolvedEvent constructor.
     * @param $character
     * @param $downtime
     * @param $method
     */
    public function __construct($character, $downtime, $method)
    {
        $this->character = $character;
        $this->downtime = $downtime;
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getCharacter()
    {
        return $this->character;
    }

    /**
     * @return mixed
     */
    public function getDowntime()
    {
        return $this->downtime;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }
}
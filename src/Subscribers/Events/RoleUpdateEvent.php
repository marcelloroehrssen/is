<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 07/11/2018
 * Time: 19:37.
 */

namespace App\Subscribers\Events;

use Symfony\Component\EventDispatcher\Event;

class RoleUpdateEvent extends Event
{
    const NAME = 'role.update';

    private $character;

    private $who;

    private $message;

    private $method;

    /**
     * RoleUpdateEvent constructor.
     *
     * @param $character
     * @param $who
     * @param $message
     * @param $method
     */
    public function __construct($character, $who, $message, $method)
    {
        $this->character = $character;
        $this->who = $who;
        $this->message = $message;
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
    public function getWho()
    {
        return $this->who;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }
}

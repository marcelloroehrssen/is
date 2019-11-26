<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 07/11/2018
 * Time: 19:37.
 */

namespace App\Subscribers\Events;

use App\Entity\Character;
use Symfony\Contracts\EventDispatcher\Event;

class RoleUpdateEvent extends Event
{
    const NAME = 'role.update';

    /**
     * @var Character
     */
    private $character;

    /**
     * @var string
     */
    private $who;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $method;

    /**
     * RoleUpdateEvent constructor.
     *
     * @param Character $character
     * @param string $who
     * @param string $message
     * @param string $method
     */
    public function __construct(Character $character, string $who, string $message, string $method)
    {
        $this->character = $character;
        $this->who = $who;
        $this->message = $message;
        $this->method = $method;
    }

    /**
     * @return Character
     */
    public function getCharacter()
    {
        return $this->character;
    }

    /**
     * @return string
     */
    public function getWho()
    {
        return $this->who;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 07/11/2018
 * Time: 19:37.
 */

namespace App\Subscribers\Events;

use Symfony\Component\EventDispatcher\Event;

class ConnectionRemovedEvent extends Event
{
    const NAME = 'connection.removed';

    private $character1;

    private $character2;

    private $method;

    /**
     * ConnectionDoneEvent constructor.
     *
     * @param $character1
     * @param $character2
     * @param $method
     */
    public function __construct($character1, $character2, $method)
    {
        $this->character1 = $character1;
        $this->character2 = $character2;
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getCharacter1()
    {
        return $this->character1;
    }

    /**
     * @return mixed
     */
    public function getCharacter2()
    {
        return $this->character2;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }
}

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

class ConnectionDoneEvent extends Event
{
    const NAME = 'connection.done';

    /**
     * @var Character
     */
    private $character1;

    /**
     * @var Character
     */
    private $character2;

    /**
     * @var bool
     */
    private $isForced;

    /**
     * @var string
     */
    private $method;

    /**
     * ConnectionDoneEvent constructor.
     *
     * @param Character $character1
     * @param Character $character2
     * @param bool $isForced
     * @param string $method
     */
    public function __construct(Character $character1, Character $character2, bool $isForced, string $method)
    {
        $this->character1 = $character1;
        $this->character2 = $character2;
        $this->isForced = $isForced;
        $this->method = $method;
    }

    /**
     * @return Character
     */
    public function getCharacter1()
    {
        return $this->character1;
    }

    /**
     * @return Character
     */
    public function getCharacter2()
    {
        return $this->character2;
    }

    /**
     * @return bool
     */
    public function getisForced()
    {
        return $this->isForced;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
}

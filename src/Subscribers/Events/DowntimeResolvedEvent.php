<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 07/11/2018
 * Time: 19:37.
 */

namespace App\Subscribers\Events;

use App\Entity\Character;
use App\Entity\Downtime;
use Symfony\Contracts\EventDispatcher\Event;

class DowntimeResolvedEvent extends Event
{
    const NAME = 'downtime.resolved';

    /**
     * @var Character
     */
    private $character;

    /**
     * @var Downtime
     */
    private $downtime;

    /**
     * @var string
     */
    private $method;

    /**
     * DowntimeResolvedEvent constructor.
     *
     * @param Character $character
     * @param Downtime $downtime
     * @param string $method
     */
    public function __construct(Character $character, Downtime $downtime, string $method)
    {
        $this->character = $character;
        $this->downtime = $downtime;
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
     * @return Downtime
     */
    public function getDowntime()
    {
        return $this->downtime;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
}

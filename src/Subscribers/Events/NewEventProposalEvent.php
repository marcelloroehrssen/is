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

class NewEventProposalEvent extends Event
{
    const NAME = 'event.proposal';

    /**
     * @var Character
     */
    private $character;

    /**
     * @var string
     */
    private $method;

    /**
     * NewEventProposalEvent constructor.
     *
     * @param Character $character
     * @param string $method
     */
    public function __construct(Character $character, string $method)
    {
        $this->character = $character;
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
    public function getMethod(): string
    {
        return $this->method;
    }
}

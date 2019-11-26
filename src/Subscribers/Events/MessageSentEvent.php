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

class MessageSentEvent extends Event
{
    const NAME = 'message.sent';

    /**
     * @var Character
     */
    private $sender;

    /**
     * @var Character
     */
    private $recipient;

    /**
     * @var bool
     */
    private $isLetter;

    /**
     * @var string
     */
    private $method;

    /**
     * MessageSentEvent constructor.
     *
     * @param Character $sender
     * @param Character $recipient
     * @param bool $isLetter
     * @param string $method
     */
    public function __construct(Character $sender, Character $recipient, bool $isLetter, string $method)
    {
        $this->sender = $sender;
        $this->recipient = $recipient;
        $this->isLetter = $isLetter;
        $this->method = $method;
    }

    /**
     * @return Character
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @return Character
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @return bool
     */
    public function getIsLetter()
    {
        return $this->isLetter;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
}

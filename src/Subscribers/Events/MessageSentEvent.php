<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 07/11/2018
 * Time: 19:37.
 */

namespace App\Subscribers\Events;

use Symfony\Component\EventDispatcher\Event;

class MessageSentEvent extends Event
{
    const NAME = 'message.sent';

    private $sender;

    private $recipient;

    private $isLetter;

    private $method;

    /**
     * MessageSentEvent constructor.
     *
     * @param $sender
     * @param $recipient
     * @param $method
     */
    public function __construct($sender, $recipient, $isLetter, $method)
    {
        $this->sender = $sender;
        $this->recipient = $recipient;
        $this->isLetter = $isLetter;
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @return mixed
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @return mixed
     */
    public function getIsLetter()
    {
        return $this->isLetter;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }
}

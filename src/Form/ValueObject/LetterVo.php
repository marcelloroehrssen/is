<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 19/11/2018
 * Time: 00:55
 */

namespace App\Form\ValueObject;


use App\Entity\Character;

class LetterVo
{
    /**
     * @var Character
     */
    private $sender;

    /**
     * @var Character
     */
    private $recipient;

    /**
     * @var string
     */
    private $text;

    /**
     * @return Character
     */
    public function getSender(): Character
    {
        return $this->sender;
    }

    /**
     * @param Character $sender
     */
    public function setSender(Character $sender): void
    {
        $this->sender = $sender;
    }

    /**
     * @return Character
     */
    public function getRecipient(): ?Character
    {
        return $this->recipient;
    }

    /**
     * @param Character $recipient
     */
    public function setRecipient(Character $recipient): void
    {
        $this->recipient = $recipient;
    }

    /**
     * @return string
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }
}
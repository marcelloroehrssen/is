<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 07/11/2018
 * Time: 19:37.
 */

namespace App\Subscribers\Events;

use Symfony\Component\EventDispatcher\Event;

class AssociateCharacterEvent extends Event
{
    const NAME = 'associate.character';

    private $character;

    private $method;

    /**
     * PublishNewCharacterEvent constructor.
     *
     * @param $character
     * @param $method
     */
    public function __construct($character, $method)
    {
        $this->character = $character;
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
    public function getMethod()
    {
        return $this->method;
    }
}

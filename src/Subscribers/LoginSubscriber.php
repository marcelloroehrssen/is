<?php

namespace App\Subscribers;

use App\Utils\MessageSystem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LoginSubscriber implements EventSubscriberInterface
{
    private $priority = 10;

    private $messageSystem;

    private $tokenStorage;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        MessageSystem $messageSystem)
    {
        $this->tokenStorage = $tokenStorage;
        $this->messageSystem = $messageSystem;
    }

    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return [
            SecurityEvents::INTERACTIVE_LOGIN => [
               ['updateLastLogin', 10],
           ],
        ];
    }

    public function updateLastLogin(InteractiveLoginEvent $event)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $this->messageSystem->updateLastMessageSeen($user);
    }
}

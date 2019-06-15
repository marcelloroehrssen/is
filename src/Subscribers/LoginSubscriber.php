<?php

namespace App\Subscribers;


use App\Controller\MessengerController;
use App\Utils\MessageSystem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class LoginSubscriber implements EventSubscriberInterface
{
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
        return [
            KernelEvents::RESPONSE => 'onKernelController',
        ];
    }
    
    public function onKernelController(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        [$controller, $action] = explode('::', $event->getRequest()->get('_controller'));
        $user = $user = $this->tokenStorage->getToken()->getUser();
        if ($controller == MessengerController::class && $user !== null) {
            $this->messageSystem->updateLastMessageSeen($user);
        }
    }
}

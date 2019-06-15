<?php

namespace App\Subscribers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use App\NoCharacterException;

class HasCharacterSubscriber implements EventSubscriberInterface
{
    private $priority = 10;

    private $tokenStorage;

    private $urlGenerator;

    public function __construct(TokenStorageInterface $token, UrlGeneratorInterface $generator)
    {
        $this->tokenStorage = $token;
        $this->urlGenerator = $generator;
    }

    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return [
            KernelEvents::EXCEPTION => [
               ['hasCharacter', 10],
           ],
        ];
    }

    public function hasCharacter(GetResponseForExceptionEvent $event)
    {
        if ($event->getException() instanceof NoCharacterException) {
            $redirectionUrl = $this->urlGenerator->generate('no-character');
            $event->setResponse(new RedirectResponse($redirectionUrl));
        }
    }
}

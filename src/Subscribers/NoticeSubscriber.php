<?php

namespace App\Subscribers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Cookie;

class NoticeSubscriber implements EventSubscriberInterface
{
    private $priority = 10;

    private $flashBag;

    private $router;

    private const WHATS_NEW_NUMBER = 'WN1';

    public function __construct(SessionInterface $session, UrlGeneratorInterface $router)
    {
        $this->flashBag = $session->getFlashBag();
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return [
            KernelEvents::REQUEST => [
               ['setNotice', 10],
            ],
            KernelEvents::RESPONSE => [
                ['setCookie', 10],
            ],
        ];
    }

    public function setNotice(GetResponseEvent $event)
    {
        if ($event->isMasterRequest()
                && 1 != $event->getRequest()->cookies->get(self::WHATS_NEW_NUMBER)) {
            $this->flashBag->add(
                'notice', sprintf('E\' stata aggiornata la sezione dei <a href="%s">Messaggi</a> con la possibilità di inviare lettere', $this->router->generate('choose-messenger'))
            );
        }
    }

    public function setCookie(FilterResponseEvent $event)
    {
        $event->getResponse()->headers->setCookie(new Cookie(self::WHATS_NEW_NUMBER, 1));
    }
}

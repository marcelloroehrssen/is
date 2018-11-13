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
    
    public function __construct(SessionInterface $session, UrlGeneratorInterface $router)
    {
        $this->flashBag = $session->getFlashBag();
        $this->router = $router;
    }
    
    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return array(
            KernelEvents::REQUEST => array(
               array('setNotice', 10),
            ),
            KernelEvents::RESPONSE => array(
                array('setCookie', 10),
            )
        );
    }
    
    public function setNotice(GetResponseEvent $event)
    {
        if ($event->isMasterRequest() 
                && $event->getRequest()->cookies->get('WN1') != 1) {
                    
            $this->flashBag->add(
                'notice', sprintf('E\' disponibile la nuova sezione <a href="%s">Equipaggiamento</a>', $this->router->generate('equipment-index'))
            );
        }
    }
    
    public function setCookie(FilterResponseEvent $event)
    {
        $event->getResponse()->headers->setCookie(new Cookie('WN1', 1));
    }
}
<?php

namespace App\Subscribers;

use App\Entity\Notifications;
use App\Entity\User;
use App\Utils\SettingsSystem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PGMailNotificationSubscriber extends PGSiteNotificationSubscriber implements EventSubscriberInterface
{
    private $priority = 10;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * PGMailNotificationSubscriber constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param SettingsSystem $settingsSystem
     * @param UrlGeneratorInterface $generator
     * @param Packages $packages
     * @param \Swift_Mailer $mailer
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SettingsSystem $settingsSystem,
        UrlGeneratorInterface $generator,
        Packages $packages,
        \Swift_Mailer $mailer
    )
    {
        parent::__construct($entityManager, $settingsSystem, $generator, $packages);
        $this->mailer = $mailer;
    }

    protected function checkSetting(User $user,  string $method)
    {
        return $this->settingsSystem->checkMailSetting($user, $method);
    }

    protected function sendNotification($image, $link, $title, $message, $recipient)
    {
        $mail = new \Swift_Message($title);
        $mail->setSender('info@imperiumsanguinis.it');
        $mail->setTo('marcello.roehrssen@gmail.com');
        $mail->setCharset('UTF-8');
        $mail->setBody($message);

        $this->mailer->send($mail);

        $notifications = new Notifications();
        $notifications->setImage($image);
        $notifications->setLink($link);
        $notifications->setTitle($title);
        $notifications->setMessage($message);
        $notifications->setUser($recipient);

        $this->entityManager->persist($notifications);
        $this->entityManager->flush();
    }
}
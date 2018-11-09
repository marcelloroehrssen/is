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
    protected $priority = 10;

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
        $mail = new \Swift_Message();
        $mail->setSubject($title);
        $mail->setFrom(['info@imperiumsanguinis.it' => 'Imperium Sanguinis']);
        $mail->setCharset('utf-8');
        $mail->setTo([$recipient->getEmail() => $recipient->getUsername()]);
        $mail->setContentType('text/html');
        $mail->setBody($this->render(
            'mail/base.html.twig',
            [
                'user' => $recipient,
                'message' => $message,
                'image' => $image,
                'link' => $link
            ]
        ),'text/html');

        $this->mailer->send($mail);
    }
}
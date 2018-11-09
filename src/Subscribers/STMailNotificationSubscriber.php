<?php

namespace App\Subscribers;

use App\Entity\Notifications;
use App\Entity\User;
use App\Subscribers\Events\AssociateCharacterEvent;
use App\Subscribers\Events\ConnectionDoneEvent;
use App\Subscribers\Events\ConnectionRemovedEvent;
use App\Subscribers\Events\ConnectionSendEvent;
use App\Subscribers\Events\DeletedCharacterEvent;
use App\Subscribers\Events\DowntimeResolvedEvent;
use App\Subscribers\Events\EventAssigned;
use App\Subscribers\Events\MessageSentEvent;
use App\Subscribers\Events\NewEventProposalEvent;
use App\Subscribers\Events\PublishNewCharacterEvent;
use App\Subscribers\Events\PublishNewCharacterSheetEvent;
use App\Subscribers\Events\RoleUpdateEvent;
use App\Utils\SettingsSystem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class STMailNotificationSubscriber extends STSiteNotificationSubscriber implements EventSubscriberInterface
{
    protected $priority = 10;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var \Twig_Environment
     */
    private $twig;

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
        \Swift_Mailer $mailer,
        \Twig_Environment $twig
    )
    {
        parent::__construct($entityManager, $settingsSystem, $generator, $packages);
        $this->mailer = $mailer;
        $this->twig = $twig;
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
        $mail->setBody($this->twig->render(
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
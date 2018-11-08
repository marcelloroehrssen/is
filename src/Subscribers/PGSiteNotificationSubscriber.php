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
use App\Subscribers\Events\NewEventCreated;
use App\Subscribers\Events\NewEventProposalEvent;
use App\Subscribers\Events\PublishNewCharacterEvent;
use App\Subscribers\Events\PublishNewCharacterSheetEvent;
use App\Subscribers\Events\RoleUpdateEvent;
use App\Utils\SettingsSystem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PGSiteNotificationSubscriber implements EventSubscriberInterface
{
    protected $priority = 10;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var SettingsSystem
     */
    protected $settingsSystem;

    /**
     * @var UrlGeneratorInterface
     */
    protected $generator;

    /**
     * @var Packages
     */
    protected $packages;


    public function __construct(
            EntityManagerInterface $entityManager,
            SettingsSystem $settingsSystem,
            UrlGeneratorInterface $generator,
            Packages $packages
        )
    {
        $this->entityManager = $entityManager;
        $this->settingsSystem = $settingsSystem;
        $this->generator = $generator;
        $this->packages = $packages;
    }

    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return array(
            AssociateCharacterEvent::NAME => array(
                array('associateCharacter', 10),
            ),
            PublishNewCharacterSheetEvent::NAME => array(
                array('publishNewCharacterSheet', 10),
            ),
            MessageSentEvent::NAME => array(
                array('messageSent', 10),
            ),
            RoleUpdateEvent::NAME => array(
                array('roleUpdated', 10),
            ),
            ConnectionDoneEvent::NAME => array(
                array('connectionDone', 10),
            ),
            ConnectionRemovedEvent::NAME => array(
                array('connectionRemoved', 10),
            ),
            ConnectionSendEvent::NAME => array(
                array('connectionSend', 10),
            ),
            DowntimeResolvedEvent::NAME => array(
                array('downtimeResolved', 10),
            ),
            NewEventCreated::NAME => array(
                array('eventCreated', 10),
            ),
            NewEventProposalEvent::NAME => array(
                array('eventProposal', 10),
            ),
            EventAssigned::NAME => array(
                array('eventAssigned', 10),
            ),
        );
    }

    public function publishNewCharacterSheet(PublishNewCharacterSheetEvent $event)
    {
        $character = $event->getCharacter();

        $image = "//ui-avatars.com/api/?name=".$character->getCharacterName()."&size=50&rounded=true";
        if (!empty($character->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/' . $character->getPhoto());
        }

        if ($character->getUser() === null) {
            return;
        }

        if (!$this->checkSetting($character->getUser(), $event->getMethod())) {
            return;
        }

        $this->sendNotification(
            $image,
            $this->generator->generate('character', ['characterNameKeyUrl' => $character->getCharacterNameKeyUrl()]),
            "Nuova scheda",
            "Per il tuo personagio è disponibile una nuova scheda",
            $character->getUser()->getId()
        );
    }

    public function associateCharacter(AssociateCharacterEvent $event)
    {
        $character = $event->getCharacter();

        if (!$this->checkSetting($character->getUser(), $event->getMethod())) {
            return;
        }

        $this->sendNotification(
            "//ui-avatars.com/api/?name=".$character->getCharacterName()."&size=50&rounded=true",
            $this->generator->generate('character', ['characterNameKeyUrl' => $character->getCharacterNameKeyUrl()]),
            "Nuovo Personaggio",
            "Ti è stato associato un nuovo personaggio {$character->getCharacterName()}",
            $character->getUser()->getId()
        );
    }

    public function messageSent(MessageSentEvent $event)
    {
        $characterActor = $event->getSender();
        $recipient = $event->getRecipient();

        if ($recipient->getUser() === null) {
            return;
        }

        if (!$this->checkSetting($recipient->getUser(), $event->getMethod())) {
            return;
        }

        $image = "//ui-avatars.com/api/?name=".$characterActor->getCharacterName()."&size=50&rounded=true";
        if (!empty($characterActor->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/' . $characterActor->getPhoto());
        }

        $this->sendNotification(
            $image,
            $this->generator->generate('messenger_chat', ['characterName' => $characterActor->getCharacterNameKeyUrl()]),
            "Nuovo Messaggio",
            "Hai ricevuto un messaggio da {$characterActor->getCharacterName()}",
            $recipient->getUser()->getId()
        );
    }

    public function roleUpdated(RoleUpdateEvent $event)
    {
        $character = $event->getCharacter();
        $who = $event->getWho();
        $message = $event->getMessage();

        if ($character->getType() === 'PNG') {
            return;
        }

        $image = "//ui-avatars.com/api/?name=".$character->getCharacterName()."&size=50&rounded=true";
        if (!empty($character->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/' . $character->getPhoto());
        }

        if (!$this->checkSetting($character->getUser(), $event->getMethod())) {
            return;
        }
        $this->sendNotification(
            $image,
            $this->generator->generate('character', ['characterNameKeyUrl' => $character->getCharacterNameKeyUrl()]),
            "PG cambiato",
            "$who ha cambiato $message",
            $character->getUser()->getId()
        );
    }

    public function connectionDone(ConnectionDoneEvent $event)
    {
        $character1 = $event->getCharacter1();
        $character2 = $event->getCharacter2();
        $isForced = $event->getisForced();

        $image = "//ui-avatars.com/api/?name=".$character1->getCharacterName()."&size=50&rounded=true";
        if (!empty($character1->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/' . $character1->getPhoto());
        }

        if (!empty($character1->getUser())) {
            if (!$this->checkSetting($character1->getUser(), $event->getMethod())) {
                return;
            }

            $message = "Adesso hai il contatto privato di {$character2->getCharacterName()}";
            if ($isForced) {
                $message = "Per la narrazione adesso hai il contatto privato di {$character2->getCharacterName()}";
            }

            $this->sendNotification(
                $image,
                $this->generator->generate('character', ['characterNameKeyUrl' => $character1->getCharacterNameKeyUrl()]),
                "Contatto privato",
                $message,
                $character1->getUser()->getId()
            );
        }
        $image = "//ui-avatars.com/api/?name=".$character2->getCharacterName()."&size=50&rounded=true";
        if (!empty($character2->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/' . $character2->getPhoto());
        }

        if (!empty($character2->getUser())) {
            if (!$this->checkSetting($character2->getUser(), $event->getMethod())) {
                return;
            }

            $message = "Adesso hai il contatto privato di {$character2->getCharacterName()}";
            if ($isForced) {
                $message = "Per la narrazione adesso hai il contatto privato di {$character1->getCharacterName()}";
            }

            $this->sendNotification(
                $image,
                $this->generator->generate('character', ['characterNameKeyUrl' => $character2->getCharacterNameKeyUrl()]),
                "Contatto privato",
                $message,
                $character2->getUser()->getId()
            );
        }
    }

    public function connectionRemoved(ConnectionRemovedEvent $event)
    {
        $character1 = $event->getCharacter1();
        $character2 = $event->getCharacter2();

        if ($character1->getUser() != null) {
            $image = "//ui-avatars.com/api/?name=".$character1->getCharacterName()."&size=50&rounded=true";
            if (!empty($character1->getPhoto())) {
                $image = $this->packages->getUrl('/uploads/character_photo/' . $character1->getPhoto());
            }
            if (!$this->checkSetting($character1->getUser(), $event->getMethod())) {
                return;
            }
            $this->sendNotification(
                $image,
                $this->generator->generate('character', ['characterNameKeyUrl' => $character1->getCharacterNameKeyUrl()]),
                "Contatto privato",
                "Il contatto privato di {$character1->getCharacterName()} non funziona più",
                $character1->getUser()->getId()
            );
        }

        if ($character2->getUser() != null) {
            $image = "//ui-avatars.com/api/?name=".$character2->getCharacterName()."&size=50&rounded=true";
            if (!empty($character2->getPhoto())) {
                $image = $this->packages->getUrl('/uploads/character_photo/' . $character2->getPhoto());
            }
            if (!$this->checkSetting($character2->getUser(), $event->getMethod())) {
                return;
            }
            $this->sendNotification(
                $image,
                $this->generator->generate('character', ['characterNameKeyUrl' => $character2->getCharacterNameKeyUrl()]),
                "Contatto privato",
                "Il contatto privato di {$character2->getCharacterName()} non funziona più",
                $character2->getUser()->getId()
            );
        }
    }

    public function connectionSend(ConnectionSendEvent $event)
    {
        $character1 = $event->getCharacter1();
        $character2 = $event->getCharacter2();

        $image = "//ui-avatars.com/api/?name=".$character2->getCharacterName()."&size=50&rounded=true";
        if (!empty($character2->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/' . $character2->getPhoto());
        }

        if ($character1->getUser() != null) {
            if (!$this->checkSetting($character1->getUser(), $event->getMethod())) {
                return;
            }
            $this->sendNotification(
                $image,
                $this->generator->generate('character', ['characterNameKeyUrl' => $character2->getCharacterNameKeyUrl()]),
                "Contatto privato",
                "{$character2->getCharacterName()} vuole scambiare il suo contatto privato con te",
                $character1->getUser()->getId()
            );
        }
    }

    public function downtimeResolved(DowntimeResolvedEvent $event)
    {
        $character = $event->getCharacter();
        $downtime = $event->getDowntime();

        $image = "//ui-avatars.com/api/?name=".$character->getCharacterName()."&size=50&rounded=true";
        if (!empty($character->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/' . $character->getPhoto());
        }

        if (!$this->checkSetting($character->getUser(), $event->getMethod())) {
            return;
        }
        $this->sendNotification(
            $image,
            $this->generator->generate('downtime-index'),
            "Risoluzione DT",
            "risolto il dt {$downtime->getTitle()}",
            $character->getUser()->getId()
        );
    }

    public function eventCreated(NewEventCreated $event)
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();

        $elysium = $event->getElysium();

        array_walk(
            $users,
            function(User $user) use ($elysium, $event) {
                if (!$this->checkSetting($user, $event->getMethod())) {
                    return;
                }
                $this->sendNotification(
                    "//ui-avatars.com/api/?name=NE&size=50&rounded=true",
                    $this->generator->generate('event_index'),
                    'Nuovo Live',
                    sprintf('E\' stato indetto un nuovo live per il %s', $elysium->getDate()->format('d/m/Y')),
                    $user->getId()
                );
            }
        );
    }

    public function eventProposal(NewEventProposalEvent $event)
    {
        $proposer = $event->getCharacter();

        $user = $this->entityManager->getRepository(User::class)->findByRole('ROLE_EDILE');
        $edile = array_pop($user);

        if (!$this->checkSetting($edile, $event->getMethod())) {
            return;
        }

        $this->sendNotification(
            "//ui-avatars.com/api/?name=NP&size=50&rounded=true",
            $this->generator->generate('event_index'),
            'Nuova Proposta di Eliseo',
            sprintf('E\' fatta una proposta per un eliseo da %s', empty($proposer) ? 'Imperatore' : $proposer->getCharacterName()),
            $edile->getId()
        );
    }

    public function eventAssigned(EventAssigned $event)
    {
        $elysiym = $event->getElysium();

        if (empty($elysiym->getProposal()->current()->getCharacterAuthor())) {
            return;
        }

        if (!$this->checkSetting($elysiym->getProposal()->current()->getCharacterAuthor(), $event->getMethod())) {
            return;
        }
        $this->sendNotification(
            "//ui-avatars.com/api/?name=NP&size=50&rounded=true",
            $this->generator->generate('event_index'),
            'Proposta approvata',
            sprintf('La tua proposta di Eliseo è stata approvata per il %s',
                $elysiym->getDate()->format('d/m/Y')
            ),
            $elysiym->getProposal()->current()->getCharacterAuthor()->getId()
        );
    }

    protected function checkSetting(User $user,  string $method)
    {
        return $this->settingsSystem->checkSiteSetting($user, $method);
    }

    protected function sendNotification($image, $link, $title, $message, $recipient)
    {
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
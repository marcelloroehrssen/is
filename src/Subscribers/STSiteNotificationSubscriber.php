<?php

namespace App\Subscribers;

use App\Entity\Equipment;
use App\Entity\Notifications;
use App\Entity\User;
use App\Subscribers\Events\AssociateCharacterEvent;
use App\Subscribers\Events\ConnectionDoneEvent;
use App\Subscribers\Events\ConnectionRemovedEvent;
use App\Subscribers\Events\ConnectionSendEvent;
use App\Subscribers\Events\DeletedCharacterEvent;
use App\Subscribers\Events\DowntimeResolvedEvent;
use App\Subscribers\Events\EquipmentAssigned;
use App\Subscribers\Events\EquipmentRequestAccepted;
use App\Subscribers\Events\EquipmentRequestDenied;
use App\Subscribers\Events\EquipmentRequestReceived;
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

class STSiteNotificationSubscriber implements EventSubscriberInterface
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
    ) {
        $this->entityManager = $entityManager;
        $this->settingsSystem = $settingsSystem;
        $this->generator = $generator;
        $this->packages = $packages;
    }

    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return [
            PublishNewCharacterEvent::NAME => [
                ['publishNewCharacter', 10],
            ],
            DeletedCharacterEvent::NAME => [
                ['deleteCharacter', 10],
            ],
            PublishNewCharacterSheetEvent::NAME => [
                ['publishNewCharacterSheet', 10],
            ],
            AssociateCharacterEvent::NAME => [
                ['associateCharacter', 10],
            ],
            MessageSentEvent::NAME => [
                ['messageSent', 10],
            ],
            RoleUpdateEvent::NAME => [
                ['roleUpdated', 10],
            ],
            ConnectionDoneEvent::NAME => [
                ['connectionDone', 10],
            ],
            ConnectionRemovedEvent::NAME => [
                ['connectionRemoved', 10],
            ],
            ConnectionSendEvent::NAME => [
                ['connectionSend', 10],
            ],
            DowntimeResolvedEvent::NAME => [
                ['downtimeResolved', 10],
            ],
            NewEventProposalEvent::NAME => [
                ['newEventProposal', 10],
            ],
            EventAssigned::NAME => [
                ['eventAssigned', 10],
            ],
            EquipmentAssigned::NAME => [
                ['equipmentAssigned', 10],
            ],
        ];
    }

    public function publishNewCharacter(PublishNewCharacterEvent $event)
    {
        $character = $event->getCharacter();
        $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');

        array_walk(
            $users,
            function (User $user) use ($character, $event) {
                if (!$this->checkSetting($user, $event->getMethod())) {
                    return;
                }
                if (null === $character) {
                    return;
                }

                $this->sendNotification(
                    '//ui-avatars.com/api/?name='.$character->getCharacterName().'&size=50&rounded=true',
                    $this->generator->generate('character', ['characterNameKeyUrl' => $character->getCharacterNameKeyUrl()]),
                    'Nuovo personaggio',
                    'è stato creato un nuovo personaggio',
                    $user
                );
            }
        );
    }

    public function deleteCharacter(DeletedCharacterEvent $event)
    {
        $character = $event->getCharacter();
        $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');

        array_walk(
            $users,
            function (User $user) use ($character, $event) {
                if (!$this->checkSetting($user, $event->getMethod())) {
                    return;
                }

                $image = '//ui-avatars.com/api/?name='.$character->getCharacterName().'&size=50&rounded=true';
                if (!empty($character->getPhoto())) {
                    $image = $this->packages->getUrl('/uploads/character_photo/'.$character->getPhoto());
                }

                $this->sendNotification(
                    $image,
                    $this->generator->generate('character'),
                    'Personaggio cancellato',
                    "{$character->getCharacterName()} è stato cancellato",
                    $user
                );
            }
        );
    }

    public function publishNewCharacterSheet(PublishNewCharacterSheetEvent $event)
    {
        $character = $event->getCharacter();
        $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');

        $image = '//ui-avatars.com/api/?name='.$character->getCharacterName().'&size=50&rounded=true';
        if (!empty($character->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/'.$character->getPhoto());
        }

        array_walk(
            $users,
            function (User $user) use ($character, $image, $event) {
                if (!$this->checkSetting($user, $event->getMethod())) {
                    return;
                }

                $this->sendNotification(
                    $image,
                    $this->generator->generate('character', ['characterNameKeyUrl' => $character->getCharacterNameKeyUrl()]),
                    'Nuova scheda',
                    "E' stata caricata una nuova scheda per il personaggio {$character->getCharacterName()}",
                    $user
                );
            }
        );
    }

    public function associateCharacter(AssociateCharacterEvent $event)
    {
        $character = $event->getCharacter();
        $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');

        array_walk(
            $users,
            function (User $user) use ($character, $event) {
                if (!$this->checkSetting($user, $event->getMethod())) {
                    return;
                }
                $this->sendNotification(
                    '//ui-avatars.com/api/?name='.$character->getCharacterName().'&size=50&rounded=true',
                    $this->generator->generate('character', ['characterNameKeyUrl' => $character->getCharacterNameKeyUrl()]),
                    'Personaggio associato',
                    "{$character->getCharacterName()} è stato associato a {$character->getUser()->getUsername()}",
                    $user
                );
            }
        );
    }

    public function messageSent(MessageSentEvent $event)
    {
        $characterActor = $event->getSender();
        $recipient = $event->getRecipient();

        $image = '//ui-avatars.com/api/?name='.$characterActor->getCharacterName().'&size=50&rounded=true';
        if (!empty($characterActor->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/'.$characterActor->getPhoto());
        }

        //we have to send notification to ST only if the recipient is PNG
        if ('PNG' === $recipient->getType()) {
            $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');
            array_walk(
                $users,
                function (User $user) use ($characterActor, $recipient, $image, $event) {
                    if (!$this->checkSetting($user, $event->getMethod())) {
                        return;
                    }

                    $this->sendNotification(
                        $image,
                        $this->generator->generate('messenger_chat', [
                            'characterName' => $characterActor->getCharacterNameKeyUrl(),
                            'png-id' => $recipient->getId(),
                        ]),
                        'Nuovo Messaggio',
                        "{$characterActor->getCharacterName()} ha inviato un messaggio a {$recipient->getCharacterName()}",
                        $user
                    );
                }
            );

            return;
        }
    }

    public function roleUpdated(RoleUpdateEvent $event)
    {
        $character = $event->getCharacter();
        $who = $event->getWho();
        $message = $event->getMessage();

        $image = '//ui-avatars.com/api/?name='.$character->getCharacterName().'&size=50&rounded=true';
        if (!empty($character->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/'.$character->getPhoto());
        }

        $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');
        array_walk(
            $users,
            function (User $user) use ($character, $who, $message, $image, $event) {
                if (!$this->checkSetting($user, $event->getMethod())) {
                    return;
                }
                $this->sendNotification(
                    $image,
                    $this->generator->generate('character', [
                        'characterNameKeyUrl' => $character->getCharacterNameKeyUrl(),
                    ]),
                    'PG cambiato',
                    "$who ha cambiato tipo/clan/congrega/grado/ruolo a {$character->getCharacterName()}",
                    $user
                );
            }
        );
    }

    public function connectionRemoved(ConnectionRemovedEvent $event)
    {
        $character1 = $event->getCharacter1();
        $character2 = $event->getCharacter2();

        $image = '//ui-avatars.com/api/?name='.$character1->getCharacterName().'&size=50&rounded=true';
        if (!empty($character1->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/'.$character1->getPhoto());
        }

        $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');
        array_walk(
            $users,
            function (User $user) use ($character1, $character2, $image, $event) {
                if (!$this->checkSetting($user, $event->getMethod())) {
                    return;
                }
                $this->sendNotification(
                    $image,
                    $this->generator->generate('character', [
                        'characterNameKeyUrl' => $character1->getCharacterNameKeyUrl(),
                    ]),
                    'Contatti privati',
                    "La narrazione ha disconnesso {$character1->getCharacterName()} e {$character2->getCharacterName()}",
                    $user
                );
            }
        );
    }

    public function connectionDone(ConnectionDoneEvent $event)
    {
        $character1 = $event->getCharacter1();
        $character2 = $event->getCharacter2();

        $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');

        $image = '//ui-avatars.com/api/?name='.$character2->getCharacterName().'&size=50&rounded=true';
        if (!empty($character2->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/'.$character2->getPhoto());
        }

        array_walk(
            $users,
            function (User $user) use ($character1, $character2, $image, $event) {
                if (!$this->checkSetting($user, $event->getMethod())) {
                    return;
                }
                $this->sendNotification(
                    $image,
                    $this->generator->generate('character', [
                        'characterNameKeyUrl' => $character1->getCharacterNameKeyUrl(),
                    ]),
                    'Contatti privati',
                    "{$character1->getCharacterName()} e {$character2->getCharacterName()} si sono scambiati i contatti privati",
                    $user
                );
            }
        );
    }

    public function connectionSend(ConnectionSendEvent $event)
    {
        $character1 = $event->getCharacter1();
        $character2 = $event->getCharacter2();

        $image = '//ui-avatars.com/api/?name='.$character2->getCharacterName().'&size=50&rounded=true';
        if (!empty($character2->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/'.$character2->getPhoto());
        }

        if (null == $character1->getUser()) {
            $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');
            array_walk(
                $users,
                function (User $user) use ($character1, $character2, $image, $event) {
                    if (!$this->checkSetting($user, $event->getMethod())) {
                        return;
                    }
                    $this->sendNotification(
                        $image,
                        $this->generator->generate('character', [
                            'characterNameKeyUrl' => $character1->getCharacterNameKeyUrl(),
                        ]),
                        'Contatti privati',
                        "{$character2->getCharacterName()} vuole il contatto privato di {$character1->getCharacterName()}",
                        $user
                    );
                }
            );
        }
    }

    public function downtimeResolved(DowntimeResolvedEvent $event)
    {
        $character = $event->getCharacter();
        $downtime = $event->getDowntime();

        $image = '//ui-avatars.com/api/?name='.$character->getCharacterName().'&size=50&rounded=true';
        if (!empty($character->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/'.$character->getPhoto());
        }

        $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');
        array_walk(
            $users,
            function (User $user) use ($character, $image, $downtime, $event) {
                if (!$this->checkSetting($user, $event->getMethod())) {
                    return;
                }
                $this->sendNotification(
                    $image,
                    $this->generator->generate('downtime-index'),
                    'Risoluzione DT',
                    "il dt  {$downtime->getTitle()} di {$character->getCharacterName()} ha avuto una risoluzione",
                    $user
                );
            }
        );
    }

    public function newEventProposal(NewEventProposalEvent $event)
    {
        $proposer = $event->getCharacter();

        $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');
        array_walk(
            $users,
            function (User $user) use ($proposer, $event) {
                if (!$this->checkSetting($user, $event->getMethod())) {
                    return;
                }
                $this->sendNotification(
                    '//ui-avatars.com/api/?name=NP&size=50&rounded=true',
                    $this->generator->generate('event_index'),
                    'Nuova Proposta di Eliseo',
                    sprintf('E\' fatta una proposta per un eliseo da %s', empty($proposer) ? 'Imperatore' : $proposer->getCharacterName()),
                    $user
                );
            }
        );
    }

    public function eventAssigned(EventAssigned $event)
    {
        $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');

        $elysiym = $event->getElysium();

        array_walk(
            $users,
            function (User $user) use ($elysiym, $event) {
                if (!$this->checkSetting($user, $event->getMethod())) {
                    return;
                }
                $this->sendNotification(
                    '//ui-avatars.com/api/?name=NP&size=50&rounded=true',
                    $this->generator->generate('event_index'),
                    'Eliseo assegnata',
                    sprintf('L\' Eliseo del %s è stato assegnato a %s',
                        $elysiym->getDate()->format('d/m/Y'),
                        !empty($elysiym->getProposal()->current()->getCharacterAuthor()) ?
                            $elysiym->getProposal()->current()->getCharacterAuthor()->getCharacterName()
                            : 'Imperatore'
                    ),
                    $user
                );
            }
        );
    }

    public function equipmentAssigned(EquipmentAssigned $event)
    {
        /** @var Equipment $equipment */
        $equipment = $event->getEquipment();

        $character = $equipment->getOwner();

        $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');

        $image = '//ui-avatars.com/api/?name='.$character->getCharacterName().'&size=50&rounded=true';
        if (!empty($character->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/'.$character->getPhoto());
        }

        array_walk(
            $users,
            function ($user) use ($image, $equipment, $event) {
                if (!$this->checkSetting($user, $event->getMethod())) {
                    return;
                }

                $this->sendNotification(
                    $image,
                    $this->generator->generate('equipment-index'),
                    'Ottenuto oggetto',
                    sprintf('%s ha ottenuto un nuovo oggetto: %s', $equipment->getOwner()->getCharacterName(), $equipment->getName()),
                    $user
                );
            }
        );
    }

    public function equipmentRequestReceived(EquipmentRequestReceived $event)
    {
        $equipment = $event->getEquipment();
        $character = $event->getEquipment()->getReceiver();

        $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');

        $image = '//ui-avatars.com/api/?name='.$character->getCharacterName().'&size=50&rounded=true';
        if (!empty($character->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/'.$character->getPhoto());
        }

        array_walk(
            $users,
            function ($user) use ($image, $equipment, $character, $event) {
                if (!$this->checkSetting($user, $event->getMethod())) {
                    return;
                }

                $this->sendNotification(
                    $image,
                    $this->generator->generate('equipment-index'),
                    'Ottenuto oggetto',
                    sprintf('%s ha riceuto una nuova richiesta per %s da %s',
                        $character->getCharacterName(),
                        $equipment->getName(),
                        $equipment->getOwner()),
                    $user
                );
            }
        );
    }

    public function equipmentRequestAccepted(EquipmentRequestAccepted $event)
    {
        $equipment = $event->getEquipment();
        $character = $event->getSender();

        $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');

        $image = '//ui-avatars.com/api/?name='.$character->getCharacterName().'&size=50&rounded=true';
        if (!empty($character->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/'.$character->getPhoto());
        }

        array_walk(
            $users,
            function ($user) use ($image, $equipment, $character, $event) {
                if (!$this->checkSetting($user, $event->getMethod())) {
                    return;
                }

                $this->sendNotification(
                    $image,
                    $this->generator->generate('equipment-index'),
                    'Richiesta per oggetto accettata',
                    sprintf('La richiesta di %s per %s è stata accettata da %s',
                        $character->getCharacterName(),
                        $equipment->getName(),
                        $equipment->getOwner()->getCharacterName()
                    ),
                    $user
                );
            }
        );
    }

    public function equipmentRequestDenied(EquipmentRequestDenied $event)
    {
        $equipment = $event->getEquipment();
        $character = $event->getSender();

        $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');

        $image = '//ui-avatars.com/api/?name='.$character->getCharacterName().'&size=50&rounded=true';
        if (!empty($character->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/'.$character->getPhoto());
        }

        array_walk(
            $users,
            function ($user) use ($image, $equipment, $character, $event) {
                if (!$this->checkSetting($user, $event->getMethod())) {
                    return;
                }

                $this->sendNotification(
                    $image,
                    $this->generator->generate('equipment-index'),
                    'Richiesta per oggetto accettata',
                    sprintf('La richiesta di %s per %s è stata rifiutata da %s',
                        $equipment->getOwner()->getCharacterName(),
                        $equipment->getName(),
                        $character->getCharacterName()
                    ),
                    $user
                );
            }
        );
    }

    protected function checkSetting(User $user, string $method)
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
        $notifications->setUser($recipient->getId());

        $this->entityManager->persist($notifications);
        $this->entityManager->flush();
    }
}

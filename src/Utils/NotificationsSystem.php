<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 14/05/2018
 * Time: 19:17
 */

namespace App\Utils;


use App\Entity\Character;
use App\Entity\Message;
use App\Entity\Notifications;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\Downtime;

class NotificationsSystem
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UrlGeneratorInterface
     */
    private $generator;

    /**
     * @var Packages
     */
    private $packages;

    /**
     * NotificationsSystem constructor.
     * @param $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $generator, Packages $packages)
    {
        $this->entityManager = $entityManager;
        $this->generator = $generator;
        $this->packages = $packages;
    }


    public function publishNewCharacter(User $actor, $character)
    {
        $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');

        array_walk(
            $users,
            function (User $user) use ($actor, $character) {
                if (null === $character) {
                    return;
                }
                if ($user->getId() == $actor->getId()) {
                    return;
                }

                 $this->sendNotification(
                    "//ui-avatars.com/api/?name=".$character->getCharacterName()."&size=50&rounded=true",
                    $this->generator->generate('character', ['characterNameKeyUrl' => $character->getCharacterNameKeyUrl()]),
                    "Nuovo personaggio",
                    "è stato creato un nuovo personaggio",
                    $user->getId()
                );
            }
        );
    }

    public function deleteCharacter(User $actor, Character $character)
    {
        $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');

        array_walk(
            $users,
            function (User $user) use ($actor, $character) {
                if ($user->getId() == $actor->getId()) {
                    return;
                }

                $image = "//ui-avatars.com/api/?name=".$character->getCharacterName()."&size=50&rounded=true";
                if (!empty($character->getPhoto())) {
                    $image = $this->packages->getUrl('/uploads/character_photo/' . $character->getPhoto());
                }

                 $this->sendNotification(
                    $image,
                    $this->generator->generate('character'),
                    "Personaggio cancellato",
                    "{$actor->getUsername()} ha cancellato il personaggio {$character->getCharacterName()}",
                    $user->getId()
                );
            }
        );
    }

    public function publishNewCharacterSheet(User $actor, Character $character)
    {
        $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');

        $image = "//ui-avatars.com/api/?name=".$character->getCharacterName()."&size=50&rounded=true";
        if (!empty($character->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/' . $character->getPhoto());
        }

        array_walk(
            $users,
            function (User $user) use ($actor, $character, $image) {
                if ($user->getId() == $actor->getId()) {
                    return;
                }

                 $this->sendNotification(
                    $image,
                    $this->generator->generate('character', ['characterNameKeyUrl' => $character->getCharacterNameKeyUrl()]),
                    "Nuova scheda",
                    "{$actor->getUsername()} ha caricato una nuova scheda per il personaggio {$character->getCharacterName()}",
                    $user->getId()
                );
            }
        );
    
        if ($character->getUser() === null) {
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

    public function associateCharacter(User $actor, $character)
    {
        $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');

        array_walk(
            $users,
            function (User $user) use ($actor, $character) {
                if ($user->getId() == $actor->getId()) {
                    return;
                }
                 $this->sendNotification(
                    "//ui-avatars.com/api/?name=".$character->getCharacterName()."&size=50&rounded=true",
                    $this->generator->generate('character', ['characterNameKeyUrl' => $character->getCharacterNameKeyUrl()]),
                    "Personaggio associato",
                    "{$character->getCharacterName()} è stato associato a {$character->getUser()->getUsername()}",
                    $user->getId()
                );
            }
        );

         $this->sendNotification(
            "//ui-avatars.com/api/?name=".$character->getCharacterName()."&size=50&rounded=true",
            $this->generator->generate('character', ['characterNameKeyUrl' => $character->getCharacterNameKeyUrl()]),
            "Nuovo Personaggio",
            "Ti è stato associato un nuovo personaggio {$character->getCharacterName()}",
            $character->getUser()->getId()
        );
    }

    public function messageSent(Character $characterActor, Character $recipient)
    {
        $image = "//ui-avatars.com/api/?name=".$characterActor->getCharacterName()."&size=50&rounded=true";
        if (!empty($characterActor->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/' . $characterActor->getPhoto());
        }

        $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');
        array_walk(
            $users,
            function (User $user) use ($characterActor, $recipient, $image) {

                $this->sendNotification(
                    $image,
                    $this->generator->generate('messenger_chat', [
                        'characterName' => $characterActor->getCharacterNameKeyUrl(),
                        'png-id' => $recipient->getId()
                    ]),
                    "Nuovo Messaggio",
                    "{$characterActor->getCharacterName()} ha inviato un messaggio a {$recipient->getCharacterName()}",
                    $user->getId()
                );
            }
        );

        if ($recipient->getType() === 'PNG') {
            return;
        }

        $this->sendNotification(
            $image,
            $this->generator->generate('messenger_chat', ['characterName' => $characterActor->getCharacterNameKeyUrl()]),
            "Nuovo Messaggio",
            "Hai ricevuto un messaggio da {$characterActor->getCharacterName()}",
            $recipient->getUser()->getId()
        );
    }

    public function roleUpdated($character, $who, $message)
    {
        $image = "//ui-avatars.com/api/?name=".$character->getCharacterName()."&size=50&rounded=true";
        if (!empty($character->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/' . $character->getPhoto());
        }

        $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');
        array_walk(
            $users,
            function (User $user) use ($character, $who, $message, $image) {

                $this->sendNotification(
                    $image,
                    $this->generator->generate('character', [
                        'characterNameKeyUrl' => $character->getCharacterNameKeyUrl()
                    ]),
                    "PG cambiato",
                    "$who ha cambiato tipo/clan/congrega/grado/ruolo a {$character->getCharacterName()}",
                    $user->getId()
                );
            }
        );

        if ($character->getType() === 'PNG') {
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

    public function connectionDone(Character $character1, Character $character2, bool $isForced)
    {
        $image = "//ui-avatars.com/api/?name=".$character1->getCharacterName()."&size=50&rounded=true";
        if (!empty($character1->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/' . $character1->getPhoto());
        }

        if (!empty($character1->getUser())) {
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

        $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');
        array_walk(
            $users,
            function (User $user) use ($character1, $character2, $image) {

                $this->sendNotification(
                    $image,
                    $this->generator->generate('character', [
                        'characterNameKeyUrl' => $character1->getCharacterNameKeyUrl()
                    ]),
                    "Contatti privati",
                    "{$character1->getCharacterName()} e {$character2->getCharacterName()} si sono scambiati i contatti privati",
                    $user->getId()
                );
            }
        );
    }

    public function connectionRemoved(Character $character1, Character $character2)
    {
        if ($character1->getUser() != null) {
            $image = "//ui-avatars.com/api/?name=".$character1->getCharacterName()."&size=50&rounded=true";
            if (!empty($character1->getPhoto())) {
                $image = $this->packages->getUrl('/uploads/character_photo/' . $character1->getPhoto());
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

            $this->sendNotification(
                $image,
                $this->generator->generate('character', ['characterNameKeyUrl' => $character2->getCharacterNameKeyUrl()]),
                "Contatto privato",
                "Il contatto privato di {$character2->getCharacterName()} non funziona più",
                $character2->getUser()->getId()
            );
        }

        $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');
        array_walk(
            $users,
            function (User $user) use ($character1, $character2, $image) {

                $this->sendNotification(
                    $image,
                    $this->generator->generate('character', [
                        'characterNameKeyUrl' => $character1->getCharacterNameKeyUrl()
                    ]),
                    "Contatti privati",
                    "La narrazione ha disconnesso {$character1->getCharacterName()} e {$character2->getCharacterName()}",
                    $user->getId()
                );
            }
        );
    }

    public function connectionSend(Character $character1, Character $character2, bool $isForced)
    {
        $image = "//ui-avatars.com/api/?name=".$character2->getCharacterName()."&size=50&rounded=true";
        if (!empty($character2->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/' . $character2->getPhoto());
        }

        if ($character1->getUser() != null) {
            $this->sendNotification(
                $image,
                $this->generator->generate('character', ['characterNameKeyUrl' => $character2->getCharacterNameKeyUrl()]),
                "Contatto privato",
                "{$character2->getCharacterName()} vuole scambiare il suo contatto privato con te",
                $character1->getUser()->getId()
            );
        } else {
            $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');
            array_walk(
                $users,
                function (User $user) use ($character1, $character2, $image) {

                    $this->sendNotification(
                        $image,
                        $this->generator->generate('character', [
                            'characterNameKeyUrl' => $character1->getCharacterNameKeyUrl()
                        ]),
                        "Contatti privati",
                        "{$character2->getCharacterName()} vuole il contatto privato di {$character1->getCharacterName()}",
                        $user->getId()
                    );
                }
            );
        }
    }
    
    public function downtimeResolved(Character $character, Downtime $downtime)
    {
        $image = "//ui-avatars.com/api/?name=".$character->getCharacterName()."&size=50&rounded=true";
        if (!empty($character->getPhoto())) {
            $image = $this->packages->getUrl('/uploads/character_photo/' . $character->getPhoto());
        }
        
        if ($character->getUser() != null) {
            $this->sendNotification(
                $image,
                $this->generator->generate('downtime-index'),
                "Risoluzione DT",
                "risolto il dt {$downtime->getTitle()}",
                $character->getUser()->getId()
                );
        } else {
            $users = $this->entityManager->getRepository(User::class)->findByRole('ROLE_STORY_TELLER');
            array_walk(
                $users,
                function (User $user) use ($character, $image, $downtime) {
                    
                    $this->sendNotification(
                        $image,
                        $this->generator->generate('downtime-index'),
                        "Risoluzione DT",
                        "il dt  {$downtime->getTitle()} di {$character->getCharacterName()} ha avuto una risoluzione",
                        $user->getId()
                        );
                }
                );
        }
    }

    private function sendNotification($image, $link, $title, $message, $recipient)
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

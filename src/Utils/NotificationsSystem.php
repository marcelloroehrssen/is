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
<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 16/05/2018
 * Time: 02:34.
 */

namespace App\Utils;

use App\Entity\Character;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;

class MessageSystem
{
    /**
     * @var MessageRepository
     */
    private $messageRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var NotificationsSystem
     */
    private $notificationsSystem;

    /**
     * MessageSystem constructor.
     *
     * @param EntityManagerInterface $em
     * @param NotificationsSystem $notificationsSystem
     * @param MessageRepository $messageRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        NotificationsSystem $notificationsSystem,
        MessageRepository $messageRepository,
        UserRepository $userRepository)
    {
        $this->em = $em;
        $this->notificationsSystem = $notificationsSystem;
        $this->messageRepository = $messageRepository;
        $this->userRepository = $userRepository;
    }

    public function sendMessage(Character $sender, Character $recipient, $text, $isLetter = false, $isPrivate = false, $isAnonymous = false, $isEncoded = false)
    {
        $message = new Message();
        /*
         * set User2 as min user1#id or user2#id
         */
        $message->setUser1(
            (function (Character $user1, Character $user2) {
                return $user1->getId() < $user2->getId() ? $user1 : $user2;
            })($sender, $recipient)
        );
        /*
         * set User2 as max user1#id or user2#id
         */
        $message->setUser2(
            (function (Character $user1, Character $user2) {
                return $user1->getId() < $user2->getId() ? $user2 : $user1;
            })($sender, $recipient)
        );

        $message->setSender($sender);

        $message->setText($text);
        $message->setIsLetter($isLetter);
        $message->setIsPrivate($isPrivate);
        $message->setIsAnonymous($isAnonymous);
        $message->setIsEncoded($isEncoded);

        $this->notificationsSystem->messageSent($sender, $recipient, $isLetter);

        $this->em->persist($message);
        $this->em->flush();
    }

    public function getChat(Character $user1, Character $user2, $isLetter = false, $forAdmin = false)
    {
        $messages = $this->messageRepository->getChat(
            (function (Character $user1, Character $user2) {
                return $user1->getId() < $user2->getId() ? $user1 : $user2;
            })($user1, $user2),
            (function (Character $user1, Character $user2) {
                return $user1->getId() < $user2->getId() ? $user2 : $user1;
            })($user1, $user2),
            $isLetter,
            $forAdmin
        );

        return $messages;
    }

    public function getCharacterChatWith(Character $character, $isLetter = false, $forAdmin = false)
    {
        return $this->messageRepository->getCharacterWithChat($character, $isLetter, $forAdmin);
    }

    public function getAllChat(Character $character, $isLetter = false, $forAdmin = false)
    {
        $chat = $this->messageRepository->getCharacterWithChat($character, $isLetter, $forAdmin);

        $chatSeen = [];
        array_walk(
            $chat,
            function (Message $message) use ($character, &$chatSeen) {
                $user1 = $message->getUser1();
                $user2 = $message->getUser2();

                if ($user1->getId() == $character->getId()) {
                    $chatSeen[$user2->getId()] = $user2;
                } else {
                    $chatSeen[$user1->getId()] = $user1;
                }
            }
        );

        return $chatSeen;
    }

    public function getLastInteraction(User $admin, Character $character, $isLetter = false)
    {
        $lastMessageSeen = $admin->getLastMessageSeenDate();

        $chats = $this->getAllChat($character, $isLetter);
        $data = [];
        foreach ($chats as $character1) {
            $messages = $this->getChat($character, $character1, $isLetter);
            $lastMessageDate = array_shift($messages)->getCreatedAt();
            $data[] = [
                'recipient' => $character1,
                'lastMessage' => [
                    'date' => $lastMessageDate,
                    'seen' => $lastMessageDate->getTimestamp() < $lastMessageSeen->getTimestamp(),
                ],
            ];
        }

        return $data;
    }

    public function updateLastMessageSeen(User $user)
    {
        $user->setLastMessageSeenDate(new \DateTime());
        $this->em->flush();
    }
}

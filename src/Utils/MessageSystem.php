<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 16/05/2018
 * Time: 02:34
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
     * @param MessageRepository $messageRepository
     */
    public function __construct(EntityManagerInterface $em, NotificationsSystem $notificationsSystem)
    {
        $this->messageRepository = $em->getRepository(Message::class);
        $this->userRepository = $em->getRepository(User::class);
        $this->em = $em;
        $this->notificationsSystem = $notificationsSystem;
    }

    public function sendMessage(Character $sender, Character $recipient, $text, $isLetter = false, $isPrivate = false, $isAnonymous = false, $isEncoded = false)
    {
        $message = new Message();
        /**
         * set User2 as min user1#id or user2#id
         */
        $message->setUser1(
            (function (Character $user1, Character $user2) {
                return $user1->getId() < $user2->getId() ? $user1 : $user2;
            })($sender, $recipient)
        );
        /**
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

        $this->notificationsSystem->messageSent($sender, $recipient);

        $this->em->persist($message);
        $this->em->flush();
    }

    public function getChat(Character $user1, Character $user2, $onlyPrivate = false)
    {
        $messages = $this->messageRepository->getChat(
            (function(Character $user1, Character $user2) {
                return $user1->getId() < $user2->getId() ? $user1 : $user2;
            })($user1, $user2),
            (function(Character $user1, Character $user2) {
                return $user1->getId() < $user2->getId() ? $user2 : $user1;
            })($user1, $user2),
            $onlyPrivate
        );
        return $messages;
    }

    public function getAllChat(Character $character)
    {
        $chat = $this->messageRepository->getCharacterWithChat($character);

        $chatSeen = [];
        array_walk(
            $chat,
            function(Message $message) use ($character, &$chatSeen) {
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
    
    public function getAllChatForAdmin(User $user, int $limit = 15, int $currentPage = 1)
    {
        $user->setLastMessageSeenDate(new \DateTime());
        $this->em->flush();
        
        return $this->messageRepository->getAllChatForAdminQuery($limit, $currentPage);
    }
}
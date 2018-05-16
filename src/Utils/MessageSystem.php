<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 16/05/2018
 * Time: 02:34
 */

namespace App\Utils;


use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;

class MessageSystem
{
    /**
     * @var MessageRepository
     */
    private $messageRepository;

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
    public function __construct(MessageRepository $messageRepository, EntityManagerInterface $em, NotificationsSystem $notificationsSystem)
    {
        $this->messageRepository = $messageRepository;
        $this->em = $em;
        $this->notificationsSystem = $notificationsSystem;
    }

    public function sendMessage(User $sender, User $recipient, $text, $isPrivate = false, $isAnonymous = false, $isEncoded = false)
    {
        $message = new Message();
        /**
         * set User2 as min user1#id or user2#id
         */
        $message->setUser1(
            (function (User $user1, User $user2) {
                return $user1->getId() < $user2->getId() ? $user1 : $user2;
            })($sender, $recipient)
        );
        /**
         * set User2 as max user1#id or user2#id
         */
        $message->setUser2(
            (function (User $user1, User $user2) {
                return $user1->getId() < $user2->getId() ? $user2 : $user1;
            })($sender, $recipient)
        );

        $message->setText($text);
        $message->setIsPrivate($isPrivate);
        $message->setIsAnonymous($isAnonymous);
        $message->setIsEncoded($isEncoded);

        $this->em->persist($message);
        $this->em->flush();
    }

    public function getChat(User $user1, User $user2, $onlyPrivate = false)
    {
        $messages = $this->messageRepository->getChat(
            (function(User $user1, $user2) {
                return $user1->getId() < $user2->getId() ? $user1 : $user2;
            })($user1, $user2),
            (function(User $user1, $user2) {
                return $user1->getId() < $user2->getId() ? $user2 : $user1;
            })($user1, $user2),
            $onlyPrivate
        );
        return $messages;
    }
}
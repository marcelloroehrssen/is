<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 16/05/2018
 * Time: 02:31.
 */

namespace App\Repository;

use App\Entity\Character;
use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function getChat(Character $user1, Character $user2, $isLetter = false, $forAdmin = false)
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m')
            ->join('m.user1', 'u1')
            ->join('m.user2', 'u2')
            ->where('m.user1 = :user1')
            ->andWhere('m.user2 = :user2')
            ->andWhere('m.isLetter = :isLetter')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->setParameter('isLetter', $isLetter);

        if ($isLetter && !$forAdmin) {
            $qb->andWhere('m.createdAt < :oneDayBefore')
                ->setParameter('oneDayBefore', new \DateTime('-1 days'));
        }

        return $qb->getQuery()->getResult();
    }

    public function getCharacterWithChat(Character $sender, $isLetter = false, $forAdmin = false)
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m')
            ->join('m.user1', 'u1')
            ->join('m.user2', 'u2')
            ->where('(m.user1 = :sender or m.user2 = :sender)')
            ->andWhere('m.isLetter = :isLetter')
            ->orderBy('m.createdAt', 'desc')
            ->setParameter('sender', $sender)
            ->setParameter('isLetter', $isLetter);

        if ($isLetter && !$forAdmin) {
            $qb->andWhere('m.createdAt < :oneDayBefore')
                ->setParameter('oneDayBefore', new \DateTime('-1 days'));
        }

        return $qb->getQuery()->getResult();
    }

    public function getAllChatForAdminQuery()
    {
        return $this->createQueryBuilder('m')
            ->where('m.isLetter = :isLetter')
            ->orderBy('m.createdAt', 'desc')
            ->setParameter('isLetter', false)
            ->getQuery()
            ->getResult();
    }

    public function getAllLettersForAdminQuery()
    {
        return $this->createQueryBuilder('m')
            ->where('m.isLetter = :isLetter')
            ->orderBy('m.createdAt', 'desc')
            ->setParameter('isLetter', true)
            ->getQuery()
            ->getResult();
    }

    public function getDeliveringLetters(Character $character)
    {
        return $this->createQueryBuilder('m')
            ->select('m')
            ->join('m.user1', 'u1')
            ->join('m.user2', 'u2')
            ->where('m.isLetter = :isLetter')
            ->andWhere('m.sender = :sender')
            ->andWhere('m.createdAt > :oneDayBefore')
            ->orderBy('m.createdAt', 'desc')
            ->setParameter('sender', $character)
            ->setParameter('isLetter', true)
            ->setParameter('oneDayBefore', new \DateTime('-1 days'))
            ->getQuery()
            ->getResult();
    }
}

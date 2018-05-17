<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 16/05/2018
 * Time: 02:31
 */

namespace App\Repository;


use App\Entity\Character;
use Doctrine\ORM\EntityRepository;

class MessageRepository extends EntityRepository
{
    public function getChat(Character $user1, Character $user2, $onlyPrivate = false)
    {
        return $this->createQueryBuilder('m')
            ->where('m.user1 = :user1')
            ->andWhere('m.user2 = :user2')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->getQuery()
            ->getResult();
    }

    public function getCharacterWithChat(Character $sender)
    {
        return $this->createQueryBuilder('m')
            ->select('m')
            ->join('m.user1', 'u1')
            ->join('m.user2', 'u2')
            ->where('m.sender = :sender')
            ->setParameter('sender', $sender)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
}
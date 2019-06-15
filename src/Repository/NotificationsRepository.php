<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 14/05/2018
 * Time: 17:48.
 */

namespace App\Repository;

use App\Entity\Notifications;
use Doctrine\ORM\EntityRepository;

class NotificationsRepository extends EntityRepository
{
    public function getNotifications($userId)
    {
        $qb = $this->createQueryBuilder('n');
        $unreadNotifications = $qb
            ->Where($qb->expr()->orX(
                $qb->expr()->isNull('n.user'),
                $qb->expr()->eq('n.user', $userId)
            ))
            ->andWhere('n.read = 0')
            ->orderBy('n.createdAt', 'desc')
            ->getQuery()
            ->getResult();

        $readNotifications = $qb
            ->Where($qb->expr()->orX(
                $qb->expr()->isNull('n.user'),
                $qb->expr()->eq('n.user', $userId)
            ))
            ->andWhere('n.read = 1')
            ->orderBy('n.createdAt', 'desc')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return array_merge($unreadNotifications, $readNotifications);
    }

    public function readAll($userId)
    {
        $notifications = $this->createQueryBuilder('n')
            ->where('n.user = :user')
            ->setParameter('user', $userId)
            ->getQuery()
            ->getResult();

        array_walk(
            $notifications,
            function (Notifications $notification) {
                $notification->setRead(true);
            }
        );
        $this->getEntityManager()->flush();
    }
}

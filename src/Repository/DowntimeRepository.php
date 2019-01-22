<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Entity\Downtime;
use App\Entity\Character;

class DowntimeRepository extends EntityRepository
{
    public function getPaginatedDowntime(Character $character, $currentPage = 1, $limit = 5)
    {
        $query = $this->createQueryBuilder('d')
            ->where('d.character = :character')
            ->orderBy('d.createdAt', 'desc')
            ->setParameter('character', $character)
            ->getQuery();

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $paginator->getQuery()
        ->setFirstResult($limit * ($currentPage - 1)) // Offset
        ->setMaxResults($limit); // Limit

        return $paginator;
    }

    public function getAdminPaginatedDowntime($currentPage = 1, $limit = 5, $status = null)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->orderBy('d.createdAt', 'desc');

        if (Downtime::STATUS_RESOLVED === $status) {
            $queryBuilder->where('d.resolution is not null');
        } else {
            $queryBuilder->where('d.resolution is null');
        }

        $query = $queryBuilder->getQuery();

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $paginator->getQuery()
        ->setFirstResult($limit * ($currentPage - 1)) // Offset
        ->setMaxResults($limit); // Limit

        return $paginator;
    }

    public function getDistinctDate(Character $character)
    {
        $result = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('d.createdAt')
            ->from(Downtime::class, 'd')
            ->where('d.character = :character')
            ->distinct()
            ->setParameter('character', $character)
            ->getQuery()
            ->getScalarResult();

        return array_map(function ($date) {
            return new \DateTime($date['createdAt']);
        }, $result);
    }

    public function getCountForDate(Character $character, string $type, \DateTime $date)
    {
        return $this->createQueryBuilder('d')
            ->select('count(d)')
            ->where('d.createdAt > :date')
            ->andWhere('d.type = :type')
            ->andWhere('d.character = :character')
            ->setParameter('type', $type)
            ->setParameter('date', new \DateTime($date->format('Y-m-1 00:00:00')))
            ->setParameter('character', $character)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

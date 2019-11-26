<?php

namespace App\Repository;

use App\Entity\Elysium;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ElysiumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Elysium::class);
    }

    public function getAll()
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.date', 'desc')
            ->getQuery()
            ->getResult();
    }
}

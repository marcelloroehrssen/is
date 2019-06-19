<?php

namespace App\Repository;

use App\Entity\Elysium;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ElysiumRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
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

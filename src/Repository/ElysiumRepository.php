<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class ElysiumRepository extends EntityRepository
{
    public function getAll()
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.date', 'desc')
            ->getQuery()
            ->getResult();
    }
}

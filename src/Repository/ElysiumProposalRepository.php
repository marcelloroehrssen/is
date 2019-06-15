<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class ElysiumProposalRepository extends EntityRepository
{
    public function getUnassigned()
    {
        return $this->createQueryBuilder('e')
            ->where('e.elysium is null')
            ->getQuery()
            ->getResult();
    }
}

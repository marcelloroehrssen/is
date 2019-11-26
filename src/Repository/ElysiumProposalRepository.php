<?php

namespace App\Repository;

use App\Entity\ElysiumProposal;
use DateTime;
use App\Entity\Character;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ElysiumProposalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ElysiumProposal::class);
    }

    public function getUnassigned()
    {
        return $this->createQueryBuilder('e')
            ->where('e.elysium is null')
            ->getQuery()
            ->getResult();
    }

    public function getProposalByCharacter(Character $character)
    {
        return $this->createQueryBuilder('p')
            ->join('p.validity', 'e')
            ->where('e.date > :now')
            ->setParameter('now', new DateTime())
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    public function getProposalWithNoValidity()
    {
        return $this->createQueryBuilder('p')
            ->join('p.validity', 'e')
            ->where('e is null')
            ->distinct()
            ->getQuery()
            ->getResult();
    }
}

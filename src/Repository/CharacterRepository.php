<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 13/05/2018
 * Time: 04:53.
 */

namespace App\Repository;

use App\Entity\Character;
use App\Entity\Rank;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CharacterRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Character::class);
    }

    public function findByKeyUrl($keyUrl)
    {
        return $this->createQueryBuilder('c')
            ->where('c.characterNameKeyUrl = :keyUrl')
            ->setParameter('keyUrl', $keyUrl)
            ->getQuery()
            ->getResult();
    }

    public function getAllCharacterOrderedByAssociation()
    {
        return $this->createQueryBuilder('c')->orderBy('c.user', 'asc')->getQuery()->getResult();
    }

    public function getAllByType(string $type)
    {
        return $this->createQueryBuilder('png')
        ->where('png.type = :type')
        ->setParameter('type', $type)
        ->getQuery()
        ->getResult();
    }

    public function getAllPng()
    {
        return $this->createQueryBuilder('png')
            ->where('png.type = :type')
            ->setParameter('type', 'PNG')
            ->getQuery()
            ->getResult();
    }

    public function getAllPg(string $query, Character $character = null)
    {
        $qb = $this->createQueryBuilder('pg');
        if (null !== $character) {
            $qb->andWhere('pg != :character')
                ->setParameter('character', $character);
        }
        $a = $qb->andWhere($qb->expr()->like('pg.characterName', ':name'))
            ->setParameter('name', '%'.$query.'%');

        return $a->getQuery()
            ->getResult();
    }

    public function getAllOthersQB(Character $character)
    {
        return $this->createQueryBuilder('u')
            ->where('u != :character')
            ->setParameter('character', $character);
    }

    public function getAll($query, $forCensor = false)
    {
        $query = $this->createQueryBuilder('pg')
            ->where('pg.characterName like :query')
            ->setParameter('query', "%$query%")
        ;

        if (true !== $forCensor) {
            $query->andWhere('pg.rank != :rank')
                ->setParameter('rank', $this->getEntityManager()->getReference(Rank::class, 4));
        }

        return $query->getQuery()->getResult();
    }
}

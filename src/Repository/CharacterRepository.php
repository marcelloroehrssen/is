<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 13/05/2018
 * Time: 04:53
 */

namespace App\Repository;


use App\Entity\Character;
use Doctrine\ORM\EntityRepository;

class CharacterRepository extends EntityRepository
{
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

    public function getAllPng()
    {
        return $this->createQueryBuilder('png')
            ->where('png.type = :type')
            ->setParameter('type', 'PNG')
            ->getQuery()
            ->getResult();
    }

    public function getAllPg(Character $character)
    {
        return $this->createQueryBuilder('pg')
            ->where('pg.user != :character')
            ->setParameter('character', $character)
            ->getQuery()
            ->getResult();
    }
}
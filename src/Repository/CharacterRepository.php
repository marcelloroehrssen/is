<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 13/05/2018
 * Time: 04:53
 */

namespace App\Repository;


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
}
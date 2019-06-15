<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 19/05/2018
 * Time: 20:36.
 */

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class BoardRepository extends EntityRepository
{
    public function getAll()
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.createdAt', 'desc')
            ->getQuery()
            ->getResult();
    }
}

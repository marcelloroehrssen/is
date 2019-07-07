<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 19/05/2018
 * Time: 20:36.
 */

namespace App\Repository;

use App\Entity\Board;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class BoardRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Board::class);
    }

    public function getAll()
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.createdAt', 'desc')
            ->getQuery()
            ->getResult();
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 10/11/2018
 * Time: 13:34
 */

namespace App\Repository;


use App\Entity\Character;
use Doctrine\ORM\EntityRepository;

class EquipmentRepository extends EntityRepository
{
    public function getAllByCharacter($character = null, $limit = null)
    {
        $qb = $this->createQueryBuilder('e')
            ->orderBy('e.obtainedAt', 'desc')
        ;

        if ($character !== null) {
            $qb->where('e.owner = :character')
                ->setParameter('character', $character);
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()
            ->getResult();
    }

    public function getEquipmentRequest(Character $character = null)
    {
        $qb = $this->createQueryBuilder('e')
            ->orderBy('e.obtainedAt', 'desc')
        ;

        if ($character !== null) {
            $qb->where('e.receiver = :character')
                ->setParameter('character', $character);
        } else {
            $qb->where('e.receiver is not null');
        }

        return $qb->getQuery()
            ->getResult();
    }

    public function getByOwnerNameAndDescription(Character $owner, string $name, string $description)
    {
        return $this->createQueryBuilder('e')
            ->where('e.name = :name')
            ->andWhere('e.description = :description')
            ->andWhere('e.owner = :owner')
            ->andWhere('e.receiver is null')
            ->setParameter('name', $name)
            ->setParameter('description', $description)
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
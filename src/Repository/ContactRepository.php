<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 23/05/2018
 * Time: 03:13.
 */

namespace App\Repository;

use App\Entity\Character;
use App\Entity\Contact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    public function getContactInfo(Character $character1, Character $character2)
    {
        return $this->createQueryBuilder('c')
            ->where('c.character1 = :character1')
            ->andWhere('c.character2 = :character2')
            ->setParameter('character1', $character1)
            ->setParameter('character2', $character2)
            ->getQuery()
            ->getResult();
    }

    public function getAllContactRequest(Character $character)
    {
        return $this->createQueryBuilder('c')
            ->where('(c.character1 = :character OR c.character2 = :character)')
            ->setParameter('character', $character)
            ->getQuery()
            ->getResult();
    }
}

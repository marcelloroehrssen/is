<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 14/05/2018
 * Time: 01:50.
 */

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function findByRole($role)
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%"'.$role.'"%')
            ->getQuery()
            ->getResult();
    }

    public function findByEmail(string $mail)
    {
        return $this->createQueryBuilder('u')
            ->where('u.email = :mail')
            ->setParameter('mail', $mail)
            ->getQuery()
            ->getSingleResult();
    }
}

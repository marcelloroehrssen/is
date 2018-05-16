<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 16/05/2018
 * Time: 02:31
 */

namespace App\Repository;


use Doctrine\ORM\EntityRepository;

class MessageRepository extends EntityRepository
{
    public function getChat($user1, $user2, $onlyPrivate = false)
    {

    }
}
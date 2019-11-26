<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 13/05/2018
 * Time: 13:48.
 */

namespace App\Repository;

use App\Entity\CharacterPhoto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class CharacterPhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CharacterPhoto::class);
    }

    public function getPhotos($character)
    {
        return $this->createQueryBuilder('cp')
            ->where('cp.character = :character')
            ->setParameter('character', $character)
            ->orderBy('cp.uploadDate', 'desc')
            ->setMaxResults(2)
            ->getQuery()
            ->getResult();
    }

    public function cleanAlbum($character)
    {
        $result = $this->createQueryBuilder('cp')
           ->where('cp.character = :character')
           ->andWhere('cp not in(:goodPhotos)')
           ->setParameter('character', $character)
           ->setParameter('goodPhotos', $this->getPhotos($character))
           ->getQuery()
           ->getResult();

        $em = $this->getEntityManager();
        foreach ($result as $photo) {
            $em->remove($photo);
        }
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 26/05/2018
 * Time: 00:09
 */

namespace App\Utils;


use App\Entity\Character;
use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;

class ConnectionSystem
{
    public function __construct(EntityManagerInterface $em, NotificationsSystem $notificationsSystem)
    {
        $this->contactRepository = $em->getRepository(Contact::class);
        $this->em = $em;
        $this->notificationsSystem = $notificationsSystem;
    }

    public function areConnected(Character $character1, Character $character2)
    {
        list($minChar, $maxChar) = $this->getOrderedContact($character1, $character2);
        $contact = $this->contactRepository->getContactInfo($minChar, $maxChar);
        return $contact->isCharacter1Confirmed() && $contact->isCharacter2Confirmed();
    }

    public function getAllContactRequest(Character $character)
    {
        return $this->contactRepository->getAllUnConfirmedRequest($character);
    }

    /**
     * Il character1 è SEMPRE il richiedente (tranne nel caso di forzatura)
     *
     * @param Character $character1
     * @param Character $character2
     * @param bool $isForced
     */
    public function connect(Character $character1, Character $character2, bool $isForced)
    {
        list($minChar, $maxChar) = $this->getOrderedContact($character1, $character2);

        $contact = new Contact();
        $contact->setCharacter1($minChar);
        $contact->setCharacter2($maxChar);

        if (!$isForced) {
            if ($minChar == $character1) {
                $contact->setCharacter1RequestDate(new \DateTime());
                $contact->setCharacter1Confirmed(true);
            } else {
                $contact->setCharacter2RequestDate(new \DateTime());
                $contact->setCharacter2Confirmed(true);
            }
        } else {
            $contact->setCharacter1RequestDate(new \DateTime());
            $contact->setCharacter1Confirmed(true);
            $contact->setCharacter2RequestDate(new \DateTime());
            $contact->setCharacter2Confirmed(true);
        }
        $this->em->persist($contact);
        $this->em->flush();
    }

    public function confirm(int $connectionId, Character $character)
    {
        /** @var Contact $connection */
        $connection = $this->em->getRepository(Contact::class)->find($connectionId);
        if ($character == $connection->getCharacter1()) {
            $connection->setCharacter1Confirmed(true);
        } else {
            $connection->setCharacter2Confirmed(true);
        }
        $this->em->flush();
    }

    private function getOrderedContact(Character $character1, Character $character2)
    {
        return [
            'min' => (function(Character $character1, Character $character2) {
                return $character1->getId() < $character2->getId() ? $character1 : $character2;
            })($character1, $character2),
            'max' => (function(Character $character1, Character $character2){
                return $character1->getId() < $character2->getId() ? $character2 : $character1;
            })($character2, $character2)
        ];
    }
}
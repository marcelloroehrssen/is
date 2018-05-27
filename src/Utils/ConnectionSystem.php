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

    /**
     * @param Character $character1
     * @param Character $character2
     * @return bool
     * @throws \Exception
     */
    public function areConnected(Character $character1, Character $character2)
    {
        list($minChar, $maxChar) = $this->getOrderedContact($character1, $character2);
        $contact = $this->contactRepository->getContactInfo($minChar, $maxChar);

        if (count($contact) == 0) {
            return false;
        } else if (count($contact) > 1) {
            throw new \Exception(sprintf("Too many contact request %s =>  %s", $minChar->getId(), $maxChar->getId()));
        }

        $contact = current($contact);

        return $contact->isCharacter1Confirmed() && $contact->isCharacter2Confirmed();
    }

    /**
    *   @param $character1 *DEVE* essere il current user
    */
    public function getConnectionStatus(Character $character1, Character $character2)
    {
        list($minChar, $maxChar) = $this->getOrderedContact($character1, $character2);
        $contact = $this->contactRepository->getContactInfo($minChar, $maxChar);

        if (!empty($contact)) {
            $contact = current($contact);

            $contactInfo = new ContactInfo();

            $contactInfo->connectionId = $contact->getId();
            if ($contact->getCharacter1()->equals($character1)) {
                $contactInfo->currentUserIsRequesting = $contact->isCharacter1Confirmed();
                $contactInfo->currentUserIsRequested = $contact->isCharacter2Confirmed();
            } else {
                $contactInfo->currentUserIsRequesting = $contact->isCharacter2Confirmed();
                $contactInfo->currentUserIsRequested = $contact->isCharacter1Confirmed();
            }

            return $contactInfo;
        }
        return null;
    }

    public function getAllContactRequest(Character $character)
    {
        return $this->contactRepository->getAllContactRequest($character);
    }

    /**
     * Il character1 è SEMPRE il richiedente (tranne nel caso di forzatura)
     *
     * @param Character $character1
     * @param Character $character2
     * @param bool $isForced
     */
    public function connect(Character $character1, Character $character2, bool $isForced = false)
    {
        list($minChar, $maxChar) = $this->getOrderedContact($character1, $character2);

        $contact = new Contact();
        $contact->setCharacter1($minChar);
        $contact->setCharacter2($maxChar);

        if (!$isForced) {
            if ($minChar->equals($character1)) {
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
            $contact->setIsForced($isForced);
        }
        $this->em->persist($contact);
        $this->em->flush();
    }

    public function disconnect($connectionId)
    {
        $connection = $this->em->getRepository(Contact::class)->find($connectionId);
        $this->em->remove($connection);
        $this->em->flush();
    }

    public function confirm(int $connectionId, Character $character, $isForced = false)
    {
        /** @var Contact $connection */
        $connection = $this->em->getRepository(Contact::class)->find($connectionId);
        if ($character->equals($connection->getCharacter1())) {
            $connection->setCharacter1Confirmed(true);
        } else {
            $connection->setCharacter2Confirmed(true);
        }

        if ($isForced) {
            $connection->setIsForced($isForced);
        }
        $this->em->flush();
    }

    private function getOrderedContact(Character $character1, Character $character2)
    {
        return [
            (function(Character $character1, Character $character2) {
                return $character1->getId() < $character2->getId() ? $character1 : $character2;
            })($character1, $character2),
            (function(Character $character1, Character $character2){
                return $character1->getId() < $character2->getId() ? $character2 : $character1;
            })($character2, $character2)
        ];
    }
}

class ContactInfo {

    public $connectionId;

    public $currentUserIsRequesting;

    public $currentUserIsRequested;
}

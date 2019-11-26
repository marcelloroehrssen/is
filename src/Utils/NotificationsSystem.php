<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 14/05/2018
 * Time: 19:17.
 */

namespace App\Utils;

use App\Entity\Character;
use App\Entity\Equipment;
use App\Entity\User;
use App\Subscribers\Events\AssociateCharacterEvent;
use App\Subscribers\Events\ConnectionDoneEvent;
use App\Subscribers\Events\ConnectionRemovedEvent;
use App\Subscribers\Events\ConnectionSendEvent;
use App\Subscribers\Events\DeletedCharacterEvent;
use App\Subscribers\Events\DowntimeResolvedEvent;
use App\Subscribers\Events\EquipmentAssigned;
use App\Subscribers\Events\EquipmentRequestAccepted;
use App\Subscribers\Events\EquipmentRequestDenied;
use App\Subscribers\Events\EquipmentRequestReceived;
use App\Subscribers\Events\EventAssigned;
use App\Subscribers\Events\MessageSentEvent;
use App\Subscribers\Events\NewEventCreated;
use App\Subscribers\Events\NewEventProposalEvent;
use App\Subscribers\Events\PublishNewCharacterEvent;
use App\Subscribers\Events\PublishNewCharacterSheetEvent;
use App\Subscribers\Events\RoleUpdateEvent;
use App\Entity\Downtime;
use App\Entity\Elysium;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class NotificationsSystem
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * NotificationsSystem constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
    }

    public function publishNewCharacter($character)
    {
        $this->eventDispatcher->dispatch(
            new PublishNewCharacterEvent($character, __FUNCTION__)
        );
    }

    public function deleteCharacter(User $actor, Character $character)
    {
        $this->eventDispatcher->dispatch(
            new DeletedCharacterEvent($character, __FUNCTION__)
        );
    }

    public function publishNewCharacterSheet(User $actor, Character $character)
    {
        $this->eventDispatcher->dispatch(
            new PublishNewCharacterSheetEvent($character, __FUNCTION__)
        );
    }

    public function associateCharacter(User $actor, $character)
    {
        $this->eventDispatcher->dispat8ch(
            new AssociateCharacterEvent($character, __FUNCTION__)
        );
    }

    public function messageSent(Character $characterActor, Character $recipient, bool $isLetter)
    {
        $this->eventDispatcher->dispatch(
            new MessageSentEvent($characterActor, $recipient, $isLetter, __FUNCTION__)
        );
    }

    public function roleUpdated($character, $who, $message)
    {
        $this->eventDispatcher->dispatch(
            new RoleUpdateEvent($character, $who, $message, __FUNCTION__)
        );
    }

    public function connectionDone(Character $character1, Character $character2, bool $isForced)
    {
        $this->eventDispatcher->dispatch(
            new ConnectionDoneEvent($character1, $character2, $isForced, __FUNCTION__)
        );
    }

    public function connectionRemoved(Character $character1, Character $character2)
    {
        $this->eventDispatcher->dispatch(
            new ConnectionRemovedEvent($character1, $character2, __FUNCTION__)
        );
    }

    public function connectionSend(Character $character1, Character $character2, bool $isForced)
    {
        $this->eventDispatcher->dispatch(
            new ConnectionRemovedEvent($character1, $character2, __FUNCTION__)
        );
    }

    public function downtimeResolved(Character $character, Downtime $downtime)
    {
        $this->eventDispatcher->dispatch(
            new DowntimeResolvedEvent($character, $downtime, __FUNCTION__)
        );
    }

    public function newEventCreated(Elysium $event)
    {
        $this->eventDispatcher->dispatch(
            new NewEventCreated($event, __FUNCTION__)
        );
    }

    public function newEventProposalCreated(Character $proposer)
    {
        $this->eventDispatcher->dispatch(
            new NewEventProposalEvent($proposer, __FUNCTION__)
        );
    }

    public function eventAssigned(Elysium $event)
    {
        $this->eventDispatcher->dispatch(
            new EventAssigned($event, __FUNCTION__)
        );
    }

    public function equipmentReceived(Equipment $equipment)
    {
        $this->eventDispatcher->dispatch(
            new EquipmentAssigned($equipment, __FUNCTION__)
        );
    }

    public function equipmentRequestReceived(Equipment $equipment)
    {
        $this->eventDispatcher->dispatch(
            new EquipmentRequestReceived($equipment, __FUNCTION__)
        );
    }

    public function equipmentRequestAccepted(Equipment $equipment, Character $sender)
    {
        $this->eventDispatcher->dispatch(
            new EquipmentRequestAccepted($equipment, $sender, __FUNCTION__)
        );
    }

    public function equipmentRequestDenied(Equipment $equipment, Character $sender)
    {
        $this->eventDispatcher->dispatch(
            new EquipmentRequestDenied($equipment, $sender, __FUNCTION__)
        );
    }
}

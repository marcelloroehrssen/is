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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Entity\Downtime;
use App\Entity\Elysium;

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
            PublishNewCharacterEvent::NAME,
            new PublishNewCharacterEvent($character, __FUNCTION__)
        );
    }

    public function deleteCharacter(User $actor, Character $character)
    {
        $this->eventDispatcher->dispatch(
            DeletedCharacterEvent::NAME,
            new DeletedCharacterEvent($character, __FUNCTION__)
        );
    }

    public function publishNewCharacterSheet(User $actor, Character $character)
    {
        $this->eventDispatcher->dispatch(
            PublishNewCharacterSheetEvent::NAME,
            new PublishNewCharacterSheetEvent($character, __FUNCTION__)
        );
    }

    public function associateCharacter(User $actor, $character)
    {
        $this->eventDispatcher->dispatch(
            AssociateCharacterEvent::NAME,
            new AssociateCharacterEvent($character, __FUNCTION__)
        );
    }

    public function messageSent(Character $characterActor, Character $recipient, bool $isLetter)
    {
        $this->eventDispatcher->dispatch(
            MessageSentEvent::NAME,
            new MessageSentEvent($characterActor, $recipient, $isLetter, __FUNCTION__)
        );
    }

    public function roleUpdated($character, $who, $message)
    {
        $this->eventDispatcher->dispatch(
            RoleUpdateEvent::NAME,
            new RoleUpdateEvent($character, $who, $message, __FUNCTION__)
        );
    }

    public function connectionDone(Character $character1, Character $character2, bool $isForced)
    {
        $this->eventDispatcher->dispatch(
            ConnectionDoneEvent::NAME,
            new ConnectionDoneEvent($character1, $character2, $isForced, __FUNCTION__)
        );
    }

    public function connectionRemoved(Character $character1, Character $character2)
    {
        $this->eventDispatcher->dispatch(
            ConnectionRemovedEvent::NAME,
            new ConnectionRemovedEvent($character1, $character2, __FUNCTION__)
        );
    }

    public function connectionSend(Character $character1, Character $character2, bool $isForced)
    {
        $this->eventDispatcher->dispatch(
            ConnectionSendEvent::NAME,
            new ConnectionRemovedEvent($character1, $character2, __FUNCTION__)
        );
    }

    public function downtimeResolved(Character $character, Downtime $downtime)
    {
        $this->eventDispatcher->dispatch(
            DowntimeResolvedEvent::NAME,
            new DowntimeResolvedEvent($character, $downtime, __FUNCTION__)
        );
    }

    public function newEventCreated(Elysium $event)
    {
        $this->eventDispatcher->dispatch(
            NewEventCreated::NAME,
            new NewEventCreated($event, __FUNCTION__)
        );
    }

    public function newEventProposalCreated(Character $proposer = null)
    {
        $this->eventDispatcher->dispatch(
            NewEventProposalEvent::NAME,
            new NewEventProposalEvent($proposer, __FUNCTION__)
        );
    }

    public function eventAssigned(Elysium $event)
    {
        $this->eventDispatcher->dispatch(
            EventAssigned::NAME,
            new EventAssigned($event, __FUNCTION__)
        );
    }

    public function equipmentReceived(Equipment $equipment)
    {
        $this->eventDispatcher->dispatch(
            EquipmentAssigned::NAME,
            new EquipmentAssigned($equipment, __FUNCTION__)
        );
    }

    public function equipmentRequestReceived(Equipment $equipment)
    {
        $this->eventDispatcher->dispatch(
            EquipmentRequestReceived::NAME,
            new EquipmentRequestReceived($equipment, __FUNCTION__)
        );
    }

    public function equipmentRequestAccepted(Equipment $equipment, Character $sender)
    {
        $this->eventDispatcher->dispatch(
            EquipmentRequestAccepted::NAME,
            new EquipmentRequestAccepted($equipment, $sender, __FUNCTION__)
        );
    }

    public function equipmentRequestDenied(Equipment $equipment, Character $sender)
    {
        $this->eventDispatcher->dispatch(
            EquipmentRequestDenied::NAME,
            new EquipmentRequestDenied($equipment, $sender, __FUNCTION__)
        );
    }
}

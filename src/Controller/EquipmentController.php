<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 19/05/2018
 * Time: 20:08
 */

namespace App\Controller;


use App\Entity\Board;
use App\Entity\Character;
use App\Entity\Equipment;
use App\Entity\Notifications;
use App\Form\BoardCreate;
use App\Form\EquipmentCreate;
use App\Form\EquipmentSend;
use App\Utils\NotificationsSystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\NoCharacterException;
use App\Entity\User;

class EquipmentController extends Controller
{
    /**
     * @Route("/equipment", name="equipment-index")
     */
    public function index(Request $request)
    {
        //generate equip creation form
        //get list of equip
        $characterId = $request->query->get('cid', null);
        if ($this->isGranted('ROLE_STORY_TELLER')) {
            if ($characterId === null) {
                $character = null;
            } else {
                $character = $this->getDoctrine()->getRepository(Character::class)->find($characterId);
            }
        } else {
            $character = $this->getUser()->getCharacters()[0];
        }
        $limit = $request->query->get('limit', null);

        $equipments = $this->getDoctrine()->getRepository(Equipment::class)
            ->getAllByCharacter($character, $limit);
        $equipmentsRequest = $this->getDoctrine()->getRepository(Equipment::class)
            ->getEquipmentRequest($character, $limit);

        $data = [
            'equipments' => $equipments,
            'character' => $character,
            'equipmentsRequest' => $equipmentsRequest
        ];
        if ($request->isXmlHttpRequest()) {
            return $this->render('equipment/index.html.twig', $data);
        } else {
            return $this->render('equipment/index-no-ajax.html.twig', $data);
        }
    }

    /**
     * @Route("/equipment/create", name="equipment-create")
     */
    public function createEquip(Request $request, NotificationsSystem $notificationsSystem)
    {
        $actionType = $request->query->get('t', null);
        $characterId = $request->query->get('cid', null);

        $equipmentId = $request->query->get('eid', null);
        if ($equipmentId === null) {
            $equipment = new Equipment();
        } else {
            /** @var Equipment $equipment */
            $equipment = $this->getDoctrine()->getRepository(Equipment::class)->find($equipmentId);
            $equipmentOld = clone $equipment;
        }

        if ($characterId !== null) {
            $character = $this->getDoctrine()->getRepository(Character::class)->find($characterId);
            $equipment->setOwner($character);
        } else {
            $character = null;
        }
        $equipForm = $this->createForm(EquipmentCreate::class, $equipment);

        $equipForm->handleRequest($request);

        if ($equipForm->isSubmitted() && $equipForm->isValid()) {

            if ($equipmentId === null) {
                $this->getDoctrine()->getManager()->persist($equipment);
                $this->addFlash('notice', 'Oggetto creato ed assegnato con successo');
            } else {
                $this->addFlash('notice', 'Oggetto aggiornato con successo');
            }
            $this->getDoctrine()->getManager()->flush();

            //check updatedField
            if (
                isset($equipmentOld)
                && $equipment->getOwner() !== null
                && $equipmentOld->getOwner()->getId() !== $equipment->getOwner()->getId()
                && $equipmentOld->getName() === $equipment->getName()
                && $equipmentOld->getDescription() === $equipment->getDescription()
            ) {
                $notificationsSystem->equipmentReceived($equipment);
            }

            return $this->redirectToRoute('equipment-index', ['cid' => $characterId]);
        }

        return $this->render('equipment/create.html.twig', [
            'form' => $equipForm->createView(),
            'character' => $character,
            'actionType' => $actionType,
            'equipmentId' => $equipmentId,
            'equipment' => $equipment
        ]);
    }

    /**
     * @Route("/equipment/remove", name="equipment-remove")
     */
    public function deleteEquip(Request $request)
    {
        $characterId = $request->query->get('cid', null);
        $equipmentId = $request->query->get('eid', null);

        //handle equip deletion post
        if ($this->isGranted('ROLE_STORY_TELLER')) {
            $equipment = $this->getDoctrine()->getRepository(Equipment::class)->find($equipmentId);
            $this->getDoctrine()->getManager()->remove($equipment);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('notice', 'Oggetto eliminato con successo');
        }

        return $this->redirectToRoute('equipment-index', ['cid' => $characterId]);
    }

    /**
     * @Route("/equipment/unassign", name="equipment-unassign")
     */
    public function unassignEquip(Request $request)
    {
        $characterId = $request->query->get('cid', null);
        $equipmentId = $request->query->get('eid', null);

        //handle equip deletion post
        if ($this->isGranted('ROLE_STORY_TELLER')) {
            $equipment = $this->getDoctrine()->getRepository(Equipment::class)->find($equipmentId);

            $equipment->setOwner(null);

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('notice', 'Oggetto disassegnato con successo');
        }

        return $this->redirectToRoute('equipment-index', ['cid' => $characterId]);
    }

    /**
     * @Route("/equipment/send", name="equipment-send")
     */
    public function sendEquipment(Request $request, NotificationsSystem $notificationsSystem)
    {
        $equipmentId = $request->query->get('eid');
        $equipment = $this->getDoctrine()->getRepository(Equipment::class)->find($equipmentId);

        $equipForm = $this->createForm(EquipmentSend::class, $equipment);

        $equipForm->handleRequest($request);
        if ($equipForm->isSubmitted() && $equipForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $notificationsSystem->equipmentRequestReceived($equipment);

            $this->addFlash('notice', 'Richiesta inviata con successo');

            return $this->redirectToRoute('equipment-index');
        }

        return $this->render('equipment/send.html.twig', [
            'form' => $equipForm->createView()
        ]);
    }
    /**
     * @Route("/equipment/accept", name="equipment-accept")
     */
    public function acceptEquipment(Request $request, NotificationsSystem $notificationsSystem)
    {
        $equipmentId = $request->query->get('eid');
        $equipment = $this->getDoctrine()->getRepository(Equipment::class)->find($equipmentId);

        $receiver = $equipment->getReceiver();

        $sender = $equipment->getOwner();

        $equipment->setOwner($receiver);
        $equipment->setReceiver(null);

        $this->getDoctrine()->getManager()->flush();

        $notificationsSystem->equipmentRequestAccepted($equipment, $sender);

        return $this->redirectToRoute('equipment-index');
    }

    /**
     * @Route("/equipment/deny", name="equipment-deny")
     */
    public function denyEquipment(Request $request, NotificationsSystem $notificationsSystem)
    {
        $equipmentId = $request->query->get('eid');
        $equipment = $this->getDoctrine()->getRepository(Equipment::class)->find($equipmentId);

        $receiver = $equipment->getReceiver();

        $equipment->setReceiver(null);

        $this->getDoctrine()->getManager()->flush();

        $notificationsSystem->equipmentRequestAccepted($equipment, $receiver);

        return $this->redirectToRoute('equipment-index');
    }
}

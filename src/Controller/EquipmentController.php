<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 19/05/2018
 * Time: 20:08.
 */

namespace App\Controller;

use App\Entity\Character;
use App\Entity\Equipment;
use App\Form\EquipmentCreate;
use App\Form\EquipmentSend;
use App\Repository\CharacterRepository;
use App\Repository\EquipmentRepository;
use App\Utils\NotificationsSystem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EquipmentController extends AbstractController
{
    /**
     * @Route("/equipment", name="equipment-index")
     *
     * @param Request $request
     * @param CharacterRepository $characterRepository
     * @param EquipmentRepository $equipmentRepository
     *
     * @return Response
     */
    public function index(
        Request $request,
        CharacterRepository $characterRepository,
        EquipmentRepository $equipmentRepository)
    {
        //generate equip creation form
        //get list of equip
        $characterId = $request->query->get('cid', null);
        if ($this->isGranted('ROLE_STORY_TELLER')) {
            if (null === $characterId) {
                $character = null;
            } else {
                /** @var Character $character */
                $character = $characterRepository->find($characterId);
            }
        } else {
            $character = $this->getUser()->getCharacters()[0];
        }
        $limit = $request->query->get('limit', null);

        $equipments = $equipmentRepository->getAllByCharacter($character, $limit);
        $equipmentsRequest = $equipmentRepository->getEquipmentRequest($character);

        $data = [
            'equipments' => $equipments,
            'character' => $character,
            'equipmentsRequest' => $equipmentsRequest,
        ];
        if ($request->isXmlHttpRequest()) {
            return $this->render('equipment/index.html.twig', $data);
        } else {
            return $this->render('equipment/index-no-ajax.html.twig', $data);
        }
    }

    /**
     * @Route("/equipment/create", name="equipment-create")
     *
     * @param Request $request
     * @param NotificationsSystem $notificationsSystem
     * @param EquipmentRepository $equipmentRepository
     * @param CharacterRepository $characterRepository
     *
     * @return Response
     */
    public function createEquip(
        Request $request,
        NotificationsSystem $notificationsSystem,
        EquipmentRepository $equipmentRepository,
        CharacterRepository $characterRepository)
    {
        $actionType = $request->query->get('t', null);
        $characterId = $request->query->get('cid', null);

        $equipmentId = $request->query->get('eid', null);
        if (null === $equipmentId) {
            $equipment = new Equipment();
            $equipmentOld = null;
        } else {
            /** @var Equipment $equipment */
            $equipment = $equipmentRepository->find($equipmentId);
            $equipmentOld = clone $equipment;
        }

        if (null !== $characterId) {
            /** @var Character $character */
            $character = $characterRepository->find($characterId);
            $equipment->setOwner($character);
        } else {
            $character = null;
        }
        $equipForm = $this->createForm(EquipmentCreate::class, $equipment);

        $equipForm->handleRequest($request);

        if ($equipForm->isSubmitted() && $equipForm->isValid()) {
            if (null === $equipmentOld) {
                $this->getDoctrine()->getManager()->persist($equipment);
                $this->addFlash('notice', 'Oggetto creato ed assegnato con successo');
            } else {
                $remainingQuantity = $equipmentOld->getQuantity() - $equipment->getQuantity();

                if ($remainingQuantity > 0 && $equipmentOld->getOwner() !== $equipment->getOwner()) {
                    $remainingEquipment = new Equipment();
                    $remainingEquipment->setOwner($equipmentOld->getOwner());
                    $remainingEquipment->setQuantity($remainingQuantity);
                    $remainingEquipment->setDescription($equipment->getDescription());
                    $remainingEquipment->setName($equipment->getName());

                    $this->getDoctrine()->getManager()->persist($remainingEquipment);
                }

                $this->addFlash('notice', 'Oggetto aggiornato con successo');
            }
            $this->getDoctrine()->getManager()->flush();

            //check updatedField
            if (
                isset($equipmentOld)
                && null !== $equipment->getOwner()
                && $equipmentOld->getOwner()->getId() !== $equipment->getOwner()->getId()
                && $equipmentOld->getName() === $equipment->getName()
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
            'equipment' => $equipment,
        ]);
    }

    /**
     * @Route("/equipment/remove", name="equipment-remove")
     *
     * @param Request $request
     * @param EquipmentRepository $equipmentRepository
     *
     * @return Response
     */
    public function deleteEquip(
        Request $request,
        EquipmentRepository $equipmentRepository)
    {
        $characterId = $request->query->get('cid', null);
        $equipmentId = $request->query->get('eid', null);

        //handle equip deletion post
        if ($this->isGranted('ROLE_STORY_TELLER')) {
            $equipment = $equipmentRepository->find($equipmentId);
            $this->getDoctrine()->getManager()->remove($equipment);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('notice', 'Oggetto eliminato con successo');
        }

        return $this->redirectToRoute('equipment-index', ['cid' => $characterId]);
    }

    /**
     * @Route("/equipment/unassign", name="equipment-unassign")
     *
     * @param Request $request
     * @param EquipmentRepository $equipmentRepository
     *
     * @return Response
     */
    public function unassignEquip(
        Request $request,
        EquipmentRepository $equipmentRepository)
    {
        $characterId = $request->query->get('cid', null);
        $equipmentId = $request->query->get('eid', null);

        //handle equip deletion post
        if ($this->isGranted('ROLE_STORY_TELLER')) {
            $equipment = $equipmentRepository->find($equipmentId);

            $equipment->setOwner(null);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('notice', 'Oggetto disassegnato con successo');
        }

        return $this->redirectToRoute('equipment-index', ['cid' => $characterId]);
    }

    /**
     * @Route("/equipment/send", name="equipment-send")
     *
     * @param Request $request
     * @param NotificationsSystem $notificationsSystem
     * @param EquipmentRepository $equipmentRepository
     *
     * @return Response
     */
    public function sendEquipment(
        Request $request,
        NotificationsSystem $notificationsSystem,
        EquipmentRepository $equipmentRepository)
    {
        $equipmentId = $request->query->get('eid');

        /** @var Equipment $equipment */
        $equipment = $equipmentRepository->find($equipmentId);
        $equipmentOld = clone $equipment;

        $equipForm = $this->createForm(EquipmentSend::class, $equipment);

        $equipForm->handleRequest($request);
        if ($equipForm->isSubmitted() && $equipForm->isValid()) {
            if ($equipment->getReceiver() !== null &&
                    $equipmentOld->getOwner()->getId() === $equipment->getReceiver()->getId()) {
                $this->addFlash('notice', 'Non puoi inviare un oggetto a te stesso');

                return $this->redirectToRoute('equipment-index');
            }

            $remainingQuantity = $equipmentOld->getQuantity() - $equipment->getQuantity();
            if ($equipment->getReceiver() !== null
                    && $remainingQuantity > 0
                    && $equipmentOld->getOwner()->getId() !== $equipment->getReceiver()->getId()) {
                $remainingEquipment = new Equipment();
                $remainingEquipment->setOwner($equipmentOld->getOwner());
                $remainingEquipment->setQuantity($remainingQuantity);
                $remainingEquipment->setName($equipment->getName());
                $remainingEquipment->setDescription($equipment->getDescription());

                $this->getDoctrine()->getManager()->persist($remainingEquipment);
            }

            $this->getDoctrine()->getManager()->flush();

            $notificationsSystem->equipmentRequestReceived($equipment);

            $this->addFlash('notice', 'Richiesta inviata con successo');

            return $this->redirectToRoute('equipment-index');
        }

        return $this->render('equipment/send.html.twig', [
            'form' => $equipForm->createView(),
        ]);
    }

    /**
     * @Route("/equipment/accept", name="equipment-accept")
     *
     * @param Request $request
     * @param NotificationsSystem $notificationsSystem
     * @param EquipmentRepository $equipmentRepository
     *
     * @return Response
     */
    public function acceptEquipment(
        Request $request,
        NotificationsSystem $notificationsSystem,
        EquipmentRepository $equipmentRepository)
    {
        $equipmentId = $request->query->get('eid');

        $equipment = $equipmentRepository->find($equipmentId);
        $receiver = $equipment->getReceiver();

        $remainingEquipment = $equipmentRepository->getByOwnerNameAndDescription($receiver, $equipment->getName());

        $sender = $equipment->getOwner();

        if (null !== $remainingEquipment) {
            $remainingEquipment->setQuantity(
                $remainingEquipment->getQuantity() + $equipment->getQuantity()
            );
            $this->getDoctrine()->getManager()->remove($equipment);
        } else {
            $equipment->setOwner($receiver);
            $equipment->setReceiver(null);
        }

        $this->getDoctrine()->getManager()->flush();

        $notificationsSystem->equipmentRequestAccepted($equipment, $sender);

        return $this->redirectToRoute('equipment-index');
    }

    /**
     * @Route("/equipment/deny", name="equipment-deny")
     *
     * @param Request $request
     * @param NotificationsSystem $notificationsSystem
     * @param EquipmentRepository $equipmentRepository
     *
     * @return Response
     */
    public function denyEquipment(
        Request $request,
        NotificationsSystem $notificationsSystem,
        EquipmentRepository $equipmentRepository)
    {
        $equipmentId = $request->query->get('eid');

        /** @var Equipment $equipment */
        $equipment = $equipmentRepository->find($equipmentId);

        $remainingEquipment = $equipmentRepository->getByOwnerNameAndDescription(
            $equipment->getOwner(),
            $equipment->getName()
        );

        $receiver = $equipment->getReceiver();

        if (null != $remainingEquipment) {
            $remainingEquipment->setQuantity(
                $remainingEquipment->getQuantity() + $equipment->getQuantity()
            );
            $this->getDoctrine()->getManager()->remove($equipment);
        } else {
            $equipment->setReceiver(null);
        }

        $this->getDoctrine()->getManager()->flush();

        if (null !== $receiver) {
            $notificationsSystem->equipmentRequestAccepted($equipment, $receiver);
        }

        return $this->redirectToRoute('equipment-index');
    }
}

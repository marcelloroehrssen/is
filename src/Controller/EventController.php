<?php

namespace App\Controller;

use App\Form\ElysiumProposalEdit;
use App\Form\ValueObject\ElysiumCreateVo;
use App\Repository\ElysiumProposalRepository;
use App\Repository\ElysiumRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ElysiumCreate;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Elysium;
use App\Form\ElysiumProposalCreate;
use App\Entity\ElysiumProposal;
use App\Utils\NotificationsSystem;
use Exception;

class EventController extends AbstractController
{
    /**
     * @Route("/event", name="event_index")
     *
     * @param Request                   $request
     * @param NotificationsSystem       $notification
     * @param UserRepository            $userRepository
     * @param ElysiumRepository         $elysiumRepository
     * @param ElysiumProposalRepository $proposalRepository
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function index(
        Request $request,
        NotificationsSystem $notification,
        UserRepository $userRepository,
        ElysiumRepository $elysiumRepository,
        ElysiumProposalRepository $proposalRepository)
    {
        $form = null;
        if ($this->isGranted('ROLE_ADMIN')) {
            $elysiumVo = new ElysiumCreateVo();

            $form = $this->createForm(ElysiumCreate::class, $elysiumVo);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $elysium = new Elysium();
                $elysium->setAdminAuthor($this->getUser());
                $elysium->setCreatedAt(new \DateTime());

                $elysium->setAddress(sprintf('%s %s', $elysiumVo->getLocationName(), $elysiumVo->getAddress()));
                $elysium->setDate($elysiumVo->getDate());

                $this->getDoctrine()->getManager()->persist($elysium);
                $this->getDoctrine()->getManager()->flush();

                $notification->newEventCreated($elysium);

                return $this->redirectToRoute('event_index');
            }
            $form = $form->createView();
        }

        $user = $userRepository->findByRole('ROLE_EDILE');
        $user = array_pop($user);

        $edile = null;
        if ($user && null !== $user->getCharacters()[0]) {
            $edile = $user->getCharacters()[0];
        }

        $events = $elysiumRepository->findAll();

        $proposals = $proposalRepository->findAll();
        if (!$this->isGranted('ROLE_ADMIN')) {
            $proposals = $proposalRepository->getProposalByCharacter($this->getUser()->getCharacters()[0]);
        }

        return $this->render('event/index.html.twig', [
            'form' => $form,
            'proposals' => $proposals,
            'events' => $events,
            'now' => new \DateTime(),
            'edile' => $edile,
        ]);
    }

    /**
     * @Route("/event/delete/{eid}", name="event_delete")
     * @ParamConverter("event", options={"id" = "eid"})
     *
     * @param Elysium $event
     *
     * @return Response
     */
    public function eventDelete(Elysium $event)
    {
        if (null == $event) {
            return $this->redirectToRoute('event_index');
        }

        $this->getDoctrine()->getManager()->remove($event);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('event_index');
    }

    /**
     * @Route("/event/propose/{id}", name="event_proposal", defaults={"id"=null})
     *
     * @param Request $request
     * @param NotificationsSystem $notification
     * @param ElysiumProposal|null $elysiumProposal
     *
     * @return Response
     */
    public function eventProposal(
        Request $request,
        NotificationsSystem $notification,
        ElysiumProposal $elysiumProposal = null)
    {
        $isEdit = true;
        $form = $this->createForm(ElysiumProposalEdit::class, $elysiumProposal);

        if (null === $elysiumProposal) {
            $isEdit = false;
            $elysiumProposal = new ElysiumProposal();
            $form = $this->createForm(ElysiumProposalCreate::class, $elysiumProposal);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$isEdit) {
                if (null !== $this->getUser()->getCharacters()[0]) {
                    $elysiumProposal->setCharacterAuthor($this->getUser()->getCharacters()[0]);
                }
                $this->getDoctrine()->getManager()->persist($elysiumProposal);
            }
            $this->getDoctrine()->getManager()->flush();

            $notification->newEventProposalCreated($this->getUser()->getCharacters()[0]);
            $this->addFlash('notice', 'La tua proposta è stata inviata con successo');

            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/proposal.html.twig', [
            'form' => $form->createView(),
            'proposal' => $elysiumProposal,
            'isEdit' => $isEdit,
        ]);
    }

    /**
     * @Route("/event/assign/{eid}/{pid}", name="event_assign")
     * @ParamConverter("event", options={"id" = "eid"})
     * @ParamConverter("proposal", options={"id" = "pid"})
     *
     * @param Elysium $event
     * @param ElysiumProposal $proposal
     * @param NotificationsSystem $notification
     *
     * @return Response
     */
    public function eventAssign(
        Elysium $event,
        ElysiumProposal $proposal,
        NotificationsSystem $notification)
    {
        if (false !== $event->getProposal()->current()) {
            $event->getProposal()->current()->setElysium(null);
        }

        $proposal->setElysium($event);

        $this->getDoctrine()->getManager()->flush();

        $notification->eventAssigned($event);

        return $this->redirectToRoute('event_index');
    }

    /**
     * @Route("/event/reject/{pid}", name="event_reject")
     * @ParamConverter("proposal", options={"id" = "pid"})
     *
     * @param ElysiumProposal $proposal
     *
     * @return Response
     */
    public function eventReject(ElysiumProposal $proposal)
    {
        $this->getDoctrine()->getManager()->remove($proposal);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('event_index');
    }

    /**
     * @Route("/event/proposal/view/{eid}", name="event_proposal_view")
     * @ParamConverter("event", options={"id" = "eid"})
     *
     * @param Elysium $event
     *
     * @return Response
     */
    public function eventProposalView(Elysium $event)
    {
        return $this->render('event/view-proposal.html.twig', [
            'assigned' => $event->getProposal()->current(),
            'proposals' => $event->getValidProposal(),
            'eid' => $event->getId(),
        ]);
    }

    /**
     * @Route("/event/proposal/delete/{eid}", name="event_proposal_delete")
     * @ParamConverter("event", options={"id" = "eid"})
     *
     * @param Elysium $event
     *
     * @return Response
     */
    public function eventProposalDelete(Elysium $event)
    {
        $this->getDoctrine()->getManager()->remove($event);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('event_index');
    }

    /**
     * @Route("/event/proposal/info-view/{eid}", name="event_proposal_info_view")
     * @ParamConverter("proposal", options={"id" = "eid"})
     *
     * @param ElysiumProposal $proposal
     *
     * @return Response
     */
    public function eventProposalInfoView(ElysiumProposal $proposal)
    {
        $viewAll = false;
        if ($this->isGranted('ROLE_STORY_TELLER')) {
            $viewAll = true;
        } elseif (!$this->isGranted('ROLE_STORY_TELLER')
                && $proposal->getCharacterAuthor()->getId() == $this->getUser()->getCharacters()[0]->getId()) {
            $viewAll = true;
        }

        return $this->render('event/info-view-proposal.html.twig', [
            'assigned' => $proposal,
            'viewAll' => $viewAll,
        ]);
    }
}

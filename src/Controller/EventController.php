<?php

namespace App\Controller;

use App\Form\ElysiumProposalEdit;
use App\Form\ValueObject\ElysiumCreateVo;
use App\Repository\ElysiumProposalRepository;
use App\Repository\ElysiumRepository;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Form\ElysiumCreate;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Elysium;
use App\Form\ElysiumProposalCreate;
use App\Entity\ElysiumProposal;
use App\Utils\NotificationsSystem;

class EventController extends Controller
{
    /**
     * @Route("/event", name="event_index")
     *
     * @param Request $request
     * @param NotificationsSystem $notification
     * @param UserRepository $userRepository
     * @param ElysiumRepository $elysiumRepository
     * @param ElysiumProposalRepository $proposalRepository
     * @return mixed
     * @throws \Exception
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
     */
    public function eventDelete($eid, ElysiumRepository $elysiumRepository, ElysiumProposalRepository $elysiumProposalRepository)
    {
        $event = $elysiumRepository->find($eid);

        if (null === $event) {
            return $this->redirectToRoute('event_index');
        }

        $this->getDoctrine()->getManager()->remove($event);

        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('event_index');
    }

    /**
     * @Route("/event/propose/{id}", name="event_proposal", defaults={"id"=null})
     */
    public function eventProposal(ElysiumProposal $elysiumProposal = null, Request $request, NotificationsSystem $notification)
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
     * @Route("/event/reject/{eid}/{pid}", name="event_assign")
     */
    public function eventReject($eid, $pid, NotificationsSystem $notification)
    {
        $event = $this->getDoctrine()->getManager()->getRepository(Elysium::class)->find($eid);

        if (false !== $event->getProposal()->current()) {
            $event->getProposal()->current()->setElysium(null);
        }

        $proposals = $this->getDoctrine()->getManager()->getRepository(ElysiumProposal::class)->find($pid);

        $proposals->setElysium($event);

        $this->getDoctrine()->getManager()->flush();

        $notification->eventAssigned($event);

        return $this->redirectToRoute('event_index');
    }

    /**
     * @Route("/event/assign/{pid}", name="event_reject")
     */
    public function eventAssign($pid)
    {
        $proposals = $this->getDoctrine()->getManager()->getRepository(ElysiumProposal::class)->find($pid);
        $this->getDoctrine()->getManager()->remove($proposals);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('event_index');
    }

    /**
     * @Route("/event/proposal/view/{eid}", name="event_proposal_view")
     */
    public function eventProposalView($eid, ElysiumRepository $elysiumRepository)
    {
        /** @var Elysium $event */
        $event = $elysiumRepository->find($eid);

        return $this->render('event/view-proposal.html.twig', [
            'assigned' => $event->getProposal()->current(),
            'proposals' => $event->getValidProposal(),
            'eid' => $eid,
        ]);
    }

    /**
     * @Route("/event/proposal/delete/{eid}", name="event_proposal_delete")
     */
    public function eventProposalDelete($eid, ElysiumProposalRepository $elysiumProposalRepository)
    {
        /** @var ElysiumProposal $event */
        $event = $elysiumProposalRepository->find($eid);
        $this->getDoctrine()->getManager()->remove($event);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('event_index');
    }

    /**
     * @Route("/event/proposal/info-view/{eid}", name="event_proposal_info_view")
     */
    public function eventProposalInfoView($eid, ElysiumProposalRepository $elysiumRepository)
    {
        /** @var ElysiumProposal $proposal */
        $proposal = $elysiumRepository->find($eid);

        $viewAll = false;
        if ($this->isGranted('ROLE_STORY_TELLER')) {
            $viewAll = true;
        } elseif (!$this->isGranted('ROLE_STORY_TELLER')
                && $proposal->getCharacterAuthor()->getId() == $this->getUser()->getCharacters()[0]->getId()) {
            $viewAll = true;
        }

        return $this->render('event/info-view-proposal.html.twig', [
            'assigned' => $proposal,
            'viewAll'  => $viewAll,
        ]);
    }
}

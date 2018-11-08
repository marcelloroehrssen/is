<?php

namespace App\Controller;

use App\Form\ValueObject\ElysiumCreateVo;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\User;
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
     */
    public function index(Request $request, NotificationsSystem $notification)
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
        
        $edile = null;
        $user = $this->getDoctrine()->getManager()->getRepository(User::class)->findByRole('ROLE_EDILE');
        $user = array_pop($user);

        $edile = null;
        if ($user && $user->getCharacters()[0] !== null) {
            $edile = $user->getCharacters()[0];
        }
        
        $events = $this->getDoctrine()->getManager()->getRepository(Elysium::class)->getAll();
        return $this->render('event/index.html.twig',[
            'form' => $form,
            'events' => $events,
            'now' => new \DateTime(),
            'edile' => $edile
        ]);
    }
    
    /**
     * @Route("/event/delete/{eid}", name="event_delete")
     */
    public function eventDelete($eid)
    {
        $event = $this->getDoctrine()->getManager()->getRepository(Elysium::class)->find($eid);
        
        if (null === $event) {
            return $this->redirectToRoute('event_index');
        }
        
        $this->getDoctrine()->getManager()->remove($event);
        $this->getDoctrine()->getManager()->flush();
        
        return $this->redirectToRoute('event_index');
    }
    
    /**
     * @Route("/event/propose", name="event_proposal")
     */
    public function eventProposal(Request $request, NotificationsSystem $notification)
    {
        
        $elysiumProposal = new ElysiumProposal();
        
        $form = $this->createForm(ElysiumProposalCreate::class, $elysiumProposal);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            if ($this->getUser()->getCharacters()[0] !== null) {
                $elysiumProposal->setCharacterAuthor($this->getUser()->getCharacters()[0]);
            }
            $this->getDoctrine()->getManager()->persist($elysiumProposal);
            $this->getDoctrine()->getManager()->flush();
           
            $notification->newEventProposalCreated($this->getUser()->getCharacters()[0]);
            
            return $this->redirectToRoute('event_index');
        }
        
        return $this->render('event/proposal.html.twig',[
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * @Route("/event/assign/{eid}/{pid}", name="event_assign")
     */
    public function eventAssign($eid, $pid, NotificationsSystem $notification)
    {
        $event = $this->getDoctrine()->getManager()->getRepository(Elysium::class)->find($eid);
        
        if ($event->getProposal()->current() !== false) {
            $event->getProposal()->current()->setElysium(null);
        }
        
        $proposals = $this->getDoctrine()->getManager()->getRepository(ElysiumProposal::class)->find($pid);
        
        $proposals->setElysium($event);
        
        $this->getDoctrine()->getManager()->flush();
        
        $notification->eventAssigned($event);
        
        return $this->redirectToRoute('event_index');
    }
    
    /**
     * @Route("/event/proposal/view/{eid}", name="event_proposal_view")
     */
    public function eventProposalView($eid)
    {
        /**
         * @var Elysium $event
         */
        $event = $this->getDoctrine()->getManager()->getRepository(Elysium::class)->find($eid);
        
        /**
         * @var ElysiumProposal $proposals
         */
        $proposals = $this->getDoctrine()->getManager()->getRepository(ElysiumProposal::class)->getUnassigned();
        
        return $this->render('event/view-proposal.html.twig',[
            'assigned' => $event->getProposal()->current(),
            'proposals' => $proposals,
            'eid' => $eid
        ]);
    }
    
    /**
     * @Route("/event/proposal/info-view/{eid}", name="event_proposal_info_view")
     */
    public function eventProposalInfoView($eid)
    {
        /**
         * @var Elysium $event
         */
        $proposal = $this->getDoctrine()->getManager()->getRepository(ElysiumProposal::class)->find($eid);
        
        return $this->render('event/info-view-proposal.html.twig',[
            'assigned' => $proposal
        ]);
    }
}

<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\User;
use App\Form\ElysiumCreate;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Elysium;
use App\Form\ElysiumProposalCreate;
use App\Entity\ElysiumProposal;

class EventController extends Controller
{
    /**
     * @Route("/event", name="event_index")
     */
    public function index(Request $request)
    {
        $form = null;
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_STORY_TELLER')) {
            
            $elysium = new Elysium();
            
            $form = $this->createForm(ElysiumCreate::class, $elysium);
            
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                $elysium->setAdminAuthor($this->getUser());
                $elysium->setCreatedAt(new \DateTime());
                
                $this->getDoctrine()->getManager()->persist($elysium);
                $this->getDoctrine()->getManager()->flush();
                
                return $this->redirectToRoute('event_index');
            }
        }
        
        $events = $this->getDoctrine()->getManager()->getRepository(Elysium::class)->getAll();
        return $this->render('event/index.html.twig',[
            'form' => $form->createView(),
            'events' => $events
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
     * @Route("/event/propose/{eid}", name="event_proposal")
     */
    public function eventProposal(Request $request, $eid)
    {
        $event = $this->getDoctrine()->getManager()->getRepository(Elysium::class)->find($eid);
        
        $elysiumProposal = new ElysiumProposal();
        
        $form = $this->createForm(ElysiumProposalCreate::class, $elysiumProposal);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $elysiumProposal->setCharacterAuthor($this->getUser()->getCharacters()[0]);
            $elysiumProposal->setElysium($event);
            
            $this->getDoctrine()->getManager()->persist($elysiumProposal);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('event_index');
        }
                
        $edile = null;
        $user = $this->getDoctrine()->getManager()->getRepository(User::class)->findByRole('ROLE_EDILE');
        $user = array_pop($user);
        $edile = null;
        if ($user && $user->getCharacters()[0] !== null) {
            $edile = $user->getCharacters()[0];
        }
        
        return $this->render('event/proposal.html.twig',[
            'form' => $form->createView(),
            'event' => $event,
            'edile' => $edile
        ]);
    }
    
    /**
     * @Route("/event/assign/{eid}", name="event_assign")
     */
    public function eventAssign($eid)
    {
        
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
        
        return $this->render('event/view-proposal.html.twig',[
            'proposals' => $event->getProposal()
        ]);
    }
}

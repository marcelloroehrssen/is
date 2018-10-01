<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Downtime;
use App\Form\DowntimeAdd;
use App\Form\DowntimeResolve;
use App\Utils\NotificationsSystem;
use App\NoCharacterException;
use App\Form\DowntimeCommentsAdd;
use App\Entity\DowntimeComment;

class DowntimeController extends Controller
{

    private $pageSize = 5;

    /**
     * @Route("/downtime", name="downtime-index")
     */
    public function indexController(Request $request)
    {

        $downtimeRepo = $this->getDoctrine()
            ->getManager()
            ->getRepository(Downtime::class);
        
            
        if (!$this->isGranted('ROLE_STORY_TELLER')){
            $character = $this->getUser()->getCharacters()[0];
            
            if (null === $character) {
                throw new NoCharacterException();
            }
            
            $status = null;
            $paginatedDowntime = $downtimeRepo->getPaginatedDowntime(
                $character, 1, $this->pageSize
            );

            $simple = $downtimeRepo->getCountForDate($character, 's', new \DateTime());
            $complex = $downtimeRepo->getCountForDate($character, 'c', new \DateTime());
        } else {
            $character = null;
            
            $status = ($request->get('status', 'unresolved') == 'resolved') ? 'notnull' : null;
            
            $paginatedDowntime = $downtimeRepo->getAdminPaginatedDowntime(
                1, $this->pageSize, $status
            );
            
            $simple = 0;
            $complex = 0;
        }

        $pagesCount = ceil(count($paginatedDowntime) / $this->pageSize);

        return $this->render('downtime/index.html.twig', [
            'pagesCount' => $pagesCount,
            'simple' => $simple,
            'complex' => $complex,
            'character' => $character,
            'status' => $status
        ]);
    }

    /**
     * @Route("/downtime/create/{dtid}", defaults={"dtid"=null}, name="downtime-create")
     */
    public function createDownTime(Request $request, $dtid = null)
    {
        if (empty($dtid)) {
            $downTime = new Downtime();
        } else {
            $downTime = $this->getDoctrine()->getRepository(Downtime::class)->find($dtid);
        }
        dump($downTime);

        $form = $this->createForm(DowntimeAdd::class, $downTime);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                
                if (empty($dtid)) {
                    $downTime->setCharacter($this->getUser()->getCharacters()[0]);
                    $this->getDoctrine()->getManager()->persist($downTime);
                }
                $this->getDoctrine()->getManager()->flush();
            }
        }

        return $this->redirectToRoute('downtime-index');
    }

    /**
     * @Route("/downtime/result/{page}/{lastDate}", name="downtime-result")
     */
    public function renderResult(Request $request, $page = 1, $lastDate = '')
    {
        $downtimeRepo = $this->getDoctrine()
            ->getManager()
            ->getRepository(Downtime::class);

        if (!$this->isGranted('ROLE_STORY_TELLER')){
            $paginatedDowntime = $downtimeRepo->getPaginatedDowntime(
                    $this->getUser()->getCharacters()[0], $page, $this->pageSize
            );
        } else {
            $status = $request->query->get('status', 'unresolved') == 'unresolved' ? null : 'notnull';
            
            $paginatedDowntime = $downtimeRepo->getAdminPaginatedDowntime(
                1, $this->pageSize, $status
            );
        }

        return $this->render('downtime/results.html.twig', [
            'downtimes' => $paginatedDowntime,
            'lastDate' => $lastDate,
            'page' => $page
        ]);
    }
    
    /**
     * @Route("/downtime/delete/{dtid}", name="downtime-delete")
     */
    public function delete(int $dtid)
    {
        $downtime = $this->getDoctrine()->getRepository(Downtime::class)->find($dtid);

        $this->getDoctrine()->getManager()->remove($downtime);
        $this->getDoctrine()->getManager()->flush();
        
        return $this->redirectToRoute('downtime-index');
    }
    
    /**
     * @Route("/downtime/view/{type}/{dtid}", defaults={"dtid"=null}, name="downtime-view")
     */
    public function view(string $type, int $dtid = null)
    {
        $downtime = new Downtime();
        if (!empty($dtid)) {
            $downtime = $this->getDoctrine()->getManager()->getRepository(Downtime::class)->find($dtid);
        }
        
        $downtime->setType($type);
        
        $form = $this->createForm(DowntimeAdd::class, $downtime);
        
        return $this->render('downtime/view.html.twig', [
            'downtime' => $form->createView(),
            'dtid' => $dtid,
        ]);
    }
    
    /**
     * @Route("/downtime/resolve/{dtid}", defaults={"dtid"=null}, name="downtime-resolve")
     */
    public function resolve(int $dtid = null)
    {
        $downtime = new Downtime();
        
        $downtime->setId($dtid);
        
        $form = $this->createForm(DowntimeResolve::class, $downtime);
        $formAdd = $this->createForm(DowntimeCommentsAdd::class);
        
        return $this->render('downtime/resolve.html.twig', [
            'downtime' => $this->getDoctrine()->getManager()->getRepository(Downtime::class)->find($dtid),
            'downtimeForm' => $form->createView(),
            'downtimeCommentsForm' => $formAdd->createView(),
        ]);
    }
    
    /**
     * @Route("/downtime/resolve-do/{dtid}", name="downtime-resolve-do")
     */
    public function resolveDo(Request $request, $dtid, NotificationsSystem $notificationSystem)
    {
        $downtime = $this->getDoctrine()->getManager()->getRepository(Downtime::class)->find($dtid);
        
        $form = $this->createForm(DowntimeResolve::class, $downtime);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $downtime->setResolvedAt(new \DateTime());
            $this->getDoctrine()->getEntityManager()->flush();
            
            $notificationSystem->downtimeResolved($downtime->getCharacter(), $downtime);
        }
        return $this->redirectToRoute('downtime-index');
    }
    /**
     * @Route("/downtime/comments-add/{dtid}", name="downtime-comments-add")
     */
    public function commentsAdd(Request $request, $dtid, NotificationsSystem $notificationSystem)
    {
        $downtimeComment = new DowntimeComment();
        
        $form = $this->createForm(DowntimeCommentsAdd::class, $downtimeComment);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $downtimeComment->setAuthor($this->getUser());
            
            $downtime = $this->getDoctrine()->getManager()->getRepository(Downtime::class)->find($dtid);
            $downtimeComment->setDowntime($downtime);
                        
            $this->getDoctrine()->getEntityManager()->persist($downtimeComment);
            $this->getDoctrine()->getEntityManager()->flush();
            
            $notificationSystem->downtimeResolved($downtime->getCharacter(), $downtime);
        }
        return $this->redirectToRoute('downtime-index');
    }
}

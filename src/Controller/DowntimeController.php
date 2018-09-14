<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Downtime;
use App\Form\DowntimeAdd;

class DowntimeController extends Controller
{

    private $pageSize = 5;

    /**
     * @Route("/downtime", name="downtime-index")
     */
    public function indexController()
    {

        $downtimeRepo = $this->getDoctrine()
            ->getManager()
            ->getRepository(Downtime::class);
        
        $character = $this->getUser()->getCharacters()[0];
        
        $paginatedDowntime = $downtimeRepo->getPaginatedDowntime(
            $character, 1, $this->pageSize
        );

        $pagesCount = ceil(count($paginatedDowntime) / $this->pageSize);

        return $this->render('downtime/index.html.twig', [
            'pagesCount' => $pagesCount,
            'simple' => $downtimeRepo->getCountForDate('s', new \DateTime()),
            'complex' => $downtimeRepo->getCountForDate('c', new \DateTime()),
            'character' => $character
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
    public function renderResult($page = 1, $lastDate = '')
    {
        $downtimeRepo = $this->getDoctrine()
            ->getManager()
            ->getRepository(Downtime::class);

        $paginatedDowntime = $downtimeRepo->getPaginatedDowntime(
                $this->getUser()->getCharacters()[0], $page, $this->pageSize
        );

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
}

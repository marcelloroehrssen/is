<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Downtime;
use App\Entity\Merits;
use App\Form\MeritsShow;
use App\Form\DowntimeAdd;
use App\Form\DowntimeNormalCreate;
use Symfony\Component\HttpFoundation\JsonResponse;

class DowntimeController extends Controller
{

    private $pageSize = 5;

    /**
     * @Route("/downtime", name="downtime-index")
     */
    public function indexController()
    {
        $meritsShow = $this->createForm(MeritsShow::class);

        $downtimeAdd = $this->createForm(DowntimeAdd::class);

        $downtimeNormalCreate = $this->createForm(DowntimeNormalCreate::class);

        $downtimeRepo = $this->getDoctrine()
            ->getManager()
            ->getRepository(Downtime::class);

        $paginatedDowntime = $downtimeRepo->getPaginatedDowntime(
                $this->getUser()->getCharacters()[0], 1, $this->pageSize
        );

        $pagesCount = ceil(count($paginatedDowntime) / $this->pageSize);

        return $this->render('downtime/index.html.twig', [
            'meritsShow' => $meritsShow->createView(),
            'downtimeAdd' => $downtimeAdd->createView(),
            'downtimeNormalCreate' => $downtimeNormalCreate->createView(),
            'pagesCount' => $pagesCount
        ]);
    }

    /**
     * @Route("/downtime/create/{dtid}", name="downtime-create")
     */
    public function createDownTime(Request $request, $dtid = null)
    {
        if (empty($dtid)) {
            $downTime = new Downtime();
        }

        $form = $this->createForm(DowntimeAdd::class, $downTime);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            $form = $this->createForm(DowntimeNormalCreate::class, $downTime);
            $form->handleRequest($request);
        }

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $downTime->setCharacter($this->getUser()->getCharacters()[0]);

                $this->getDoctrine()->getManager()->persist($downTime);
                $this->getDoctrine()->getManager()->flush();
            } else {
                dump($form);
                die();
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
     * @Route("/downtime/load/{meritsId}", name="downtime-load-merits")
     */
    public function loadMeritsDt($meritsId = null)
    {
        $meritsRepo = $this->getDoctrine()
            ->getManager()
            ->getRepository(Merits::class);

        $merit = $meritsRepo->find($meritsId);

        return new JsonResponse([
            'title' => sprintf("Utilizzo merito %s",$merit->getName()),
            'text' => $merit->getAssociatedDowntime(),
        ]);
    }
}

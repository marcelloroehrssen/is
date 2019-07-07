<?php

namespace App\Controller;

use App\Repository\DowntimeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    private $pageSize = 15;

    /**
     * @Route("/downtime", name="downtime-index")
     *
     * @param Request $request
     * @param DowntimeRepository $downtimeRepo
     *
     * @return Response
     *
     * @throws NoCharacterException
     */
    public function indexController(Request $request, DowntimeRepository $downtimeRepo)
    {
        if (!$this->isGranted('ROLE_STORY_TELLER')) {
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

            $status = $request->query->get('status', Downtime::STATUS_UNRESOLVED);

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
            'status' => $status,
        ]);
    }

    /**
     * @Route("/downtime/create/{dtid}", defaults={"dtid"=null}, name="downtime-create")
     * @ParamConverter("downTime", options={"id" = "dtid"})
     *
     * @param Request $request
     * @param Downtime $downTime
     *
     * @return Response
     */
    public function createDownTime(Request $request, Downtime $downTime = null)
    {
        $isNew = false;
dump($downTime);
        if (null === $downTime) {
            $isNew = true;
            $downTime = new Downtime();
        }
        $form = $this->createForm(DowntimeAdd::class, $downTime);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($isNew) {
                $downTime->setCharacter($this->getUser()->getCharacters()[0]);
                $this->getDoctrine()->getManager()->persist($downTime);
            }
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('notice', 'Downtime creato con successo');
        }

        return $this->redirectToRoute('downtime-index');
    }

    /**
     * @Route("/downtime/result/{page}/{lastDate}", name="downtime-result")
     *
     * @param Request $request
     * @param DowntimeRepository $downtimeRepo
     * @param int $page
     * @param string $lastDate
     *
     * @return Response
     */
    public function renderResult(
        Request $request,
        DowntimeRepository $downtimeRepo,
        int $page = 1,
        string $lastDate = '')
    {
        if (!$this->isGranted('ROLE_STORY_TELLER')) {
            $paginatedDowntime = $downtimeRepo->getPaginatedDowntime(
                $this->getUser()->getCharacters()[0], $page, $this->pageSize
            );
        } else {
            $status = $request->query->get('status', Downtime::STATUS_UNRESOLVED);

            $paginatedDowntime = $downtimeRepo->getAdminPaginatedDowntime(
                $page, $this->pageSize, $status
            );
        }

        return $this->render('downtime/results.html.twig', [
            'downtimes' => $paginatedDowntime,
            'lastDate' => $lastDate,
            'page' => $page,
        ]);
    }

    /**
     * @Route("/downtime/delete/{dtid}", name="downtime-delete")
     * @ParamConverter("downtime", options={"id" = "dtid"})
     *
     * @param Downtime $downtime
     *
     * @return Response
     */
    public function delete(Downtime $downtime)
    {
        $this->getDoctrine()->getManager()->remove($downtime);
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('notice', 'Downtime eliminato con successo');

        return $this->redirectToRoute('downtime-index');
    }

    /**
     * @Route("/downtime/view/{type}/{dtid}", defaults={"dtid"=null}, name="downtime-view")
     * @ParamConverter("downtime", options={"id" = "dtid"})
     *
     * @param string $type
     * @param Downtime $downtime
     *
     * @return Response
     */
    public function view(string $type, Downtime $downtime = null)
    {
        if (null === $downtime) {
            $downtime = new Downtime();
        }

        $downtime->setType($type);

        $form = $this->createForm(DowntimeAdd::class, $downtime);

        return $this->render('downtime/view.html.twig', [
            'downtime' => $form->createView(),
            'dtid' => $downtime ? $downtime->getId() : null,
        ]);
    }

    /**
     * @Route("/downtime/view-no-edit/{type}/{dtid}", defaults={"dtid"=null}, name="downtime-view-noedit")
     * @ParamConverter("downtime", options={"id" = "dtid"})
     *
     * @param string $type
     * @param Downtime $downtime
     *
     * @return Response
     */
    public function viewNoEdit(string $type, Downtime $downtime = null)
    {
        if (null === $downtime) {
            $downtime = new Downtime();
        }

        $downtime->setType($type);

        return $this->render('downtime/view-no-edit.html.twig', [
            'downtime' => $downtime,
        ]);
    }

    /**
     * @Route("/downtime/resolution-no-edit/{type}/{dtid}", defaults={"dtid"=null}, name="downtime-resolution-noedit")
     * @ParamConverter("downtime", options={"id" = "dtid"})
     *
     * @param string $type
     * @param Downtime $downtime
     *
     * @return Response
     */
    public function resolutionNoEdit(string $type, Downtime $downtime = null)
    {
        if (null === $downtime) {
            $downtime = new Downtime();
        }

        $downtime->setType($type);

        return $this->render('downtime/resolution-no-edit.html.twig', [
            'downtime' => $downtime,
        ]);
    }

    /**
     * @Route("/downtime/resolve/{dtid}", defaults={"dtid"=null}, name="downtime-resolve")
     * @ParamConverter("downtime", options={"id" = "dtid"})
     *
     * @param Downtime $downtime
     *
     * @return Response
     */
    public function resolve(Downtime $downtime = null)
    {
        $form = $this->createForm(DowntimeResolve::class, $downtime);
        $formAdd = $this->createForm(DowntimeCommentsAdd::class);

        return $this->render('downtime/resolve.html.twig', [
            'downtime' => $downtime,
            'downtimeForm' => $form->createView(),
            'downtimeCommentsForm' => $formAdd->createView(),
        ]);
    }

    /**
     * @Route("/downtime/resolve-do/{dtid}", name="downtime-resolve-do")
     * @ParamConverter("downtime", options={"id" = "dtid"})
     *
     * @param Request $request
     * @param Downtime $downtime
     * @param NotificationsSystem $notificationSystem
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function resolveDo(
        Request $request,
        Downtime $downtime,
        NotificationsSystem $notificationSystem)
    {
        $form = $this->createForm(DowntimeResolve::class, $downtime);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $downtime->setResolvedAt(new \DateTime());
            $this->getDoctrine()->getEntityManager()->flush();

            $notificationSystem->downtimeResolved($downtime->getCharacter(), $downtime);

            $this->addFlash('notice', 'Downtime risolto con successo');
        }

        return $this->redirectToRoute('downtime-index');
    }

    /**
     * @Route("/downtime/comments-add/{dtid}", name="downtime-comments-add")
     * @ParamConverter("downtime", options={"id" = "dtid"})
     *
     * @param Request $request
     * @param Downtime $downtime
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function commentsAdd(
        Request $request,
        Downtime $downtime)
    {
        $downtimeComment = new DowntimeComment();

        $form = $this->createForm(DowntimeCommentsAdd::class, $downtimeComment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $downtimeComment->setAuthor($this->getUser());
            $downtimeComment->setDowntime($downtime);

            $this->getDoctrine()->getEntityManager()->persist($downtimeComment);
            $this->getDoctrine()->getEntityManager()->flush();

            $this->addFlash('notice', 'Commento aggiunto con successo');
        }

        return $this->redirectToRoute('downtime-index');
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 19/05/2018
 * Time: 20:08.
 */

namespace App\Controller;

use App\Entity\Board;
use App\Form\BoardCreate;
use App\Repository\BoardRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\NoCharacterException;

class BoardController extends Controller
{
    /**
     * @Route("/bacheca", name="board-index")
     *
     * @param UserRepository $userRepository
     * @param BoardRepository $boardRepository
     * @return Response
     *
     * @throws NoCharacterException
     */
    public function index(UserRepository $userRepository, BoardRepository $boardRepository)
    {
        $character = $this->getUser()->getCharacters()[0];
        if (!$this->isGranted('ROLE_STORY_TELLER') && null === $character) {
            throw new NoCharacterException();
        }

        $user = $userRepository->findByRole('ROLE_TRIBUNUS');
        $user = array_pop($user);
        $tribunus = null;
        if ($user && null !== $user->getCharacters()[0]) {
            $tribunus = $user->getCharacters()[0];
        }

        return $this->render('board/index.html.twig', [
            'edicts' => $boardRepository->getAll(),
            'tribunus' => $tribunus,
        ]);
    }

    /**
     * @Route("/bacheca/view/{boardId}", name="board-view")
     * @ParamConverter("board", options={"id" = "boardId"})
     *
     * @param Board $board
     *
     * @return Response
     */
    public function view(Board $board)
    {
        return $this->render('board/view.html.twig', [
            'edict' => $board,
            'rawText' => nl2br($board->getText()),
        ]);
    }

    /**
     * @Route("/bacheca/edit/{boardId}", name="board-edit", defaults={"boardId"=null})
     * @ParamConverter("board", options={"id" = "boardId"})
     *
     * @param Request $request
     * @param BoardRepository $boardRepository
     * @param Board $board
     *
     * @return Response
     */
    public function create(Request $request, BoardRepository $boardRepository, Board $board = null)
    {
        if (null === $board) {
            $board = new Board();
        }
        $form = $this->createForm(BoardCreate::class, $board);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('ROLE_STORY_TELLER')) {
                $author = $this->getUser()->getCharacters()->current();
                $board->setAuthor($author);
            }

            $this->getDoctrine()->getManager()->persist($board);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('notice', 'Editto creato con successo');

            return $this->redirectToRoute('board-index');
        }

        return $this->render('board/create.html.twig', [
            'edicts' => $boardRepository->getAll(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/bacheca/veto/{boardId}", name="board-veto")
     * @ParamConverter("board", options={"id" = "boardId"})
     *
     * @param Board $board
     *
     * @return Response
     */
    public function veto(Board $board)
    {
        $board->setHasVeto(!$board->isHasVeto());
        $board->setVetoAuthor($this->getUser());

        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('notice', 'Veto imposto con successo');

        return $this->redirectToRoute('board-index');
    }

    /**
     * @Route("/bacheca/remove/{boardId}", name="board-remove")
     * @ParamConverter("board", options={"id" = "boardId"})
     *
     * @param Board $board
     *
     * @return Response
     */
    public function delete(Board $board)
    {
        $this->getDoctrine()->getManager()->remove($board);
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('notice', 'Editto cancellato con successo');

        return $this->redirectToRoute('board-index');
    }
}

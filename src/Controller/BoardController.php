<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 19/05/2018
 * Time: 20:08
 */

namespace App\Controller;


use App\Entity\Board;
use App\Form\BoardCreate;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\NoCharacterException;

class BoardController extends Controller
{
    /**
     * @Route("/bacheca", name="board-index")
     */
    public function index()
    {
        $character = $this->getUser()->getCharacters()[0];
        if (!$this->isGranted('ROLE_STORY_TELLER') && null === $character) {
            throw new NoCharacterException();
        }
        
        return $this->render('board/index.html.twig', [
            'edicts' => $this->getDoctrine()->getRepository(Board::class)->getAll(),
        ]);
    }

    /**
     * @Route("/bacheca/view/{boardId}", name="board-view")
     */
    public function view(int $boardId)
    {
        $board = $this->getDoctrine()->getManager()->getRepository(Board::class)->find($boardId);

        return $this->render('board/view.html.twig', [
            'edict' => $board,
            'rawText' => nl2br($board->getText())
        ]);
    }

    /**
     * @Route("/bacheca/edit/{boardId}", name="board-edit")
     */
    public function create(Request $request, int $boardId = null)
    {
        if ($boardId === null) {
            $board = new Board();
        } else {
            $board = $this->getDoctrine()->getManager()->getRepository(Board::class)->find($boardId);
        }
        $form = $this->createForm(BoardCreate::class, $board);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            if (!$this->isGranted('ROLE_STORY_TELLER')){
                $author = $this->getUser()->getCharacters()->current();
                $board->setAuthor($author);
            }

            $this->getDoctrine()->getManager()->persist($board);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('board-index');
        }

        return $this->render('board/create.html.twig', [
            'edicts' => $this->getDoctrine()->getRepository(Board::class)->getAll(),
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/bacheca/veto/{boardId}", name="board-veto")
     */
    public function veto(int $boardId)
    {
        /** @var Board $board */
        $board = $this->getDoctrine()->getRepository(Board::class)->find($boardId);

        $board->setHasVeto(!$board->isHasVeto());
        $board->setVetoAuthor($this->getUser());

        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('board-index');
    }

    /**
     * @Route("/bacheca/remove/{boardId}", name="board-remove")
     */
    public function delete(int $boardId)
    {
        $board = $this->getDoctrine()->getRepository(Board::class)->find($boardId);

        $this->getDoctrine()->getManager()->remove($board);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('board-index');
    }
}
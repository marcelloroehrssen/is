<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 15/05/2018
 * Time: 01:12.
 */

namespace App\Controller;

use App\Entity\Character;
use App\Entity\Message;
use App\Form\MessageCreate;
use App\Form\ValueObject\MessageVo;
use App\Repository\CharacterRepository;
use App\Repository\MessageRepository;
use App\Utils\MessageSystem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LetterController extends AbstractController
{
    /**
     * @Route("/choose-messenger", name="choose-messenger")
     */
    public function chooseType()
    {
        return $this->render('letter/choose.html.twig');
    }

    /**
     * @Route("/letter", name="letter")
     *
     * @param MessageSystem $messageSystem
     * @param MessageRepository $messageRepository
     *
     * @return Response
     */
    public function letter(MessageSystem $messageSystem, MessageRepository $messageRepository)
    {
        if ($this->isGranted('ROLE_STORY_TELLER')) {
            return $this->render('letter/letters-admin.html.twig', [
                'letters' => $messageRepository->getAllLettersForAdminQuery(),
            ]);
        } else {
            $userCharacter = $this->getUser()->getCharacters()[0];
            $interactedUsers = $messageSystem->getAllChat($userCharacter, true);

            return $this->render('letter/letters.html.twig', [
                'letters' => $interactedUsers,
                'chats' => array_combine(
                    array_map(function ($interactedUser) {
                        return $interactedUser->getId();
                    }, $interactedUsers),
                    array_map(function ($interactedUser) use ($userCharacter, $messageSystem) {
                        return $messageSystem->getChat($userCharacter, $interactedUser, true);
                    }, $interactedUsers)
                ),
                'delivering' => $messageRepository->getDeliveringLetters($userCharacter),
            ]);
        }
    }

    /**
     * @Route("/letter/read/{cid}", name="letter-read")
     * @ParamConverter("board", options={"id" = "cid"})
     *
     * @param Character $character
     * @param MessageSystem $messageSystem
     *
     * @return Response
     */
    public function letterRead(Character $character, MessageSystem $messageSystem)
    {
        $userCharacter = $this->getUser()->getCharacters()[0];

        return $this->render('letter/letter-read.html.twig', [
            'letters' => $messageSystem->getChat($userCharacter, $character, true),
        ]);
    }

    /**
     * @Route("/letter/read/admin/{cid1}-{cid2}", name="letter-read-admin", defaults={"cid2"=null})
     *
     * @param int $cid1
     * @param int $cid2
     * @param MessageSystem $messageSystem
     * @param MessageRepository $messageRepository
     * @param CharacterRepository $characterRepository
     *
     * @return Response
     */
    public function letterReadAdmin(
        MessageSystem $messageSystem,
        MessageRepository $messageRepository,
        CharacterRepository $characterRepository,
        int $cid1,
        int $cid2 = null)
    {
        if (null === $cid2) {
            $letters = [$messageRepository->find($cid1)];
        } else {
            /** @var Character $character1 */
            $character1 = $characterRepository->find($cid1);
            /** @var Character $character2 */
            $character2 = $characterRepository->find($cid2);
            $letters = $messageSystem->getChat($character1, $character2, true, true);
        }

        return $this->render('letter/letter-read.html.twig', [
            'letters' => $letters,
        ]);
    }

    /**
     * @Route("/letter/delete/admin/{lid}", name="letter-delete-admin")
     * @ParamConverter("letter", options={"id" = "lid"})
     *
     * @param Message $letter
     *
     * @return Response
     */
    public function letterDeleteAdmin(Message $letter)
    {
        if (Character::TYPE_PNG !== $letter->getSender()->getType()) {
            $this->addFlash('notice', 'Puoi cancellare lettere dei PNG');
        }
        $this->getDoctrine()->getEntityManager()->remove($letter);
        $this->getDoctrine()->getEntityManager()->flush();

        return $this->redirectToRoute('letter');
    }

    /**
     * @Route("/letter/send", name="letter-send")
     *
     * @param Request $request
     * @param MessageSystem $messageSystem
     *
     * @return Response
     */
    public function letterSend(Request $request, MessageSystem $messageSystem)
    {
        $messageVo = new MessageVo();
        if (!$this->isGranted('ROLE_STORY_TELLER')) {
            $messageVo->setSender($this->getUser()->getCharacters()[0]);
        }

        $form = $this->createForm(MessageCreate::class, $messageVo, [
            'character' => $this->getUser()->getCharacters()[0],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($messageVo->getSender()->getId() === $messageVo->getRecipient()->getId()) {
                $this->addFlash('notice', 'Mittente e destinatario devono essere diversi');

                return $this->redirectToRoute('letter');
            }

            $messageSystem->sendMessage(
                $messageVo->getSender(),
                $messageVo->getRecipient(),
                $messageVo->getText(),
                true
            );
            $this->addFlash('notice', 'Lettera spedita con successo');

            return $this->redirectToRoute('letter');
        }

        return $this->render('letter/letter-write.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

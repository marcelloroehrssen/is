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
use App\Form\LetterCreate;
use App\Form\ValueObject\LetterVo;
use App\Repository\CharacterRepository;
use App\Repository\MessageRepository;
use App\Utils\ConnectionSystem;
use App\Utils\MessageSystem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\NoCharacterException;
use Exception;

class MessengerController extends AbstractController
{
    /**
     * @Route("/choose-messenger", name="choose-messenger")
     */
    public function chooseType()
    {
        return $this->render('messenger/choose.html.twig');
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
            return $this->render('messenger/letters-admin.html.twig', [
                'letters' => $messageRepository->getAllLettersForAdminQuery(),
            ]);
        } else {
            $userCharacter = $this->getUser()->getCharacters()[0];
            $interactedUsers = $messageSystem->getAllChat($userCharacter, true);

            return $this->render('messenger/letters.html.twig', [
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

        return $this->render('messenger/letter-read.html.twig', [
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

        return $this->render('messenger/letter-read.html.twig', [
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
        $letterVo = new LetterVo();

        if (!$this->isGranted('ROLE_STORY_TELLER')) {
            $letterVo->setSender($this->getUser()->getCharacters()[0]);
        }

        $form = $this->createForm(LetterCreate::class, $letterVo, [
            'character' => $this->getUser()->getCharacters()[0],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($letterVo->getSender()->getId() === $letterVo->getRecipient()->getId()) {
                $this->addFlash('notice', 'Mittente e destinatario devono essere diversi');

                return $this->redirectToRoute('letter');
            }

            $messageSystem->sendMessage(
                $letterVo->getSender(),
                $letterVo->getRecipient(),
                $letterVo->getText(),
                true
            );
            $this->addFlash('notice', 'Lettera spedita con successo');

            return $this->redirectToRoute('letter');
        }

        return $this->render('messenger/letter-write.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/messenger", name="messenger")
     *
     * @param Request $request
     * @param MessageSystem $messageSystem
     * @param CharacterRepository $characterRepository
     *
     * @return Response
     *
     * @throws NoCharacterException
     */
    public function index(
        Request $request,
        MessageSystem $messageSystem,
        CharacterRepository $characterRepository)
    {
        $chat = [];
        $pngId = $request->query->getInt('png-id', false);

        if ($this->isGranted('ROLE_STORY_TELLER')) {
            if ($pngId) {
                /** @var Character $userCharacter */
                $userCharacter = $characterRepository->find($pngId);
                $chat = $messageSystem->getAllChat($userCharacter, false, true);
            } else {
                $characters = $characterRepository->findAll();

                return $this->render('messenger/admin.html.twig', [
                    'pgs' => $characters,
                    'characters' => $characters,
                    'chats' => array_combine(
                        array_map(function ($character) {
                            return $character->getId();
                        }, $characters),
                        array_map(function ($character) use ($messageSystem) {
                            return $messageSystem->getLastInteraction($this->getUser(), $character);
                        }, $characters)
                    ),
                ]);
            }
        }
        $userCharacter = null;
        if (!$this->isGranted('ROLE_STORY_TELLER')) {
            $userCharacter = $this->getUser()->getCharacters()[0];

            if (null === $userCharacter) {
                throw new NoCharacterException();
            }

            $chat = $messageSystem->getAllChat($userCharacter);
        }

        $png = $pngId ? $userCharacter : null;

        return $this->render('messenger/index.html.twig', [
            'recipient' => null,
            'messages' => [],
            'chat' => $chat,
            'enabled_search' =>
                ($this->isGranted('ROLE_STORY_TELLER') && $pngId)
                || !$this->isGranted('ROLE_STORY_TELLER'),
            'png' => $png,
        ]);
    }

    /**
     * @Route("/messenger/{characterName}", name="messenger_chat")
     * @ParamConverter("character", options={"mapping": {"characterName": "characterNameKeyUrl"}})
     *
     * @param Request $request
     * @param Character $character
     * @param MessageSystem $messageSystem
     * @param ConnectionSystem $connectionSystem
     * @param CharacterRepository $characterRepository
     *
     * @return Response
     *
     * @throws Exception
     */
    public function chat(
        Request $request,
        Character $character,
        MessageSystem $messageSystem,
        ConnectionSystem $connectionSystem,
        CharacterRepository $characterRepository)
    {
        $pngId = $request->query->getInt('png-id', false);

        if ($this->isGranted('ROLE_STORY_TELLER') && $pngId) {
            /** @var Character $userCharacter */
            $userCharacter = $characterRepository->find($pngId);
        } else {
            /** @var Character $userCharacter */
            $userCharacter = $this->getUser()->getCharacters()->current();
        }

        $png = $pngId ? $userCharacter : null;

        $messages = $messageSystem->getChat($userCharacter, $character);
        $chat = $messageSystem->getAllChat($userCharacter);

        return $this->render('messenger/index.html.twig', [
            'user_character' => $userCharacter,
            'recipient' => $character,
            'messages' => $messages,
            'chat' => $chat ?? [],
            'enabled_search' => true,
            'png' => $png,
            'areConnected' => $connectionSystem->areConnected($userCharacter, $character),
        ]);
    }

    /**
     * @Route("/messenger/{characterName}/send", name="messenger_send")
     * @ParamConverter("character", options={"mapping": {"characterName": "characterNameKeyUrl"}})
     *
     * @param Request $request
     * @param Character $character
     * @param CharacterRepository $characterRepository
     * @param MessageSystem $messageSystem
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function send(
        Request $request,
        Character $character,
        CharacterRepository $characterRepository,
        MessageSystem $messageSystem)
    {
        $sender = null;
        $pngId = $request->query->getInt('png-id', false);
        if ($this->isGranted('ROLE_STORY_TELLER') && $pngId) {
            /** @var Character $sender */
            $sender = $characterRepository->find($pngId);
        }
        if (!$this->isGranted('ROLE_STORY_TELLER')) {
            /** @var Character $sender */
            $sender = $this->getUser()->getCharacters()->current();
        }

        $messageSystem->sendMessage(
            $sender,
            $character,
            $request->request->get('message'),
            $request->request->getBoolean('isLetter'),
            $request->request->getBoolean('isPrivate'),
            $request->request->getBoolean('isAnonymous'),
            $request->request->getBoolean('isEncoded')
        );

        date_default_timezone_set('Europe/Rome');

        return new JsonResponse([
            'date' => (new \DateTime())->format('j F Y, H:i:s'),
        ]);
    }
}

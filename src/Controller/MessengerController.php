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
     * @Route("/messenger", name="messenger")
     *
     * @param MessageSystem $messageSystem
     * @param MessageRepository $messageRepository
     *
     * @return Response
     *
     * @throws NoCharacterException
     */
    public function index(
        MessageSystem $messageSystem,
        MessageRepository $messageRepository)
    {
        $chat = [];

        if ($this->isGranted('ROLE_STORY_TELLER')) {
            return $this->render('messenger/chat-admin.html.twig', [
                'messages' => $messageRepository->getAllChatForAdminQuery(),
            ]);
        }
        $userCharacter = null;
        if (!$this->isGranted('ROLE_STORY_TELLER')) {
            $userCharacter = $this->getUser()->getCharacters()[0];

            if (null === $userCharacter) {
                throw new NoCharacterException();
            }

            $chat = $messageSystem->getAllChat($userCharacter);
        }

        return $this->render('messenger/index.html.twig', [
            'recipient' => null,
            'messages' => [],
            'chat' => $chat,
            'enabled_search' => !$this->isGranted('ROLE_STORY_TELLER'),
        ]);
    }

    /**
     * @Route("/messenger/read/admin/{cid1}-{cid2}", name="messenger-read-admin", defaults={"cid2"=null})
     *
     * @param int $cid1
     * @param int $cid2
     * @param MessageSystem $messageSystem
     * @param MessageRepository $messageRepository
     * @param CharacterRepository $characterRepository
     *
     * @return Response
     */
    public function messageReadAdmin(
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
            $letters = $messageSystem->getChat($character1, $character2, false, true);
        }

        return $this->render('messenger/messenger-read.html.twig', [
            'letters' => $letters,
        ]);
    }

    /**
     * @Route("/messenger/delete/admin/{mid}", name="messenger-delete-admin")
     * @ParamConverter("message", options={"id" = "mid"})
     *
     * @param Message $message
     *
     * @return Response
     */
    public function messageDeleteAdmin(Message $message)
    {
        if (Character::TYPE_PNG !== $message->getSender()->getType()) {
            $this->addFlash('notice', 'Puoi cancellare messaggi dei PNG');
        }
        $this->getDoctrine()->getEntityManager()->remove($message);
        $this->getDoctrine()->getEntityManager()->flush();

        return $this->redirectToRoute('messenger');
    }

    /**
     * @Route("/messenger/send", name="messenger_send")
     *
     * @param Request $request
     * @param MessageSystem $messageSystem
     *
     * @return Response
     */
    public function send(
        Request $request,
        MessageSystem $messageSystem)
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

                return $this->redirectToRoute('messenger');
            }

            $messageSystem->sendMessage(
                $messageVo->getSender(),
                $messageVo->getRecipient(),
                $messageVo->getText(),
                false
            );
            $this->addFlash('notice', 'Messaggio spedito con successo');

            return $this->redirectToRoute('messenger');
        }

        return $this->render('messenger/messenger-write.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/messenger/{characterName}/send", name="messenger_send_player_to_player")
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
    public function sendPlayerToPlayer(
        Request $request,
        Character $character,
        CharacterRepository $characterRepository,
        MessageSystem $messageSystem)
    {
        /** @var Character $sender */
        $sender = $this->getUser()->getCharacters()->current();

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

    /**
     * @Route("/messenger/{characterName}", name="messenger_chat")
     * @ParamConverter("character", options={"mapping": {"characterName": "characterNameKeyUrl"}})
     *
     * @param Character $character
     * @param MessageSystem $messageSystem
     * @param ConnectionSystem $connectionSystem
     *
     * @return Response
     *
     * @throws Exception
     */
    public function chat(
        Character $character,
        MessageSystem $messageSystem,
        ConnectionSystem $connectionSystem)
    {
        /** @var Character $userCharacter */
        $userCharacter = $this->getUser()->getCharacters()->current();

        $messages = $messageSystem->getChat($userCharacter, $character);
        $chat = $messageSystem->getAllChat($userCharacter);

        return $this->render('messenger/index.html.twig', [
            'user_character' => $userCharacter,
            'recipient' => $character,
            'messages' => $messages,
            'chat' => $chat ?? [],
            'enabled_search' => true,
            'areConnected' => $connectionSystem->areConnected($userCharacter, $character),
        ]);
    }
}

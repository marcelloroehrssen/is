<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 15/05/2018
 * Time: 01:12
 */

namespace App\Controller;

use App\Entity\Character;
use App\Entity\Message;
use App\Form\LetterCreate;
use App\Form\ValueObject\LetterVo;
use App\Utils\ConnectionSystem;
use App\Utils\MessageSystem;
use App\Utils\NotificationsSystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\NoCharacterException;

class MessengerController extends Controller
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
     */
    public function letter(MessageSystem $messageSystem)
    {
        if ($this->isGranted('ROLE_STORY_TELLER')) {
            $letters = $this->getDoctrine()->getRepository(Character::class)->findAll();

            return $this->render('messenger/letters.html.twig', [
                'letters' => $letters,
                'chats' => array_combine(
                    array_map(function($letter) {
                        return $letter->getId();
                    }, $letters),
                    array_map(function($letter) use ($messageSystem) {
                        $chats = $messageSystem->getAllChat($letter, true);
                        foreach ($chats as $chat) {

                        }
                    }, $letters)
                )
            ]);

        } else {
            $userCharacter = $this->getUser()->getCharacters()[0];
            $letters = $messageSystem->getAllChat($userCharacter, true);

            return $this->render('messenger/letters.html.twig', [
                'letters' => $letters,
                'chats' => array_combine(
                    array_map(function($letter) {
                        return $letter->getId();
                    }, $letters),
                    array_map(function($letter) use ($userCharacter, $messageSystem) {
                        return $messageSystem->getChat($userCharacter, $letter, true);
                    }, $letters)
                )
            ]);
        }
    }

    /**
     * @Route("/letter/read/{lid}", name="letter-read")
     */
    public function letterRead($lid)
    {
        return $this->render('messenger/letter-read.html.twig', [
            'letter' => $this->getDoctrine()->getRepository(Message::class)->find($lid)
        ]);
    }

    /**
     * @Route("/letter/send", name="letter-send")
     */
    public function letterSend(Request $request, MessageSystem $messageSystem)
    {
        $letterVo = new LetterVo();

        if (!$this->isGranted('ROLE_STORY_TELLER')) {
            $letterVo->setSender($this->getUser()->getCharacters()[0]);
        }

        $form = $this->createForm(LetterCreate::class, $letterVo);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($letterVo->getRecipient()->getId() === $this->getUser()->getCharacters()[0]->getId()) {
                $this->addFlash('notice','Non Puoi inviare una lettera a te stesso');
                return $this->redirectToRoute('letter');
            }
            dump($letterVo->getRecipient()->getId());
            dump($this->getUser()->getCharacters()[0]);

            $messageSystem->sendMessage(
                $letterVo->getSender(),
                $letterVo->getRecipient(),
                $letterVo->getText(),
                true
            );
            $this->addFlash('notice','Lettera spedita con successo');

            return $this->redirectToRoute('letter');
        }
        return $this->render('messenger/letter-write.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/messenger", name="messenger")
     */
    public function index(Request $request, MessageSystem $messageSystem)
    {
        $chat = [];

        $pngId = $request->query->getInt('png-id', false);

        if ($this->isGranted('ROLE_STORY_TELLER')) {
            if ($pngId) {
                $userCharacter = $this->getDoctrine()->getRepository(Character::class)->find($pngId);
                $chat = $messageSystem->getAllChat($userCharacter);
            } else {

                $characters = $this->getDoctrine()->getRepository(Character::class)->findAll();
                
                return $this->render('messenger/admin.html.twig', [
                    'pgs' => $characters,
                    'characters' => $characters,
                    'chats' =>array_combine(
                        array_map(function ($character) {
                            return $character->getId();
                        }, $characters),
                        array_map(function ($character) use ($messageSystem) {
                            return $messageSystem->getLastInteraction($this->getUser(), $character);
                        }, $characters)
                    )
                ]);
            }
        }
        if (!$this->isGranted('ROLE_STORY_TELLER')) {
            $userCharacter = $this->getUser()->getCharacters()[0];
            
            if (null === $userCharacter) {
                throw new NoCharacterException();
            }
            
            $chat = $messageSystem->getAllChat($userCharacter);
        }

        $png = null;
        if ($pngId) {
            $png = $userCharacter;
        }

        return $this->render('messenger/index.html.twig', [
            'recipient' => null,
            'messages' => [],
            'chat' => $chat,
            'enabled_search' => ($this->isGranted('ROLE_STORY_TELLER') && $pngId) || !$this->isGranted('ROLE_STORY_TELLER'),
            'png' => $png
        ]);
    }

    /**
     * @Route("/messenger/{characterName}", name="messenger_chat")
     */
    public function chat(Request $request, $characterName, MessageSystem $messageSystem, ConnectionSystem $connectionSystem)
    {
        /** @var Character $character */
        $character = null ?? $this->getDoctrine()->getRepository(Character::class)->findByKeyUrl($characterName)[0];
        if (empty($character)) {
            return $this->createNotFoundException(sprintf("Utente %s non trovato", $characterName));
        }
        
        $pngId = $request->query->getInt('png-id', false);

        if ($this->isGranted('ROLE_STORY_TELLER') && $pngId) {
            /** @var Character $userCharacter */
            $userCharacter = $this->getDoctrine()->getRepository(Character::class)->find($pngId);
        } else {
            /** @var Character $userCharacter */
            $userCharacter = $this->getUser()->getCharacters()->current();
        }

        $png = null;
        if ($pngId) {
            $png = $userCharacter;
        }

        $messages = $messageSystem->getChat(
            $userCharacter,
            $character
        );

        $chat = $messageSystem->getAllChat($userCharacter);

        return $this->render('messenger/index.html.twig', [
            'user_character' => $userCharacter,
            'recipient' => $character,
            'messages' => $messages,
            'chat' => $chat ?? [],
            'enabled_search' => true,
            'png' => $png,
            'areConnected' => $connectionSystem->areConnected($userCharacter, $character)
        ]);
    }

    /**
     * @Route("/messenger/{characterName}/send", name="messenger_send")
     */
    public function send(Request $request, $characterName, MessageSystem $messageSystem)
    {
        $character = $this->getDoctrine()->getRepository(Character::class)->findByKeyUrl($characterName)[0] ?? null;

        $pngId = $request->query->getInt('png-id', false);
        if ($this->isGranted('ROLE_STORY_TELLER') && $pngId) {
            $sender = $this->getDoctrine()->getRepository(Character::class)->find($pngId);
        }
        if (!$this->isGranted('ROLE_STORY_TELLER')) {
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

        date_default_timezone_set( 'Europe/Rome' );
        return new JsonResponse([
            'date' => (new \DateTime())->format('j F Y, H:i:s')
        ]);
    }


}
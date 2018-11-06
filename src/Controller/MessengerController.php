<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 15/05/2018
 * Time: 01:12
 */

namespace App\Controller;

use App\Entity\Character;
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
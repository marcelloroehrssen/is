<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 15/05/2018
 * Time: 01:12
 */

namespace App\Controller;

use App\Entity\Character;
use App\Utils\MessageSystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MessengerController extends Controller
{
    /**
     * @Route("/messenger", name="messenger")
     */
    public function index()
    {
        return $this->render('messenger/index.html.twig', [
        ]);
    }

    /**
     * @Route("/messenger/{characterName}", name="messenger_chat")
     */
    public function chat($characterName, MessageSystem $messageSystem)
    {
        $character = $this->getDoctrine()->getRepository(Character::class)->findByKeyUrl($characterName)[0] ?? null;
        if (empty($character)) {
            return $this->createNotFoundException(sprintf("Utente %s non trovato", $characterName));
        }

        $userCharacter = $this->getUser()->getCharacters()->current();

        $messages = $messageSystem->getChat(
            $this->getUser()->getCharacters()->current(),
            $character
        );

        $chat = $messageSystem->getAllChat($userCharacter);

        return $this->render('messenger/index.html.twig', [
            'recipient' => $character,
            'messages' => $messages,
            'chat' => $chat
        ]);
    }

    /**
     * @Route("/messenger/{characterName}/send", name="messenger_send")
     */
    public function send(Request $request, $characterName, MessageSystem $messageSystem)
    {
        $character = $this->getDoctrine()->getRepository(Character::class)->findByKeyUrl($characterName)[0] ?? null;

        $messageSystem->sendMessage(
            $this->getUser()->getCharacters()->current(),
            $character,
            $request->request->getAlpha('message'),
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
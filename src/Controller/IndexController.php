<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\User;

class IndexController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        $user = $this->getDoctrine()->getManager()->getRepository(User::class)->findByRole('ROLE_CENSOR');

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'censorMessagePath' => $this->generateUrl('messenger_chat', [
                'characterName' => array_pop($user)->getCharacters()[0]->getCharacterNameKeyUrl(),
            ]),
        ]);
    }
}

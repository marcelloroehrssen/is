<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     *
     * @param UserRepository $userRepository
     *
     * @return Response
     */
    public function index(UserRepository $userRepository)
    {
        $user = $userRepository->findByRole('ROLE_CENSOR');

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'censorMessagePath' => $this->generateUrl('messenger_chat', [
                'characterName' => array_pop($user)->getCharacters()[0]->getCharacterNameKeyUrl(),
            ]),
        ]);
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 15/05/2018
 * Time: 01:12
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
}
<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 14/05/2018
 * Time: 17:54
 */

namespace App\Controller;

use App\Entity\Notifications;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class NotificationsController extends Controller
{

    public function notifications()
    {
        $notifications = $this->getDoctrine()->getRepository(Notifications::class)->getNotifications($this->getUser()->getId());

        return $this->render('notifications/notifications.html.twig', [
            'notifications' => $notifications
        ]);
    }

    /**
     * @Route("/notifications/read", name="notifications-read")
     */
    public function readAll()
    {
        $userId = $this->getUser()->getId();
        $this->getDoctrine()->getRepository(Notifications::class)->readAll($userId);
        return new JsonResponse([]);
    }
}
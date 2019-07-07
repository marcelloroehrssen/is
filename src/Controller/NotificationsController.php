<?php

namespace App\Controller;

use App\Entity\Notifications;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class NotificationsController extends AbstractController
{
    public function notifications()
    {
        $notifications = [];
        if (null !== $this->getUser()) {
            $notifications = $this->getDoctrine()->getRepository(Notifications::class)->getNotifications($this->getUser()->getId());
        }

        return $this->render('notifications/notifications.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * @Route("/notifications/read", name="notifications-read")
     */
    public function readAll()
    {
        if (null !== $this->getUser()) {
            $userId = $this->getUser()->getId();
            $this->getDoctrine()->getRepository(Notifications::class)->readAll($userId);
        }

        return new JsonResponse([]);
    }
}

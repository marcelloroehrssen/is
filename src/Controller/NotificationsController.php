<?php

namespace App\Controller;

use App\Repository\NotificationsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationsController extends AbstractController
{
    /**
     * @param NotificationsRepository $notificationsRepository
     *
     * @return Response
     */
    public function notifications(NotificationsRepository $notificationsRepository)
    {
        $notifications = [];
        if (null !== $this->getUser()) {
            $notifications = $notificationsRepository->getNotifications($this->getUser()->getId());
        }

        return $this->render('notifications/notifications.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * @Route("/notifications/read", name="notifications-read")
     *
     * @param NotificationsRepository $notificationsRepository
     *
     * @return JsonResponse
     */
    public function readAll(NotificationsRepository $notificationsRepository)
    {
        if (null !== $this->getUser()) {
            $userId = $this->getUser()->getId();
            $notificationsRepository->readAll($userId);
        }

        return new JsonResponse([]);
    }
}

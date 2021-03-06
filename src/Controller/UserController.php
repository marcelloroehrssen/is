<?php

namespace App\Controller;

use App\Utils\SettingsSystem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserUpdate;
use App\Form\ValueObject\UserUpdateVo;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     *
     * @param SettingsSystem $settingsSystem
     *
     * @return Response
     */
    public function index(SettingsSystem $settingsSystem)
    {
        $user = $this->getUser();

        $userVo = new UserUpdateVo();
        $userVo->setUsername($user->getUsername());
        $userVo->setEmail($user->getEmail());

        $userForm = $this->createForm(UserUpdate::class, $userVo);

        return $this->render('user/index.html.twig', [
            'user' => $userForm->createView(),
            'action' => $this->generateUrl('user-update'),
            'settings' => $settingsSystem->load($user)->getSettings(),
        ]);
    }

    /**
     * @Route("/user/update", name="user-update")
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     *
     * @return Response
     */
    public function update(
        Request $request,
        UserPasswordEncoderInterface $encoder)
    {
        $userVo = new UserUpdateVo();

        $userForm = $this->createForm(UserUpdate::class, $userVo);
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $user = $this->getUser();

            if (!empty($userVo->getUsername())) {
                $user->setUsername($userVo->getUsername());
                $encodedPassword = $encoder->encodePassword($user, $userVo->getPassword());
                $user->setPassword($encodedPassword);
            }
            if (!empty($userVo->getEmail())) {
                $user->setEmail($userVo->getEmail());
                $encodedPassword = $encoder->encodePassword($user, $userVo->getPassword());
                $user->setPassword($encodedPassword);
            }

            if (!empty($userVo->getPassword())) {
                $encodedPassword = $encoder->encodePassword($user, $userVo->getPassword());
                $user->setPassword($encodedPassword);
            }

            $this->getDoctrine()->getEntityManager()->flush();

            $this->addFlash('notice', 'Utente aggiornato con successo');
        }

        return $this->redirectToRoute('user');
    }

    /**
     * @Route("/user/no-character", name="no-character")
     *
     * @return Response
     */
    public function noCharacter()
    {
        return $this->render('user/no-character.html.twig', [
        ]);
    }

    /**
     * @Route("/user/set-setting", name="user-set-settings")
     *
     * @param Request $request
     * @param SettingsSystem $settingsSystem
     *
     * @return Response
     */
    public function setSettings(Request $request, SettingsSystem $settingsSystem)
    {
        list('type' => $type, 'value' => $value, 'isChecked' => $isChecked) = $request->request->all();

        $value = (int) $value;
        $isChecked = ('true' === $isChecked);

        $user = $this->getUser();
        $settingsSystem->setSetting($this->getUser(), $type, $value, $isChecked);

        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->json(['status' => 'ok']);
    }
}

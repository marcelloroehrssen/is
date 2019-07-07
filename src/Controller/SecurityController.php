<?php

namespace App\Controller;

use App\Form\UserType;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Utils\ErrorNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Swift_Mailer;

class SecurityController extends AbstractController
{
    /**
     * @Route("/security", name="user_security")
     */
    public function security()
    {
        return $this->render(
            'security/security.html.twig'
        );
    }

    /**
     * @Route("/login", name="user_login")
     *
     * @param Request $request
     * @param AuthenticationUtils $authenticationUtils
     * @param ErrorNormalizer $normalizer
     *
     * @return Response
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils, ErrorNormalizer $normalizer)
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/security.html.twig', [
            'last_username' => $lastUsername,
            'errors' => $normalizer->normalizeInvalidCredential($error),
        ]);
    }

    /**
     * @Route("/register", name="user_register")
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ErrorNormalizer $normalizer
     *
     * @return Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, ErrorNormalizer $normalizer)
    {
        $error = null;

        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($password);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                /**
                 * @todo redirezionare a pagina di messaggio (la stessa?) per l'attesa di attivazione
                 */
                return $this->redirectToRoute('homepage');
            } else {
                $error = $normalizer->normalizeForErrors($form->getErrors(true));
            }
        }

        return $this->render(
            'security/security.html.twig',
            [
                'register_form' => $form->createView(),
                'errors' => $error,
            ]
        );
    }

    /**
     * @Route("/password_forgotten", name="user_forgotten_password")
     *
     * @param Request $request
     * @param Swift_Mailer $mailer
     * @param UserPasswordEncoderInterface $encoder
     * @param UserRepository $userRepository
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function forgottenPassword(
        Request $request,
        Swift_Mailer $mailer,
        UserPasswordEncoderInterface $encoder,
        UserRepository $userRepository)
    {
        $user = $userRepository->findByEmail($request->request->get('email'));

        $password = sprintf('%s%s%s', dechex(rand(1, 255)), dechex(rand(1, 255)), dechex(rand(1, 255)));
        $encodedPassword = $encoder->encodePassword($user, $password);
        $user->setPassword($encodedPassword);

        $this->getDoctrine()->getEntityManager()->flush();

        $mail = new \Swift_Message();
        $mail->setSubject('Ecco la tua nuova password');
        $mail->setFrom(['info@imperiumsanguinis.it' => 'Imperium Sanguinis']);
        $mail->setCharset('utf-8');
        $mail->setTo([$user->getEmail() => $user->getUsername()]);
        $mail->setContentType('text/html');
        $mail->setBody($this->render(
            'mail/base.html.twig',
            [
                'user' => $user,
                'message' => sprintf('Ecco la tua nuova password <strong>%s</strong>', $password),
                'link' => $this->generateUrl('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]
        ), 'text/html');

        $mailer->send($mail);

        return $this->redirectToRoute('user_login');
    }
}

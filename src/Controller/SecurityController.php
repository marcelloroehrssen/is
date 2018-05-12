<?php

namespace App\Controller;

use App\Form\UserType;
use App\Entity\User;
use App\Utils\ErrorNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
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
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils, ErrorNormalizer $normalizer)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/security.html.twig', array(
            'last_username' => $lastUsername,
            'errors'         => $normalizer->normalizeInvalidCredential($error),
        ));
    }

    /**
     * @Route("/register", name="user_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, ErrorNormalizer $normalizer)
    {
        $error = null;
        // 1) build the form
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                // 3) Encode the password (you could also do this via Doctrine listener)
                $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($password);

                // 4) save the User!
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                // ... do any other work - like sending them an email, etc
                // maybe set a "flash" success message for the user

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
            array(
                'register_form' => $form->createView(),
                'errors' => $error
            )
        );
    }
}

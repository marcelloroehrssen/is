<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Form\UserUpdate;
use App\Form\ValueObject\UserUpdateVo;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Route("/user", name="user")
     */
    public function index()
    {
        $user = $this->getUser();
        
        $userVo = new UserUpdateVo();
        $userVo->setUsername($user->getUsername());
        $userVo->setEmail($user->getEmail());
        
        $userForm = $this->createForm(UserUpdate::class, $userVo);
        
        return $this->render('user/index.html.twig', [
            'user' => $userForm->createView(),
            'action' => $this->generateUrl('user-update'),
        ]);
    }
    
    /**
     * @Route("/user/update", name="user-update")
     */
    public function update(Request $request, UserPasswordEncoderInterface $encoder)
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
        }
        
        return $this->redirectToRoute('user');
    }
}
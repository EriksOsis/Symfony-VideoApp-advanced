<?php
/*
|--------------------------------------------------------
| copyright netprogs.pl | available only at Udemy.com | further distribution is prohibited  ***
|--------------------------------------------------------
*/

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use App\Entity\User;
use App\Form\UserType;


class SecurityController extends AbstractController
{

    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $helper)
    {
        return $this->render('front/login.html.twig', [
            'error' => $helper->getLastAuthenticationError()
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }

    #[Route('/register/{plan}', name: 'register', defaults: ['plan' => null])]
    public function register(UserPasswordHasherInterface $password_encoder, Request $request, SessionInterface $session, $plan): Response
    {
        $user = new User;
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->doctrine->getManager();

            $user->setName($request->request->get('user')['name']);
            $user->setLastName($request->request->get('user')['last_name']);
            $user->setEmail($request->request->get('user')['email']);
            $password = $password_encoder->hashPassword($user, $request->request->get('user')['password']['first']);
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->loginUserAutomatically($user);

            return $this->redirectToRoute('admin_main_page');
        }
        return $this->render('front/register.html.twig', ['form' => $form->createView()]);
    }


    private function loginUserAutomatically($user)
    {
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
    }
}

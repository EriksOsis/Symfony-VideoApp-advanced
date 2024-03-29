<?php
/*
|--------------------------------------------------------
| copyright netprogs.pl | available only at Udemy.com | further distribution is prohibited  ***
|--------------------------------------------------------
*/

namespace App\Controller;

use App\Controller\Traits\SaveSubscription;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use App\Entity\Subscription;
use App\Entity\User;
use App\Form\UserType;


class SecurityController extends AbstractController
{
    use SaveSubscription;

    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $helper): Response
    {
        return $this->render('front/login.html.twig', [
            'error' => $helper->getLastAuthenticationError()
        ]);
    }

    /**
     * @throws \Exception
     */
    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }

    #[Route('/register/{plan}', name: 'register', defaults: ['plan' => null])]
    public function register(UserPasswordHasherInterface $password_hasher, Request $request, SessionInterface $session, $plan): RedirectResponse|Response
    {
        if ($request->isMethod('GET')) {
            $session->set('planName', $plan);
            $session->set('planPrice', Subscription::getPlanDataPriceByName($plan));
        }

        $user = new User;
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->doctrine->getManager();

            $user->setName($request->get('user')['name']);
            $user->setLastName($request->get('user')['last_name']);
            $user->setEmail($request->get('user')['email']);
            $password = $password_hasher->hashPassword($user, $request->get('user')['password']['first']);
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);

            $date = new \Datetime();
            $date->modify('+1 month');
            $subscription = new Subscription();
            $subscription->setValidTo($date);
            $subscription->setPlan($session->get('planName'));
            $subscription->setFreePlanUsed(true);
            if($plan == Subscription::getPlanDataNameByIndex(0)) {
                $subscription->setFreePlanUsed(false);
                $subscription->setPaymentStatus('paid');
            }

            $user->setSubscription($subscription);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->loginUserAutomatically($user);

            return $this->redirectToRoute('admin');
        }
        return $this->render('front/register.html.twig', ['form' => $form->createView()]);
    }


    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function loginUserAutomatically($user)
    {
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
    }
}

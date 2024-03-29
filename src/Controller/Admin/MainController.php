<?php

namespace App\Controller\Admin;

use App\Entity\Subscription;
use App\Entity\Video;
use App\Entity\User;
use App\Form\UserType;
use App\Utils\CategoryTreeAdminOptionList;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;


#[Route('/admin')]
class MainController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    #[Route('/', name: 'admin')]
    public function index(Request $request, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user, ['user' => $user]);
        $form->handleRequest($request);
        $isInvalid = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->doctrine->getManager();
            $user->setName($request->get('user')['name']);
            $user->setLastName($request->get('user')['last_name']);
            $user->setEmail($request->get('user')['email']);
            $password = $passwordHasher->hashPassword($user, $request->get('user')['password']['first']);
            $user->setPassword($password);

            $entityManager->persist($user);
            $entityManager->flush();

            $translated = $translator->trans('Your changes were saved!');


            $this->addFlash('success', $translated);
            return $this->redirectToRoute('admin');
        } elseif ($request->isMethod('POST')) {
            $isInvalid = 'is-invalid';
        }

        return $this->render('/front/admin/my_profile.html.twig', [
            'subscription' => $this->getUser()->getSubscription(),
            'form' => $form->createView(),
            'isInvalid' => $isInvalid,
            'user' => $user
        ]);
    }

    #[Route('/videos', name: 'videos')]
    public function videos(CategoryTreeAdminOptionList $categories): Response
    {
        $user = $this->doctrine->getRepository(User::class)->find($this->getUser());
        if ($this->isGranted('ROLE_ADMIN')) {
            $categories->getCategoryList($categories->buildTree());
            $videos = $this->doctrine->getRepository(Video::class)->findBy([], ['title' => 'ASC']);
        } else {
            $categories = null;
            $videos = $this->getUser()->getLikedVideos();
        }
        return $this->render('/front/admin/videos.html.twig', [
            'videos' => $videos,
            'categories' => $categories,
            'user' => $user
        ]);
    }

    #[Route('/cancel-plan', name: 'cancel_plan')]
    public function cancelPlan(): RedirectResponse
    {
        $user = $this->doctrine->getRepository(User::class)->find($this->getUser());

        $subscription = $user->getSubscription();
        $subscription->setValidTo(new \DateTime());
        $subscription->setPaymentStatus(null);
        $subscription->setPlan('canceled');

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->persist($subscription);

        $entityManager->flush();

        return $this->redirectToRoute('admin');
    }

    #[Route('/delete-account/', name: 'delete_account')]
    public function deleteAccount(): RedirectResponse
    {
        $entityManager = $this->doctrine->getManager();
        $user = $entityManager->getRepository(User::class)->find($this->getUser());

        $entityManager->remove($user);
        $entityManager->flush();

        session_destroy();

        return $this->redirectToRoute('main_page');
    }
}
<?php

namespace App\Controller\Admin\SuperAdmin;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SuperAdminController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    #[Route('/su/upload_video', name: 'upload_video')]
    public function upload_videos(): Response
    {
        $user = $this->doctrine->getRepository(User::class)->find($this->getUser());
        return $this->render('/front/admin/upload_video.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/su/users', name: 'users')]
    public function users(): Response
    {
        $user = $this->doctrine->getRepository(User::class)->find($this->getUser());
        $repository = $this->doctrine->getRepository(User::class);
        $users = $repository->findBy([], ['name' => 'DESC']);
        return $this->render('/front/admin/users.html.twig', [
            'users' => $users,
            'user' => $user,
        ]);
    }

    #[Route('/su/delete-user/{user}', name: 'delete_user')]
    public function deleteUser(User $user)
    {
        $manager = $this->doctrine->getManager();
        $manager->remove($user);
        $manager->flush();
    }
}
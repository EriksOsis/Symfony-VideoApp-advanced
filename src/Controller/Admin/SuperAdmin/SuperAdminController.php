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
        return $this->render('/front/admin/upload_video.html.twig');
    }

    #[Route('/su/users', name: 'users')]
    public function users(): Response
    {
        $repository = $this->doctrine->getRepository(User::class);
        $users = $repository->findBy([], ['name' => 'ASC']);
        return $this->render('/front/admin/users.html.twig', [
            'users' => $users
        ]);
    }
}
<?php

namespace App\Controller\Admin\SuperAdmin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SuperAdminController extends AbstractController
{
    #[Route('/su/upload_video', name: 'upload_video')]
    public function upload_videos(): Response
    {
        return $this->render('/front/admin/upload_video.html.twig');
    }

    #[Route('/su/users', name: 'users')]
    public function users(): Response
    {
        return $this->render('/front/admin/users.html.twig');
    }
}
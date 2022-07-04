<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin')]
    public function index(): Response
    {
        return $this->render('/front/admin/my_profile.html.twig');
    }

    #[Route('/categories', name: 'categories')]
    public function cateories(): Response
    {
        return $this->render('/front/admin/categories.html.twig');
    }

    #[Route('/videos', name: 'videos')]
    public function videos(): Response
    {
        return $this->render('/front/admin/videos.html.twig');
    }

    #[Route('/upload_video', name: 'upload_video')]
    public function upload_videos(): Response
    {
        return $this->render('/front/admin/upload_video.html.twig');
    }

    #[Route('/users', name: 'users')]
    public function users(): Response
    {
        return $this->render('/front/admin/users.html.twig');
    }

    #[Route('/edit-category', name: 'edit_category')]
    public function editCategory(): Response
    {
        return $this->render('/front/admin/edit_category.html.twig');
    }
}

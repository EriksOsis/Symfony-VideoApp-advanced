<?php

namespace App\Controller;

use App\Entity\Category;
use App\Utils\CategoryTreeAdminList;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    #[Route('/', name: 'admin')]
    public function index(): Response
    {
        return $this->render('/front/admin/my_profile.html.twig');
    }

    #[Route('/categories', name: 'categories')]
    public function categories(CategoryTreeAdminList $categories): Response
    {
        $categories->getCategoryList($categories->buildTree());
        return $this->render('/front/admin/categories.html.twig',[
            'categories' => $categories->categorylist
        ]);
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

    #[Route('/delete-category/{id}', name: 'delete_category')]
    public function deleteCategory(Category $category): Response
    {
        $entityManager = $this->doctrine->getManager();
        $entityManager->remove($category);
        $entityManager->flush();

        return $this->redirectToRoute('categories');
    }
}

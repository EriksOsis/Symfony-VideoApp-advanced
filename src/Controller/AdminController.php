<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Utils\CategoryTreeAdminList;
use App\Utils\CategoryTreeAdminOptionList;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/su/categories', name: 'categories', methods: ["GET", "POST"])]
    public function categories(CategoryTreeAdminList $categories, Request $request): Response
    {
        $categories->getCategoryList($categories->buildTree());

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $isInvalid = null;
        $form->handleRequest($request);

        if ($this->saveCategory($category, $form, $request)) {

            return $this->redirectToRoute('categories');

        } elseif ($request->isMethod('post')) {

            $isInvalid = ' is-invalid';

        }

        return $this->render('front/admin/categories.html.twig', [
            'categories' => $categories->categorylist,
            'form' => $form->createView(),
            'isInvalid' => $isInvalid
        ]);
    }

    #[Route('/videos', name: 'videos')]
    public function videos(): Response
    {
        return $this->render('/front/admin/videos.html.twig');
    }

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

    #[Route('/su/edit-category/{id}', name: 'edit_category', methods: ["GET", "POST"])]
    public function editCategory(Category $category, Request $request): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $isInvalid = null;

        if ($this->saveCategory($category, $form, $request)) {

            return $this->redirectToRoute('categories');

        } elseif ($request->isMethod('post')) {

            $isInvalid = ' is-invalid';

        }

        return $this->render('/front/admin/edit_category.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
            'isInvalid' => $isInvalid
        ]);
    }

    #[Route('/su/delete-category/{id}', name: 'delete_category')]
    public function deleteCategory(Category $category): Response
    {
        $entityManager = $this->doctrine->getManager();
        $entityManager->remove($category);
        $entityManager->flush();

        return $this->redirectToRoute('categories');
    }

    public function getAllCategories(CategoryTreeAdminOptionList $categories, $editedCategory = null): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $categories->getCategoryList($categories->buildTree());
        return $this->render('front/admin/_all_categories.html.twig', [
            'categories' => $categories,
            'editedCategory' => $editedCategory
        ]);
    }

    private function saveCategory($category, $form, $request): bool
    {
//        $form->handleRequest($request); A form can only be submitted once error

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setName($request->get('category')['name']);

            $repository = $this->doctrine->getRepository(Category::class);
            $parent = $repository->find($request->get('category')['parent']);
            $category->setParent($parent);

            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return true;

        }
        return false;
    }
}

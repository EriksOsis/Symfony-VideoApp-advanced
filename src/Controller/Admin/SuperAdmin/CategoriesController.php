<?php

namespace App\Controller\Admin\SuperAdmin;

use App\Entity\Category;
use App\Entity\User;
use App\Form\CategoryType;
use App\Utils\CategoryTreeAdminList;
use App\Utils\CategoryTreeAdminOptionList;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoriesController extends AbstractController
{

    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    #[Route('/su/categories', name: 'categories', methods: ["GET", "POST"])]
    public function categories(CategoryTreeAdminList $categories, Request $request): Response
    {
        $user = $this->doctrine->getRepository(User::class)->find($this->getUser());
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
            'isInvalid' => $isInvalid,
            'user' => $user
        ]);
    }

    #[Route('/su/edit-category/{id}', name: 'edit_category', methods: ["GET", "POST"])]
    public function editCategory(Category $category, Request $request): Response
    {
        $user = $this->doctrine->getRepository(User::class)->find($this->getUser());
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
            'isInvalid' => $isInvalid,
            'user' => $user
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


    private function saveCategory($category, $form, $request): bool
    {
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
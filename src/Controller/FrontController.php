<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Category;
use App\Entity\Video;
use App\Form\UserType;
use App\Repository\VideoRepository;
use App\Utils\CategoryTreeFrontPage;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class FrontController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    #[Route('/', name: 'main_page')]
    public function index(): Response
    {
        return $this->render('front/index.html.twig');
    }

    #[Route('/video-list/category/{categoryname}, {id}/{page}', name: 'video_list', defaults: ['page' => '1'])]
    public function videoList($id, $page, CategoryTreeFrontPage $categories, Request $request): Response
    {
        $categories->getCategoryListAndParent($id);
        $ids = $categories->getChildsIds($id);
        array_push($ids, $id);
        dump($categories->buildTree($id));
        $videos = $this->doctrine->getRepository(Video::class)->findByChildIds($ids, $page, $request->get('sortby'));
        return $this->render('front/video_list.html.twig', [
            'subcategories' => $categories,
            'videos' => $videos
        ]);
    }

    #[Route('/video-details/{video}', name: 'video_details')]
    public function videoDetails(VideoRepository $repo, $video): Response
    {
        return $this->render('front/video_details.html.twig', [
            'video' => $repo->videoDetails($video),
        ]);
    }

    #[Route('/video-details/{video}/like', name: 'like_video', methods: 'POST')]
    #[Route('/video-details/{video}/dislike', name: 'dislike_video', methods: 'POST')]
    #[Route('/video-details/{video}/unlike', name: 'undo_like_video', methods: 'POST')]
    #[Route('/video-details/{video}/undodislike', name: 'undo_dislike_video', methods: 'POST')]
    public function togglesLikesAjax(Video $video, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        switch ($request->get('_route')) {
            case 'like_video':
                $request = $this->likeVideo($video);
                break;

            case 'dislike_video':
                $request = $this->dislikeVideo($video);
                break;

            case 'undo_like_video':
                $request = $this->undoLikeVideo($video);
                break;

            case 'undo_dislike_video';
                $request = $this->undoDislikeVideo($video);
                break;
        }
        return $this->json(['action' => $result, 'id' => $video->getId()]);
    }

    private function likeVideo($video)
    {
        $user = $this->doctrine->getRepository(User::class)->find($this->getUser());
        $user->addLikedVideo($video);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();
        return 'liked';
    }

    private function dislikeVideo($video)
    {
        $user = $this->doctrine->getRepository(User::class)->find($this->getUser());
        $user->addDislikedVideo($video);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();
        return 'disliked';
    }

    private function undoLikeVideo($video)
    {
        $user = $this->doctrine->getRepository(User::class)->find($this->getUser());
        $user->removeLikedVideo($video);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();
        return 'undo liked';
    }

    private function undoDislikeVideo($video)
    {
        $user = $this->doctrine->getRepository(User::class)->find($this->getUser());
        $user->removeDisikedVideo($video);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();
        return 'undo disliked';
    }

    #[Route('/new/comment/{video}', name: 'new_comment', methods: 'POST')]
    public function newComment(Video $video, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (!empty(trim($request->get('comment')))) {
            $comment = new Comment();
            $comment->setContent($request->get('comment'));
            $comment->setUser($this->getUser()); //errors, bet strādā?
            $comment->setVideo($video);

            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('video_details', ['video' => $video->getId()]);
    }

    // todo VAJAG SAPRAST, KĀPĒC $query paliek null pēc input submit.
    #[Route('/search-results/{page}', name: 'search_results', defaults: ['page' => '1'], methods: 'GET')]
    public function searchResults($page, Request $request): Response
    {
        $videos = null;
        $query = null;

        if ($query = $request->get('query')) {
            $videos = $this->doctrine
                ->getRepository(Video::class)
                ->findByTitle($query, $page, $request->get('sortby'));

//            if (!$videos->getItems()) $videos = null;
        }

        return $this->render('front/search_results.html.twig', [
            'videos' => $videos,
            'query' => $query,
        ]);
    }

    #[Route('/pricing', name: 'pricing')]
    public function pricing(): Response
    {
        return $this->render('front/pricing.html.twig');
    }

    #[Route('/register', name: 'register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User;
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->doctrine->getManager();
            $user->setName($request->get('user')['name']);
            $user->setLastName($request->get('user')['last_name']);
            $user->setEmail($request->get('user')['email']);
            $password = $passwordHasher->hashPassword($user, $request->get('user')['password']['first']);
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->loginUserAutomatically($user);

            return $this->redirectToRoute('admin');
        }

        return $this->render('front/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $helper): Response
    {
        return $this->render('front/login.html.twig', [
            'error' => $helper->getLastAuthenticationError()
        ]);
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function loginUserAutomatically($user) //logs in user after registration
    {
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \Exception('This should never be reached');
    }

    #[Route('/payment', name: 'payment')]
    public function payment(): Response
    {
        return $this->render('front/payment.html.twig');
    }

    public function mainCategories(): Response
    {
        $categories = $this->doctrine->getRepository(Category::class)
            ->findBy(['parent' => null], ['name' => 'ASC']);
        return $this->render('front/_main_categories.html.twig', [
            'categories' => $categories
        ]);
    }


}

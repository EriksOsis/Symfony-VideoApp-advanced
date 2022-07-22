<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Video;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LikesController extends AbstractController
{

    public function __construct(private ManagerRegistry $doctrine)
    {
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
                $result = $this->likeVideo($video);
                break;

            case 'dislike_video':
                $result = $this->dislikeVideo($video);
                break;

            case 'undo_like_video':
                $result = $this->undoLikeVideo($video);
                break;

            case 'undo_dislike_video';
                $result = $this->undoDislikeVideo($video);
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
        $user->removeDislikedVideo($video);

        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();
        return 'undo disliked';
    }
}

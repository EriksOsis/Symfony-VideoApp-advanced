<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Video;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine)
    {
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

    #[Route('/delete-comment/{comment}', name: 'delete_comment')]
    #[Security("user.getId() == comment.getUser().getId()")]
    public function deleteComment(Comment $comment, Request $request): RedirectResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $entityManager = $this->doctrine->getManager();
        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->redirect($request->headers->get('referer'));
    }
}

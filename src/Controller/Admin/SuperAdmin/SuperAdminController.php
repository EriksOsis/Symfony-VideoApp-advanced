<?php

namespace App\Controller\Admin\SuperAdmin;

use App\Entity\User;
use App\Entity\Video;
use App\Form\VideoType;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Utils\Interfaces\UploaderInterface;

class SuperAdminController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    #[Route('/su/upload-video', name: 'upload_video')]
    public function uploadVideos(): Response
    {
        $user = $this->doctrine->getRepository(User::class)->find($this->getUser());
        return $this->render('/front/admin/upload_video.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/su/upload-video-locally', name: 'upload_video_locally')]
    public function uploadVideoLocally(Request $request, UploaderInterface $fileUploader): Response
    {
        $user = $this->doctrine->getRepository(User::class)->find($this->getUser());

        $video = new Video();
        $form = $this->createForm(VideoType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->doctrine->getManager();

            $file = $video->getUploadedVideo();
            $fileName = $fileUploader->upload($file);

            $base_path = Video::uploadFolder;
            $video->setPath($base_path . $fileName[0]);
            $video->setTitle($fileName[1]);

            $entityManager->persist($video);
            $entityManager->flush();

            return $this->redirectToRoute('videos');
        }

        return $this->render('/front/admin/upload_video_locally.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    #[Route('/delete-video/{video}/{path}', name: "delete_video", requirements: ['path' => '.+'])]
    public function deleteVideo(Video $video, $path, UploaderInterface $fileUploader)
    {
        $entityManager = $this->doctrine->getManager();
        $entityManager->remove($video);
        $entityManager->flush();

        if ($fileUploader->delete($path)) {
            $this->addFlash('success', 'The video was successfully deleted');
        } else {
            $this->addFlash('danger', 'We were not able to delete this video. Check the video');
        }
        return $this->redirectToRoute('videos');
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
    public function deleteUser(User $user): RedirectResponse
    {
        $manager = $this->doctrine->getManager();
        $manager->remove($user);
        $manager->flush();

        return $this->redirectToRoute('users');
    }
}
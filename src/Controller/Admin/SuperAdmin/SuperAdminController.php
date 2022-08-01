<?php

namespace App\Controller\Admin\SuperAdmin;

use App\Entity\Category;
use App\Entity\User;
use App\Entity\Video;
use App\Form\VideoType;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
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

    #[Route('/su/upload-video-by-vimeo', name: 'upload_video_by_vimeo')]
    public function uploadVideoByVimeo(Request $request)
    {
        $vimeo_id = preg_replace('/^\/.+\/', '', $request->get('video_uri'));

        if ($request->get('videoName') && $vimeo_id) {
            $entityManager = $this->doctrine->getManager();
            $video = new Video();
            $video->setTitle($request->get('videoName'));
            $video->setPath(Video::VimeoPath . $vimeo_id);

            $entityManager->persist($video);
            $entityManager->flush();

            return $this->redirectToRoute('videos');
        }
        return $this->render('admin/upload_video_to_vimeo.html.twig');
    }

    #[Route('/su/delete-video/{video}/{path}', name: "delete_video", requirements: ['path' => '.+'])]
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

    #[Route('/su/update-video-category/{video}', name: 'update_video_category', methods: ["POST"])]
    public function updateVideoCategory(Request $request, Video $video): RedirectResponse
    {
        $entityManager = $this->doctrine->getManager();
        $category = $this->doctrine->getRepository(Category::class)->find($request->get('video_category'));

        $video->setCategory($category);

        $entityManager->persist($video);
        $entityManager->flush();

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

    #[Route('/su/set-video-duration/{video}/{vimeo_id}', name: 'set_video_duration', requirements: ['vimeo_id' => '.+'])]
    public function setVideoDuration(Video $video, $vimeo_id)
    {
        if (!is_numeric($vimeo_id)) {
            return $this->redirectToRoute('video');
        }
        $user_vimeo_token = $this->getUser()->getVimeoApiKey();

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.vimeo.com/video/$vimeo_id",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_HTTPHEADER => [
                "Accept: application/vnd.vimeo.*+json;version=3.4",
                "Authorization: bearer $user_vimeo_token",
                "Cache-Control: no-cache",
                "Content-Type: application/x-www/form/urlencoded"
            ]
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            throw new ServiceUnavailableHttpException('Error. Try again later. Message: ' . $error);
        } else {
            $duration = json_decode($response, true)['duration'] / 60;

            if ($duration)
            {
                $video->setDuration($duration);

                $entityManager = $this->doctrine->getmanager();
                $entityManager->persist($video);
                $entityManager->flush();
            }
        }
    }
}
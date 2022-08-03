<?php

namespace App\Controller;

use App\Entity\Video;
use App\Repository\VideoRepository;
use App\Utils\CategoryTreeFrontPage;
use App\Utils\Interfaces\CacheInterface;
use App\Utils\VideoForNoValidSubscription;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VideoController extends AbstractController
{

    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    #[Route('/video-list/category/{categoryname}, {id}/{page}', name: 'video_list', defaults: ['page' => '1'])]
    public function videoList($id, $page, CategoryTreeFrontPage $categories, Request $request, VideoForNoValidSubscription $videoNoMembers,
                              CacheInterface $cache): Response
    {
        $cache = $cache->cache;
        $video_list = $cache->getItem('video_list' . $id . $page . $request->get('sortby'));
        $video_list->expiresAfter(60);

        if (!$video_list->isHit()) {
            $ids = $categories->getChildsIds($id);
            $ids[] = $id;

            $videos = $this->doctrine->getRepository(Video::class)->findByChildIds($ids, $page, $request->get('sortby'));

            $categories->getCategoryListAndParent($id);
            $response = $this->render('front/video_list.html.twig', [
                'subcategories' => $categories,
                'videos' => $videos,
                'video_no_members' => $videoNoMembers->check()
            ]);

            $video_list->set($response);
            $cache->save($video_list);
        }
        return $video_list->get();
    }

    #[Route('/video-details/{video}', name: 'video_details')]
    public function videoDetails(VideoRepository $repo, $video, VideoForNoValidSubscription $videoNoMembers): Response
    {
        return $this->render('front/video_details.html.twig', [
            'video' => $repo->videoDetails($video),
            'video_no_members' => $videoNoMembers->check()
        ]);
    }
}

<?php

namespace App\Controller;

use App\Entity\Video;
use App\Utils\VideoForNoValidSubscription;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    #[Route('/search-results/{page}', name: 'search_results', defaults: ['page' => '1'], methods: 'GET')]
    public function searchResults($page, Request $request, VideoForNoValidSubscription $videoNoMembers): Response
    {
        $videos = null;
        $query = null;

        if ($query = $request->get('query')) {
            $videos = $this->doctrine
                ->getRepository(Video::class)
                ->findByTitle($query, $page, $request->get('sortby'));

            if (!$videos->getItems()) $videos = null;
        }

        return $this->render('front/search_results.html.twig', [
            'videos' => $videos,
            'query' => $query,
            'video_no_members' => $videoNoMembers->check()
        ]);
    }
}

<?php

namespace App\Controller;

use App\Requests\LikeRequest;
use App\Service\LikeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class LikeController extends AbstractController
{
    private $likeService;

    public function __construct(LikeService $likeService)
    {
        $this->likeService = $likeService;
    }

    #[Route('/like', name: 'app_like', methods: ['POST', 'DELETE'])]
    public function index(LikeRequest $request): Response
    {
        $method = $request->getRequest()->getMethod();
        $user = $this->getUser();

        switch ($method) {
            case 'POST':
                $this->likeService->like($request->entityType, $request->entityId, $user);
                break;
            case 'DELETE':
                $this->likeService->unlike($request->entityType, $request->entityId, $user);
                break;
            default:
                break;
        }

        $totalLikes = $this->likeService->countLikesForEntity($request->entityId, $request->entityType);
        $liked = $this->likeService->isLikedByUser($request->entityId, $request->entityType, $user);

        return $this->json([
            'totalLikes' => $totalLikes,
            'liked' => $liked,
        ]);
    }
}

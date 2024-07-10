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

    #[Route('/like', name: 'app_like', methods: ['POST'])]
    public function like(LikeRequest $request): Response
    {
        $user = $this->getUser();
        $this->likeService->like($request->entityType, $request->entityId, $user);

        $totalLikes = $this->likeService->countLikesForEntity($request->entityId, $request->entityType);
        $liked = $this->likeService->isLikedByUser($request->entityId, $request->entityType, $user);

        return $this->json([
            'totalLikes' => $totalLikes,
            'liked' => $liked,
        ]);
    }

    #[Route('/like', name: 'app_unlike', methods: ['DELETE'])]
    public function unlike(LikeRequest $request): Response
    {
        $user = $this->getUser();
        $this->likeService->unlike($request->entityType, $request->entityId, $user);

        $totalLikes = $this->likeService->countLikesForEntity($request->entityId, $request->entityType);
        $liked = $this->likeService->isLikedByUser($request->entityId, $request->entityType, $user);

        return $this->json([
            'totalLikes' => $totalLikes,
            'liked' => $liked,
        ]);
    }
}

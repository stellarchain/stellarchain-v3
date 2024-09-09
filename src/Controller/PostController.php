<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostFormType;
use App\Repository\PostRepository;
use App\Service\LikeService;
use App\Service\RankingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PostController extends AbstractController
{
    private $rankingService;
    private $likeService;
    private $postRepository;
    private $security;

    public function __construct(RankingService $rankingService, LikeService $likeService, Security $security, PostRepository $postRepository)
    {
        $this->rankingService = $rankingService;
        $this->likeService = $likeService;
        $this->postRepository= $postRepository;
        $this->security = $security;
    }

    #[Route('/l/new', name: 'app_post_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $post = new Post();
        $form = $this->createForm(PostFormType::class, $post, [
            'action' => $this->generateUrl('app_post_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->updateTimestamps();
            $post->setUser($this->getUser());
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
        }

        return $this->render('post/new.html.twig', [
            'newPostForm' => $form,
        ]);
    }

    #[Route('/l/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        $this->rankingService->updateRank($post);
        $user = $this->security->getUser();
        $isLiked = false;
        if ($user) {
            $isLiked = $this->likeService->isLikedByUser($post->getId(), 'post', $user);
        }
        $likes = $this->postRepository->getLikesCount($post);
        return $this->render('post/show.html.twig', [
            'post' => $post,
            'liked' => $isLiked,
            'likes' => $likes
        ]);
    }
}

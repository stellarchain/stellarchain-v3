<?php

namespace App\Twig\Components;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Service\LikeService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;


#[AsLiveComponent('posts-component')]
final class PostsComponent
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true)]
    public string $type = 'hot';

    #[LiveProp]
    public int $page = 1;

    private const PER_PAGE = 20;

    private $postRepository;

    private $likeService;
    private $security;

    public function __construct(PostRepository $postRepository, LikeService $likeService, Security $security)
    {
        $this->postRepository = $postRepository;
        $this->likeService = $likeService;
        $this->security = $security;
    }

    #[LiveAction]
    public function changeType(#[LiveArg] string $type): void
    {
        $this->type = $type;
        $this->page = 1;
    }

    #[LiveAction]
    public function more(): void
    {
        ++$this->page;
    }

    public function hasMore(): bool
    {
        //$criteria = $this->buildCriteria();
        $totalPosts = $this->postRepository->count([]);
        return $totalPosts > ($this->page * self::PER_PAGE);
    }

    #[ExposeInTemplate('per_page')]
    public function getPerPage(): int
    {
        return self::PER_PAGE;
    }

    /**
     * @return array<int,Post>
     */
    public function getPosts(): array
    {
        $criteria = $this->buildCriteria();
        $offset = ($this->page - 1) * self::PER_PAGE;
        $posts = $this->postRepository->findBy([], $criteria, self::PER_PAGE, $offset);
        $postsWithLikes = [];
        $user = $this->security->getUser();
        foreach ($posts as $post) {
            $isLiked = false;
            if ($user) {
                $isLiked = $this->likeService->isLikedByUser($post->getId(), 'post', $user);
            }
            $postsWithLikes[] = [
                'post' => $post,
                'likes' => $this->postRepository->getLikesCount($post),
                'liked' => $isLiked,
            ];
        }
        return $postsWithLikes;
    }

    /**
     * @return array|array<string,string>
     */
    private function buildCriteria(): array
    {
        switch ($this->type) {
            case 'hot':
                return [];
            case 'new':
                return ['created_at' => 'DESC'];
            case 'top':
                return ['rank' => 'DESC'];
            default:
                return [];
        }
    }
}

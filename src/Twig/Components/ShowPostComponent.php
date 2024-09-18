<?php

namespace App\Twig\Components;

use App\Entity\Feedback;
use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('show-post-component')]
final class ShowPostComponent
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp]
    public Post $post;


    #[LiveProp]
    public int $liked;


    #[LiveProp]
    public $likes;

    #[LiveProp]
    public $parentComment = null;

    public function __construct(
        private PostRepository $postRepository,
        private Security $security,
        private UrlGeneratorInterface $urlGenerator,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[LiveAction]
    public function updateParentComment(): void
    {
        $this->parentComment = null;
    }

    #[LiveAction]
    public function deletePost(): RedirectResponse
    {
        $user = $this->security->getUser();
        if ($user == $this->post->getUser()) {
            $this->postRepository->deletePost($this->post);
        }
        $url = $this->urlGenerator->generate('app_home'); // Replace 'homepage' with the actual route name
        return new RedirectResponse($url);
    }

    #[LiveAction]
    public function reportPost(): void
    {
        $user = $this->security->getUser();
        if ($user) {
            $feedback = new Feedback();
            $feedback->setMessage('This post has beed reported -> Id: '.$this->post->getId().' - '.$this->post->getTitle().'.');
            $feedback->setCreatedAt(new \DateTimeImmutable());
            $feedback->setUserId($user);

            $this->entityManager->persist($feedback);
            $this->entityManager->flush();

            $this->dispatchBrowserEvent('auth:false', ['title' => 'Reported', 'message' => 'This post is reported.Thank you!']);
        }else {
            $this->dispatchBrowserEvent('auth:false');
        }
    }
}

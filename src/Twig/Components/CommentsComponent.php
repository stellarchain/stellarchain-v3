<?php

namespace App\Twig\Components;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Project;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('comments-component')]
final class CommentsComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use ComponentToolsTrait;

    #[LiveProp]
    public Post $entity;

    public string $commentType;

    #[LiveProp(writable: true, url: true)]
    public string $order = 'popular';

    #[LiveProp]
    public ?Comment $parentComment = null;

    public function __construct(
        private CommentRepository $commentRepository,
        private FormFactoryInterface $formFactory,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function instantiateForm(): FormInterface
    {
        $comment = new Comment();
        $comment->setPost($this->entity);

        if ($this->parentComment) {
            $comment->setParent($this->parentComment);
        }

        return $this->createForm(CommentFormType::class, $comment);
    }

    /**
     * @return array<int,Comment>
     */
    public function getComments(): array
    {
        if (!$this->entity) {
            throw new \LogicException('Entity is not set.');
        }

        $entityClass = get_class($this->entity);
        $criteria = [];

        switch ($entityClass) {
            case Post::class:
                $criteria['post'] = $this->entity;
                $this->commentType = 'post';
                break;
            case Project::class:
                $criteria['project'] = $this->entity;
                $this->commentType = 'project';
                break;
            default:
                throw new \InvalidArgumentException('Unsupported entity type');
        }

        $criteria['parent'] = null;
        $sortField = $this->order === 'latest' ? 'created_at' : 'votes';

        $comments = $this->commentRepository->findBy(
            $criteria,
            [$sortField => 'DESC']
        );

        foreach ($comments as $comment) {
            $comment->getReplies($this->order);
        }
        return $comments;
    }

    public function getTotalCommentsAndReplies(): array
    {
        $comments = $this->getComments();
        $totalCount = count($comments);
        $totalReplies = 0;

        foreach ($comments as $comment) {
            $totalReplies += $comment->getReplies()->count();
        }

        return ['comments' => $totalCount, 'replies' => $totalReplies];
    }

    #[LiveAction]
    public function toggleSort(): void
    {
        $this->order = $this->order === 'popular' ? 'latest' : 'popular';
    }

    #[LiveAction]
    public function vote(#[LiveArg] int $commentId): void
    {
        $comment = $this->commentRepository->find($commentId);

        if (!$comment) {
            throw $this->createNotFoundException('Comment not found.');
        }

        $comment->setVotes($comment->getVotes() + 1);

        $this->entityManager->persist($comment);
        $this->entityManager->flush();
    }

    #[LiveAction]
    public function save(): void
    {
        if (!$this->getUser()) {
            // If the user is not authenticated, return a 401 Unauthorized response
            $this->dispatchBrowserEvent('auth:false');
            // Optionally, you could use Turbo to trigger a frontend event to show a toast notification
            return;
        }
        $this->submitForm();
        $comment = $this->form->getData();

        if ($this->form->isSubmitted() && $this->form->isValid()) {
            if ($this->parentComment) {
                $comment->setParent($this->parentComment);
            }
            $comment->setUser($this->getUser());
            $comment->setVotes(0);

            $entityClass = get_class($this->entity);
            switch ($entityClass) {
                case Post::class:
                    $this->commentType = 'post';
                    break;
                case Project::class:
                    $this->commentType = 'project';
                    break;
                default:
                    throw new \InvalidArgumentException('Unsupported entity type');
            }

            $comment->setCommentType($this->commentType);
            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            // Reset the form after saving
            $this->parentComment = null;
            $this->form = $this->instantiateForm();
        }
    }

    #[LiveAction]
    public function newComment(): void
    {
        $this->parentComment = null;
        $this->form = $this->instantiateForm();
    }

    #[LiveAction]
    public function reply(#[LiveArg] int $parentId): void
    {
        $this->parentComment = $this->commentRepository->find($parentId);
        $this->form = $this->instantiateForm();
    }
}

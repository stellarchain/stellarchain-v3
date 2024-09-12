<?php

namespace App\Twig\Components;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Project;
use App\Entity\User;
use App\Entity\Vote;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use App\Repository\VoteRepository;
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
        private EntityManagerInterface $entityManager,
        private VoteRepository $voteRepository
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
        $sortField = $this->order === 'latest' ? 'created_at' : 'votes_count';

        $comments = $this->commentRepository->findBy(
            $criteria,
            [$sortField => 'DESC']
        );

        $user = $this->getUser();
        if ($user) {
            foreach ($comments as $comment) {
                $this->setUserHasVotedRecursively($comment, $user);
            }
        }

        return $comments;
    }

    private function setUserHasVotedRecursively(Comment $comment, User $user): void
    {
        // Check if the user has voted on the comment
        $comment->setUserHasVoted($this->hasUserVoted($comment, $user));

        // Recursively check all replies to this comment
        foreach ($comment->getReplies() as $reply) {
            $this->setUserHasVotedRecursively($reply, $user);
        }
    }

    public function hasUserVoted(Comment $comment, User $user): bool
    {
        return $this->voteRepository->findOneBy([
            'user' => $user,
            'comment' => $comment,
        ]) !== null;
    }

    public function getTotalCommentsAndReplies(): int
    {
        $entityClass = get_class($this->entity);
        $total = 0;

        switch ($entityClass) {
            case Post::class:
                $total = $this->entity->getComments()->count();
                break;
            case Project::class:
                break;
            default:
                throw new \InvalidArgumentException('Unsupported entity type');
        }

        return $total;
    }

    #[LiveAction]
    public function toggleSort(): void
    {
        $this->order = $this->order === 'popular' ? 'latest' : 'popular';
    }

    #[LiveAction]
    public function vote(#[LiveArg] int $commentId): void
    {
        $user = $this->getUser();
        if (!$this->getUser()) {
            $this->dispatchBrowserEvent('auth:false');
            return;
        }

        $comment = $this->commentRepository->find($commentId);
        if (!$comment) {
            throw $this->createNotFoundException('Comment not found.');
        }

        $existingVote = $this->voteRepository->findOneBy([
            'user' => $user,
            'comment' => $comment,
        ]);

        if ($existingVote || !$user) {
            $this->dispatchBrowserEvent('auth:false');
        }



        $vote = new Vote();
        $vote->setUser($user);
        $vote->setComment($comment);
        $this->entityManager->persist($vote);

        $comment->setVotesCount($comment->getVotesCount() + 1);

        $this->entityManager->persist($comment);
        $this->entityManager->flush();
    }

    #[LiveAction]
    public function save(): void
    {
        if (!$this->getUser()) {
            $this->dispatchBrowserEvent('auth:false');
            return;
        }
        $this->submitForm();
        $comment = $this->form->getData();

        if ($this->form->isSubmitted() && $this->form->isValid()) {
            if ($this->parentComment) {
                $comment->setParent($this->parentComment);
            }
            $comment->setUser($this->getUser());
            $comment->setVotesCount(0);

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

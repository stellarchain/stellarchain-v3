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
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('comments-component')]
final class CommentsComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public Post $entity;

    public string $commentType;

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

        return $this->commentRepository->findBy(
            $criteria,
            ['created_at' => 'DESC']
        );
    }

    public function getTotalCommentsAndReplies(): int
    {
        $comments = $this->getComments();
        $totalCount = count($comments);

        foreach ($comments as $comment) {
            $totalCount += $comment->getReplies()->count();
        }

        return $totalCount;
    }

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();
        $comment = $this->form->getData();

        if ($this->form->isSubmitted() && $this->form->isValid()) {
            if ($this->parentComment) {
                $comment->setParent($this->parentComment);
            }
            $comment->setUser($this->getUser());

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

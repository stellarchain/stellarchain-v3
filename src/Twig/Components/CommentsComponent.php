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
use Symfony\Component\Form\FormView;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('comments-component')]
final class CommentsComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    public ?object $entity = null;

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

        return $this->createForm(CommentFormType::class, $comment);
    }

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
                break;
            case Project::class:
                $criteria['project'] = $this->entity;
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

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();
        $comment = $this->form->getData();

        if ($this->form->isSubmitted() && $this->form->isValid()) {
            $this->entityManager->persist($comment);
            $this->entityManager->flush();
        }
    }
}

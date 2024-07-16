<?php

namespace App\Service;

use App\Entity\Comment;
use App\Form\CommentFormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Form\FormInterface;

class CommentService
{

    public function __construct(private FormFactoryInterface $formFactory, private UrlGeneratorInterface $router)
    {
    }

    /**
     * @param array<int,mixed> $comments
     * @return array<int,mixed>
     */
    public function commentsWithForms(array $comments): array
    {
        $preparedComments = [];
        foreach ($comments as $comment) {
            $replyForm = $this->createReplyForm($comment->getId())->createView();
            $preparedComments[] = [
                'comment' => $comment,
                'reply_form' => $replyForm,
                'replies' => $this->commentsWithForms($comment->getReplies()->toArray()),
            ];
        }
        return $preparedComments;
    }

    public function createReplyForm(int $commentId): FormInterface
    {
        return $this->formFactory->createNamed(
            'reply_form',
            CommentFormType::class,
            new Comment(),
            [
                'action' => $this->router->generate('app_comment_reply', ['id' => $commentId]),
                'parent' => $commentId
            ]
        );
    }
}

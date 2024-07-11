<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentFormType;
use App\Form\PostFormType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;

class PostController extends AbstractController
{

    public function __construct(private FormFactoryInterface $formFactory)
    {
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
    public function index(Post $post, CommentRepository $commentRepository): Response
    {
        $comments = $commentRepository->findBy(
            ['post' => $post, 'parent' => null],
            ['created_at' => 'DESC']
        );

        $commentForm =  $this->createForm(
            CommentFormType::class,
            new Comment(),
            [
                'action' => $this->generateUrl('app_post_comment', ['id' => $post->getId()]),
                'method' => 'POST',
            ]
        )->createView();

        $preparedComments = $this->prepareCommentsWithForms($comments, $post->getId());

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'comments' => $preparedComments,
            'comment_form' => $commentForm
        ]);
    }

    #[Route('/l/{id}', name: 'app_post_comment', methods: ['POST'])]
    public function comment(Request $request, EntityManagerInterface $entityManager, CommentRepository $commentRepository, Post $post): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment, [
            'action' => $this->generateUrl('app_post_comment', ['id' => $post->getId()]),
        ]);
        $emptyForm = clone $form;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->updateTimestamps();
            $comment->setCommentType('post');
            $comment->setPost($post);
            $comment->setUser($this->getUser());

            $entityManager->persist($comment);
            $entityManager->flush();

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                $replyForm = $this->createReplyForm($comment->getId(), $comment->getPost()->getId());
                $replyForm->setData(['parent' => $comment->getId()]);

                return $this->renderBlock(
                    'post/show.html.twig',
                    'create_comment',
                    [
                        'comment_form' => $emptyForm,
                        'comment' => [
                            'comment' => $comment,
                            'reply_form' => $replyForm->createView(),
                            'replies' => $this->prepareCommentsWithForms($comment->getReplies()->toArray(), $comment->getPost()->getId()),
                        ]
                    ]
                );
            }
        }

        $comments = $commentRepository->findBy(
            ['post' => $post, 'parent' => null],
            ['created_at' => 'DESC']
        );

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'comment_form' => $form,
            'comments' => $this->prepareCommentsWithForms($comments, $post->getId()),
        ]);
    }

    #[Route('/l/{id}/reply', name: 'app_comment_reply', methods: ['POST'])]
    public function replyComment(Request $request, EntityManagerInterface $entityManager, CommentRepository $commentRepository, Comment $comment): Response
    {
        $reply = new Comment();
        $form = $this->createReplyForm($comment->getId(), $comment->getPost()->getId());
        $form->handleRequest($request);
        $reply = $form->getData();

        $commentForm = $this->createForm(CommentFormType::class, $comment, [
            'action' => $this->generateUrl('app_post_comment', ['id' => $comment->getPost()->getId()]),
        ]);

        if ($form->isSubmitted() && $form->isValid()) {
            $reply->updateTimestamps();
            $reply->setCommentType('post');
            $reply->setPost($comment->getPost());
            $reply->setUser($this->getUser());
            $reply->setParent($comment);

            $entityManager->persist($reply);
            $entityManager->flush();

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                $replyForm = $this->createReplyForm($reply->getId(), $comment->getPost()->getId());

                return $this->renderBlock(
                    'post/show.html.twig',
                    'update_comment',
                    [
                        'comment' => [
                            'comment' => $reply,
                            'reply_form' => $replyForm->createView(),
                            'replies' => $this->prepareCommentsWithForms($reply->getReplies()->toArray(), $comment->getPost()->getId()),
                        ],
                        'parent_id' => $comment->getId(),
                        'comment_form' => $form
                    ]

                );
            }
        }

        $comments = $commentRepository->findBy(
            ['post' => $comment->getPost(), 'parent' => null],
            ['created_at' => 'DESC']
        );

        return $this->render('post/show.html.twig', [
            'post' => $comment->getPost(),
            'comment_form' => $commentForm,
            'comments' => $this->prepareCommentsWithForms($comments, $comment->getPost()->getId()),
        ]);
    }

    /**
     * @param array<int,mixed> $comments
     * @return array<int,mixed>
     */
    private function prepareCommentsWithForms(array $comments, int $postId): array
    {
        $preparedComments = [];
        foreach ($comments as $comment) {
            $replyForm = $this->createReplyForm($comment->getId(), $postId)->createView();
            $preparedComments[] = [
                'comment' => $comment,
                'reply_form' => $replyForm,
                'replies' => $this->prepareCommentsWithForms($comment->getReplies()->toArray(), $postId),
            ];
        }
        return $preparedComments;
    }

    private function createReplyForm(int $commentId, int $postId): FormInterface
    {
        return $this->formFactory->createNamed(
            'reply_form',
            CommentFormType::class,
            new Comment(),
            [
                'action' => $this->generateUrl('app_comment_reply', ['id' => $commentId]),
                'parent' => $commentId
            ]
        );
    }
}

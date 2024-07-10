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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;

class PostController extends AbstractController
{

    public function __construct(FormFactoryInterface $formFactory)
    {
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

        $preparedComments = $this->prepareCommentsWithForms($comments);

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

            $parentId = $form->get('parent')->getData();
            if ($parentId) {
                $parentComment = $commentRepository->find($parentId);
                if ($parentComment) {
                    $comment->setParent($parentComment);
                }
            }

            $entityManager->persist($comment);
            $entityManager->flush();

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                $replyForm = $this->createForm(CommentFormType::class, new Comment(), [
                    'action' => $this->generateUrl('app_post_comment', ['id' => $comment->getPost()->getId()]),
                    'parent' => $comment->getId()
                ])->createView();

                return $this->renderBlock(
                    'post/show.html.twig',
                    'create_comment',
                    [
                        'id' => $comment->getId(),
                        'comment' => [
                            'comment' => $comment,
                            'reply_form' => $replyForm,
                            'replies' => $this->prepareCommentsWithForms($comment->getReplies()->toArray()),
                        ],
                        'comment_form' => $emptyForm,
                        'comments' => $this->prepareCommentsWithForms($commentRepository->findBy(['post' => $post])),
                        'parent_id' => $parentId
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
            'comments' => $this->prepareCommentsWithForms($comments),
        ]);
    }

    /**
     * @param array<int,mixed> $comments
     * @return array<int,mixed>
     */
    private function prepareCommentsWithForms(array $comments): array
    {
        $preparedComments = [];
        foreach ($comments as $comment) {
            $replyForm = $this->createForm(CommentFormType::class, new Comment(), [
                'action' => $this->generateUrl('app_post_comment', ['id' => $comment->getPost()->getId()]),
                'parent' => $comment->getId()
            ])->createView();

            $preparedComments[] = [
                'comment' => $comment, // Directly store the Comment object
                'reply_form' => $replyForm,
                'replies' => $this->prepareCommentsWithForms($comment->getReplies()->toArray()), // Recursively prepare replies
            ];
        }
        return $preparedComments;
    }

    #[Route('/posts/new', name: 'app_post_new')]
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
}

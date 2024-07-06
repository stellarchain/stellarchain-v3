<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentFormType;
use App\Form\PostFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class PostController extends AbstractController
{
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

        }

        return $this->render('post/new.html.twig', [
            'newPostForm' => $form,
        ]);
    }

    #[Route('/posts/{slug}', name: 'app_post_show')]
    public function index(Request $request, string $slug, EntityManagerInterface $entityManager, HubInterface $hub): Response
    {
        $post = $entityManager->getRepository(Post::class)->findOneBy(['slug' => $slug]);
        if (!$post) {
            throw $this->createNotFoundException('The post does not exist');
        }

        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment, [
            'action' => $this->generateUrl('app_post_show', ['slug' => $post->getSlug()]),
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

            $hub->publish(new Update(
                'comments-list-'.$post->getId(),
                $this->renderBlock('post/show.html.twig', 'new_comment', ['id' => $comment->getId(), 'content' => $comment->getContent(), 'username' => $comment->getUser()->getUsername()])
            ));

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                return $this->renderBlock('post/show.html.twig', 'create_comment', ['id' => $comment->getId(), 'content' => $comment->getContent(), 'username' => $comment->getUser()->getUsername(), 'newCommentForm' => $emptyForm]);
            }
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'newCommentForm' => $form
        ]);
    }
}

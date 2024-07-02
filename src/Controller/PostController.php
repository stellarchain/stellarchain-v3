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

class PostController extends AbstractController
{
    #[Route('/post/new', name: 'app_post_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $post = new Post();
        $form = $this->createForm(PostFormType::class, $post, [
            'action' => $this->generateUrl('app_post_new'),
        ]);
        $form->handleRequest($request);

        $response = new Response(null, 200);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->updateTimestamps();
            $post->setUser($this->getUser());
            $entityManager->persist($post);
            $entityManager->flush();
            $response = new Response(null, 422);
        }

        return $this->render('post/new.html.twig', [
            'newPostForm' => $form,
        ], $response);
    }

    #[Route('/post/{slug}', name: 'app_post_show')]
    public function index(Request $request, string $slug, EntityManagerInterface $entityManager): Response
    {
        $post = $entityManager->getRepository(Post::class)->findOneBy(['slug' => $slug]);
        if (!$post) {
            throw $this->createNotFoundException('The post does not exist');
        }

        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment, [
            'action' => $this->generateUrl('app_post_show', ['slug' => $post->getSlug()]),
        ]);
        $form->handleRequest($request);

        $response = new Response(null, 200);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->updateTimestamps();

            $comment->setCommentType('post');
            $comment->setPost($post);

            $comment->setUser($this->getUser());

            $entityManager->persist($comment);
            $entityManager->flush();
            $response = new Response(null, 200);
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'newCommentForm' => $form
        ], $response);
    }
}

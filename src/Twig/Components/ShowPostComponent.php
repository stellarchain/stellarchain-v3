<?php

namespace App\Twig\Components;

use App\Entity\Post;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('show-post-component')]
final class ShowPostComponent
{
    use DefaultActionTrait;

    #[LiveProp]
    public Post $post;


    #[LiveProp]
    public int $liked;


    #[LiveProp]
    public $likes;

   #[LiveProp]
   public $parentComment = null;

    #[LiveAction]
    public function updateParentComment(): void
    {
        $this->parentComment = null;
    }
}

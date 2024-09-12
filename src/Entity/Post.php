<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\UX\Turbo\Attribute\Broadcast;


#[ORM\Entity(repositoryClass: PostRepository::class)]
#[Broadcast()]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $body = null;

    #[ORM\Column(nullable: true)]
    private ?int $type_post = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(nullable: true)]
    private ?int $type_post_id = null;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(name: "type_post_id", referencedColumnName: "id")]
    private $project;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(nullable: true)]
    private ?float $rank = 0.0;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'post')]
    private $comments;

    #[ORM\Column(nullable: true)]
    private ?int $views = null;

    #[ORM\Column(nullable: true)]
    private ?int $votes = null;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }
    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function getTypePost(): ?int
    {
        return $this->type_post;
    }

    public function setTypePost(?int $type_post): static
    {
        $this->type_post = $type_post;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getTypePostId(): ?int
    {
        return $this->type_post_id;
    }

    public function setTypePostId(int $type_post_id): static
    {
        $this->type_post_id = $type_post_id;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(int $type_post_id): static
    {
        $this->type_post_id = $type_post_id;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user_id): static
    {
        $this->user = $user_id;

        return $this;
    }

    public function updateTimestamps(): void
    {
        $now = new \DateTimeImmutable();
        $this->setUpdatedAt($now);
        if ($this->getId() === null) {
            $this->setCreatedAt($now);
        }
    }

    public function getRank(): ?float
    {
        return $this->rank;
    }

    public function setRank(?float $rank): static
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        $iterator = $this->comments->getIterator();
        $iterator->uasort(function ($first, $second) {
            return $second->getCreatedAt() <=> $first->getCreatedAt();
        });

        return new ArrayCollection(iterator_to_array($iterator));
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }

    public function getViews(): ?int
    {
        return $this->views;
    }

    public function setViews(?int $views): static
    {
        $this->views = $views;

        return $this;
    }

    public function incrementViews(): self
    {
        $this->views++;

        return $this;
    }

    public function getTotalRepliesCount(): int
    {
        $totalReplies = 0;
        foreach ($this->comments as $comment) {
            $totalReplies += $comment->getRepliesCount();
        }
        return $totalReplies;
    }

    public function getVotes(): ?int
    {
        return $this->votes;
    }

    public function setVotes(?int $votes): static
    {
        $this->votes = $votes;

        return $this;
    }
}

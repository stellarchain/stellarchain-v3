<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]

class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $comment_type = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?int $comment_type_id = null;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(name: "comment_type_id", referencedColumnName: "id")]
    private $project;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(name: "comment_type_id", referencedColumnName: "id")]
    private $post;

    #[ORM\ManyToOne(targetEntity: Comment::class, inversedBy: 'replies')]
    #[ORM\JoinColumn(name: "parent_id", referencedColumnName: "id", nullable: true)]
    private $parent;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: "parent")]
    private $replies;

    #[ORM\Column(nullable: true)]
    private ?int $votes_count = null;

    /**
     * @var Collection<int, Vote>
     */
    #[ORM\OneToMany(targetEntity: Vote::class, mappedBy: 'comment')]
    private Collection $votes;

    private bool $userHasVoted = false;

    public function __construct()
    {
        $this->replies = new ArrayCollection();
        $this->created_at = new \DateTimeImmutable();
        $this->votes = new ArrayCollection();
    }

    public function setUserHasVoted(bool $hasVoted): void
    {
        $this->userHasVoted = $hasVoted;
    }

    public function getUserHasVoted(): bool
    {
        return $this->userHasVoted;
    }

    public function __toString(): string
    {
        return substr($this->content, 0, 10);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $body): static
    {
        $this->content = $body;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCommentType(): ?string
    {
        return $this->comment_type;
    }

    public function setCommentType(string $comment_type): static
    {
        $this->comment_type = $comment_type;

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

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->created_at === null) {
            $this->created_at = new \DateTime();
        }
    }

    public function updateTimestamps(): void
    {
        $now = new \DateTimeImmutable();
        if ($this->getId() === null) {
            $this->setCreatedAt($now);
        }
    }

    public function getCommentTypeId(): ?int
    {
        return $this->comment_type_id;
    }

    public function setCommentTypeId(int $comment_type_id): static
    {
        $this->comment_type_id = $comment_type_id;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

        return $this;
    }

    public function getParent(): ?Comment
    {
        return $this->parent;
    }

    public function setParent(?Comment $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getReplies(string $sortOrder = 'popular'): Collection
    {
        $criteria = Criteria::create();

        if ($sortOrder === 'latest') {
            $criteria->orderBy(['created_at' => 'DESC']);
        } else { // 'popular'
            $criteria->orderBy(['votes_count' => 'DESC']);
        }

        return $this->replies->matching($criteria);
    }

    public function getRepliesCount(): int
    {
        $totalReplies = 0;
        foreach ($this->replies as $reply) {
            $totalReplies += $reply->getRepliesCount(); // Recursively count replies
        }
        return $totalReplies;
    }

    public function addReply(Comment $reply): self
    {
        if (!$this->replies->contains($reply)) {
            $this->replies[] = $reply;
            $reply->setParent($this);
        }

        return $this;
    }

    public function removeReply(Comment $reply): self
    {
        if ($this->replies->removeElement($reply)) {
            // set the owning side to null (unless already changed)
            if ($reply->getParent() === $this) {
                $reply->setParent(null);
            }
        }

        return $this;
    }

    public function getVotesCount(): ?int
    {
        return $this->votes_count;
    }

    public function setVotesCount(?int $votes): static
    {
        $this->votes_count = $votes;

        return $this;
    }

    /**
     * @return Collection<int, Vote>
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): static
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setComment($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): static
    {
        if ($this->votes->removeElement($vote)) {
            // set the owning side to null (unless already changed)
            if ($vote->getComment() === $this) {
                $vote->setComment(null);
            }
        }

        return $this;
    }
}

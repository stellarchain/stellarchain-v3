<?php

namespace App\Entity;

use App\Repository\CommunityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommunityRepository::class)]
class Community
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'communities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, CommunityPost>
     */
    #[ORM\OneToMany(targetEntity: CommunityPost::class, mappedBy: 'community')]
    private Collection $communityPosts;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->communityPosts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, CommunityPost>
     */
    public function getCommunityPosts(): Collection
    {
        return $this->communityPosts;
    }

    public function addCommunityPost(CommunityPost $communityPost): static
    {
        if (!$this->communityPosts->contains($communityPost)) {
            $this->communityPosts->add($communityPost);
            $communityPost->setCommunity($this);
        }

        return $this;
    }

    public function removeCommunityPost(CommunityPost $communityPost): static
    {
        if ($this->communityPosts->removeElement($communityPost)) {
            // set the owning side to null (unless already changed)
            if ($communityPost->getCommunity() === $this) {
                $communityPost->setCommunity(null);
            }
        }

        return $this;
    }

}

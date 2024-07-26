<?php

namespace App\Entity;

use App\Config\AwardType;
use App\Entity\SCF\Round;
use App\Entity\SCF\RoundPhase;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[Vich\Uploadable]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column]
    private ?int $budget = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Gedmo\Slug(fields: ['name'])]
    private $slug;

    #[Vich\UploadableField(mapping: 'projects', fileNameProperty: 'image.name', size: 'image.size')]
    private ?File $imageFile = null;

    #[ORM\Embedded(class: 'Vich\UploaderBundle\Entity\File')]
    private ?EmbeddedFile $image = null;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: "project")]
    private $comments;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    private ?Round $round = null;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    private ?RoundPhase $round_phase = null;

    #[ORM\Column(nullable: true)]
    private ?int $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $scf_url = null;

    #[ORM\Column(nullable: true)]
    private ?int $score = null;

    #[ORM\Column(nullable: true)]
    private ?int $original_id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(nullable: true)]
    private ?bool $essential = false;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    private ?ProjectCategory $category = null;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    private ?ProjectType $type = null;

    #[ORM\Column(nullable: true)]
    private ?int $award_type = null;

    /**
     * @var Collection<int, ProjectBrief>
     */
    #[ORM\OneToMany(targetEntity: ProjectBrief::class, mappedBy: 'project')]
    private Collection $projectBriefs;

    public function __construct()
    {
        $this->image = new EmbeddedFile();
        $this->created_at = new \DateTimeImmutable();
        $this->updated_at = new \DateTimeImmutable();
        $this->comments = new ArrayCollection();
        $this->projectBriefs = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getBudget(): ?int
    {
        return $this->budget;
    }

    public function setBudget(int $budget): static
    {
        $this->budget = $budget;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->created_at === null) {
            $this->created_at = new \DateTimeImmutable();
        }
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updated_at = new \DateTimeImmutable();
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

    public function updateTimestamps(): void
    {
        $now = new \DateTimeImmutable();
        $this->setUpdatedAt($now);
        if ($this->getId() === null) {
            $this->setCreatedAt($now);
        }
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            $this->updated_at = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImage(EmbeddedFile $image): void
    {
        $this->image = $image;
    }

    public function getImage(): ?EmbeddedFile
    {
        return $this->image;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setProject($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getProject() === $this) {
                $comment->setProject(null);
            }
        }

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getRound(): ?Round
    {
        return $this->round;
    }

    public function setRound(?Round $round): static
    {
        $this->round = $round;

        return $this;
    }

    public function getRoundPhase(): ?RoundPhase
    {
        return $this->round_phase;
    }

    public function setRoundPhase(?RoundPhase $round_phase): static
    {
        $this->round_phase = $round_phase;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getScfUrl(): ?string
    {
        return $this->scf_url;
    }

    public function setScfUrl(?string $scf_url): static
    {
        $this->scf_url = $scf_url;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(?int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getOriginalId(): ?int
    {
        return $this->original_id;
    }

    public function setOriginalId(?int $original_id): static
    {
        $this->original_id = $original_id;

        return $this;
    }

    public function isEssential(): ?bool
    {
        return $this->essential;
    }

    public function setEssential(?bool $essential): static
    {
        $this->essential = $essential;

        return $this;
    }

    public function getCategory(): ?ProjectCategory
    {
        return $this->category;
    }

    public function setCategory(?ProjectCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getType(): ?ProjectType
    {
        return $this->type;
    }

    public function setType(?ProjectType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getAwardType(): ?int
    {
        return $this->award_type;
    }

    public function getAwardTypeEnum(): ?AwardType
    {
        return $this->award_type !== null ? AwardType::from($this->award_type) : null;
    }

    public function setAwardType(?int $award_type): static
    {
        $this->award_type = $award_type;

        return $this;
    }

    /**
     * @return Collection<int, ProjectBrief>
     */
    public function getProjectBriefs(): Collection
    {
        return $this->projectBriefs;
    }

    public function addProjectBrief(ProjectBrief $projectBrief): static
    {
        if (!$this->projectBriefs->contains($projectBrief)) {
            $this->projectBriefs->add($projectBrief);
            $projectBrief->setProject($this);
        }

        return $this;
    }

    public function removeProjectBrief(ProjectBrief $projectBrief): static
    {
        if ($this->projectBriefs->removeElement($projectBrief)) {
            // set the owning side to null (unless already changed)
            if ($projectBrief->getProject() === $this) {
                $projectBrief->setProject(null);
            }
        }

        return $this;
    }
}

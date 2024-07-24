<?php

namespace App\Entity;

use App\Repository\ProjectCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectCategoryRepository::class)]
class ProjectCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Project>
     */
    #[ORM\OneToMany(targetEntity: Project::class, mappedBy: 'category')]
    private Collection $projects;

    #[ORM\Column(length: 255)]
    private ?string $tag = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null;

    /**
     * @var Collection<int, ProjectType>
     */
    #[ORM\OneToMany(targetEntity: ProjectType::class, mappedBy: 'category')]
    private Collection $projectTypes;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->projectTypes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
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

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): static
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->setCategory($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            // set the owning side to null (unless already changed)
            if ($project->getCategory() === $this) {
                $project->setCategory(null);
            }
        }

        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return Collection<int, ProjectType>
     */
    public function getProjectTypes(): Collection
    {
        return $this->projectTypes;
    }

    public function addProjectType(ProjectType $projectType): static
    {
        if (!$this->projectTypes->contains($projectType)) {
            $this->projectTypes->add($projectType);
            $projectType->setCategory($this);
        }

        return $this;
    }

    public function removeProjectType(ProjectType $projectType): static
    {
        if ($this->projectTypes->removeElement($projectType)) {
            // set the owning side to null (unless already changed)
            if ($projectType->getCategory() === $this) {
                $projectType->setCategory(null);
            }
        }

        return $this;
    }
}

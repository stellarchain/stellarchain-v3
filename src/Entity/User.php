<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $first_name = null;

    #[ORM\Column(length: 255)]
    private ?string $last_name = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column]
    private bool $isVerified = false;

    /**
     * @var Collection<int, Project>
     */
    #[ORM\OneToMany(targetEntity: Project::class, mappedBy: 'user')]
    private Collection $projects;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'user')]
    private Collection $posts;

    /**
     * @var Collection<int, Like>
     */
    #[ORM\OneToMany(targetEntity: Like::class, mappedBy: 'user')]
    private Collection $likes;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'user')]
    private Collection $comments;

    /**
     * @var Collection<int, Community>
     */
    #[ORM\OneToMany(targetEntity: Community::class, mappedBy: 'user')]
    private Collection $communities;

    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'user_follows_user')]
    #[ORM\JoinColumn(name: 'follower_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'followed_id', referencedColumnName: 'id')]
    private Collection $followedUsers;

    /**
     * @var Collection<int, Community>
     */
    #[ORM\ManyToMany(targetEntity: Community::class, mappedBy: 'followers')]
    private Collection $followedCommunities;

    /**
     * @var Collection<int, Community>
     */
    #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'followers')]
    private Collection $followedProjects;

    /**
     * @var Collection<int, Job>
     */
    #[ORM\OneToMany(targetEntity: Job::class, mappedBy: 'user')]
    private Collection $jobs;

    /**
     * @var Collection<int, CommunityPost>
     */
    #[ORM\OneToMany(targetEntity: CommunityPost::class, mappedBy: 'user')]
    private Collection $communityPosts;

    /**
     * @var Collection<int, Vote>
     */
    #[ORM\OneToMany(targetEntity: Vote::class, mappedBy: 'user')]
    private Collection $votes;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->communities = new ArrayCollection();
        $this->jobs = new ArrayCollection();
        $this->communityPosts = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->followedCommunities = new ArrayCollection();
        $this->followedProjects = new ArrayCollection();
        $this->followedUsers = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): static
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): static
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

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
            $project->setUser($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            // set the owning side to null (unless already changed)
            if ($project->getUser() === $this) {
                $project->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setUser($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Community>
     */
    public function getCommunities(): Collection
    {
        return $this->communities;
    }

    public function addCommunity(Community $community): static
    {
        if (!$this->communities->contains($community)) {
            $this->communities->add($community);
            $community->setUser($this);
        }

        return $this;
    }

    public function removeCommunity(Community $community): static
    {
        if ($this->communities->removeElement($community)) {
            // set the owning side to null (unless already changed)
            if ($community->getUser() === $this) {
                $community->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Job>
     */
    public function getJobs(): Collection
    {
        return $this->jobs;
    }

    public function addJob(Job $job): static
    {
        if (!$this->jobs->contains($job)) {
            $this->jobs->add($job);
            $job->setUser($this);
        }

        return $this;
    }

    public function removeJob(Job $job): static
    {
        if ($this->jobs->removeElement($job)) {
            // set the owning side to null (unless already changed)
            if ($job->getUser() === $this) {
                $job->setUser(null);
            }
        }

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
            $communityPost->setUser($this);
        }

        return $this;
    }

    public function removeCommunityPost(CommunityPost $communityPost): static
    {
        if ($this->communityPosts->removeElement($communityPost)) {
            // set the owning side to null (unless already changed)
            if ($communityPost->getUser() === $this) {
                $communityPost->setUser(null);
            }
        }

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
            $vote->setUser($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): static
    {
        if ($this->votes->removeElement($vote)) {
            // set the owning side to null (unless already changed)
            if ($vote->getUser() === $this) {
                $vote->setUser(null);
            }
        }

        return $this;
    }

    public function followUser(User $user): self
    {
        if (!$this->followedUsers->contains($user)) {
            $this->followedUsers[] = $user;
        }

        return $this;
    }

    public function unfollowUser(User $user): self
    {
        $this->followedUsers->removeElement($user);

        return $this;
    }

    public function getFollowedUsers(): Collection
    {
        return $this->followedUsers;
    }

    public function getFollowedCommunities(): Collection
    {
        return $this->followedCommunities;
    }

    public function getFollowedProjects(): Collection
    {
        return $this->followedProjects;
    }
}

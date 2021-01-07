<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"post:read"}},
 *     denormalizationContext={"groups"={"post:write"}},
 *     attributes={
 *      "pagination_items_per_page"=10,
 *      "order"={"updatedAt": "DESC"}
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *      "category": "exact",
 *      "category.alias": "exact",
 * })
 * @ORM\Entity(repositoryClass=PostRepository::class)
 * Tester l'API :
 * - https://localhost:8000/api/posts?category=/api/categories/1&page=1
 * - https://localhost:8000/api/posts?category.alias=politique&page=1
 */
class Post
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"post:read","post:write"})
     */
    private $id;

    /**
     * @Assert\NotBlank(message="N'oubliez pas votre titre.")
     * @Assert\Length(max="255", maxMessage="Attention, pas plus de 255 caractères.")
     * @ORM\Column(type="string", length=255)
     * @Groups({"post:read","post:write"})
     */
    private $title;

    /**
     * @Assert\NotBlank(message="N'oubliez pas votre alias.")
     * @Assert\Length(max="255", maxMessage="Attention, pas plus de 255 caractères.")
     * @ORM\Column(type="string", length=255)
     * @Groups({"post:read","post:write"})
     */
    private $alias;

    /**
     * @Assert\NotBlank(message="N'oubliez pas votre contenu.")
     * @ORM\Column(type="text")
     * @Groups({"post:read","post:write"})
     */
    private $content;

    /**
     * @Assert\NotBlank(message="N'oubliez pas votre image.")
     * @Assert\Length(max="255", maxMessage="Attention, pas plus de 255 caractères.")
     * @ORM\Column(type="string", length=255)
     * @Groups({"post:read","post:write"})
     */
    private $image;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"post:read","post:write"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"post:read","post:write"})
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"post:read","post:write"})
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"post:read","post:write"})
     */
    private $category;

    /**
     * @ORM\ManyToMany(targetEntity=Tag::class, mappedBy="posts")
     * @Groups({"post:read","post:write"})
     */
    private $tags;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="post", orphanRemoval=true)
     * @Groups({"post:read"})
     */
    private $comments;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
            $tag->addPost($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removePost($this);
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
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
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }
}

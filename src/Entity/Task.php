<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Task
{
    const STATUS_TODO = 'todo';
    const STATUS_DOING = 'doing';
    const STATUS_DONE = 'done';

    const PRIORITY_LOW = 1;
    const PRIORITY_NORMAL = 2;
    const PRIORITY_HIGH = 3;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    // status of the task
    #[ORM\Column(length: 20)]
    private string $status = 'todo';

    #[ORM\Column]
    private int $priority = 2;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dueDate = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $completedAt = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TodoList $list = null;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $updatedAt = null;

    #[ORM\PreUpdate]
    public function touch(): void
    {
        // set the updated at
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the title.
     *
     * @return string|null the title
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the title.
     *
     * @param string $title the title
     *
     * @return static
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Returns the due date.
     */
    public function getDueDate(): ?\DateTime
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTime $dueDate): static
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getCompletedAt(): ?\DateTime
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTime $completedAt): static
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function getList(): ?TodoList
    {
        return $this->list;
    }

    public function setList(?TodoList $list): static
    {
        $this->list = $list;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function isDone()
    {
        return $this->status == 'done';
    }

    public function getPriorityLabel(): string
    {
        switch ($this->priority) {
            case self::PRIORITY_HIGH:
                return 'High';
            case self::PRIORITY_NORMAL:
                return 'Medium';
            default:
                return 'Low';
        }
    }

    // public function isLate()
    // {
    //     return $this->dueDate && $this->dueDate < new \DateTime('today');
    // }
}

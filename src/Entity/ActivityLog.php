<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ActivityLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $taskId;

    #[ORM\Column(length: 255)]
    private string $message;

    #[ORM\Column]
    private \DateTime $createdAt;

    public function __construct(int $taskId, string $message)
    {
        $this->taskId = $taskId;
        $this->message = $message;
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTaskId(): int
    {
        return $this->taskId;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}

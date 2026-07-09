<?php

namespace App\Entity;

use App\Repository\AuthLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuthLogRepository::class)]
#[ORM\Table(name: 'auth_logs')]
#[ORM\Index(name: 'idx_auth_logs_action', columns: ['action'])]
#[ORM\Index(name: 'idx_auth_logs_status', columns: ['status'])]
#[ORM\Index(name: 'idx_auth_logs_ip', columns: ['ip'])]
#[ORM\Index(name: 'idx_auth_logs_created_at', columns: ['created_at'])]
class AuthLog
{
    public const ACTION_LOGIN = 'login';
    public const ACTION_REGISTER = 'register';

    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 16)]
    private string $action;

    #[ORM\Column(type: Types::STRING, length: 16)]
    private string $status;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\Column(type: Types::STRING, length: 45)]
    private string $ip;

    #[ORM\Column(type: Types::STRING, length: 512, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        string $action,
        string $status,
        ?User $user,
        string $ip,
        ?string $userAgent,
        ?string $errorMessage,
    ) {
        $this->action = $action;
        $this->status = $status;
        $this->user = $user;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
        $this->errorMessage = $errorMessage;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}

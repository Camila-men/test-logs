<?php

namespace App\Service;

use App\Entity\AuthLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Единая точка записи в журнал попыток регистрации/входа
 */
class AuthLogger
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function log(
        string $action,
        string $status,
        ?User $user,
        Request $request,
        ?string $errorMessage = null,
    ): void {
        $log = new AuthLog(
            $action,
            $status,
            $user,
            $request->getClientIp() ?? '0.0.0.0',
            $request->headers->get('User-Agent'),
            $errorMessage,
        );

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}

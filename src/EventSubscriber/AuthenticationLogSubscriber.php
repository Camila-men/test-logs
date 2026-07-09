<?php

namespace App\EventSubscriber;

use App\Entity\AuthLog;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AuthLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Логирует каждую попытку входа независимо от того, какой Authenticator её обработал
 */
class AuthenticationLogSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AuthLogger $authLogger,
        private readonly UserRepository $userRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            LoginFailureEvent::class => 'onLoginFailure',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        $this->authLogger->log(
            AuthLog::ACTION_LOGIN,
            AuthLog::STATUS_SUCCESS,
            $user instanceof User ? $user : null,
            $event->getRequest(),
        );
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $user = null;
        $badge = $event->getPassport()?->getBadge(UserBadge::class);

        if ($badge instanceof UserBadge) {
            $user = $this->userRepository->findOneByEmail($badge->getUserIdentifier());
        }

        $this->authLogger->log(
            AuthLog::ACTION_LOGIN,
            AuthLog::STATUS_ERROR,
            $user,
            $event->getRequest(),
            $event->getException()->getMessage(),
        );
    }
}

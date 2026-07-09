<?php

namespace App\Controller;

use App\Entity\AuthLog;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Service\AuthLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->redirectToRoute($this->getUser() ? 'app_log_index' : 'app_login');
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        AuthLogger $authLogger,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_log_index');
        }

        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = (string) $form->get('email')->getData();
            $plainPassword = (string) $form->get('plainPassword')->getData();

            if ($userRepository->findOneByEmail($email) !== null) {
                $message = 'Пользователь с таким email уже зарегистрирован.';
                $authLogger->log(AuthLog::ACTION_REGISTER, AuthLog::STATUS_ERROR, null, $request, $message);
                $this->addFlash('error', $message);

                return $this->redirectToRoute('app_register');
            }

            $user = new User($email, '');
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            $authLogger->log(AuthLog::ACTION_REGISTER, AuthLog::STATUS_SUCCESS, $user, $request);
            $this->addFlash('success', 'Регистрация прошла успешно. Теперь можно войти.');

            return $this->redirectToRoute('app_login');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }

            $authLogger->log(
                AuthLog::ACTION_REGISTER,
                AuthLog::STATUS_ERROR,
                null,
                $request,
                implode(' ', $errors) ?: 'Форма регистрации заполнена некорректно.',
            );
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_log_index');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): never
    {
        throw new \LogicException('Перехватывается фаерволом до выполнения контроллера.');
    }
}

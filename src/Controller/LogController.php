<?php

namespace App\Controller;

use App\Entity\AuthLog;
use App\Repository\AuthLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LogController extends AbstractController
{
    private const PER_PAGE = 20;

    #[Route('/logs', name: 'app_log_index')]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, AuthLogRepository $authLogRepository): Response
    {
        $filters = [
            'action' => $request->query->get('action') ?: null,
            'status' => $request->query->get('status') ?: null,
            'userId' => $request->query->get('userId') ?: null,
            'ip' => $request->query->get('ip') ?: null,
            'userAgent' => $request->query->get('userAgent') ?: null,
            'dateFrom' => $this->parseDate($request->query->get('dateFrom')),
            'dateTo' => $this->parseDate($request->query->get('dateTo'), endOfDay: true),
        ];

        $page = max(1, (int) $request->query->get('page', 1));

        $result = $authLogRepository->findFiltered($filters, $page, self::PER_PAGE);

        $viewFilters = [
            'action' => (string) $request->query->get('action', ''),
            'status' => (string) $request->query->get('status', ''),
            'userId' => (string) $request->query->get('userId', ''),
            'ip' => (string) $request->query->get('ip', ''),
            'userAgent' => (string) $request->query->get('userAgent', ''),
            'dateFrom' => (string) $request->query->get('dateFrom', ''),
            'dateTo' => (string) $request->query->get('dateTo', ''),
        ];

        return $this->render('log/index.html.twig', [
            'logs' => $result['items'],
            'total' => $result['total'],
            'pages' => $result['pages'],
            'page' => $result['page'],
            'filters' => $viewFilters,
            'actions' => [AuthLog::ACTION_LOGIN, AuthLog::ACTION_REGISTER],
            'statuses' => [AuthLog::STATUS_SUCCESS, AuthLog::STATUS_ERROR],
        ]);
    }

    private function parseDate(?string $value, bool $endOfDay = false): ?\DateTimeImmutable
    {
        if (!$value) {
            return null;
        }

        try {
            $date = new \DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }

        return $endOfDay ? $date->setTime(23, 59, 59) : $date->setTime(0, 0, 0);
    }
}

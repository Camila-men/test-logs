<?php

namespace App\Repository;

use App\Entity\AuthLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AuthLog>
 */
class AuthLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthLog::class);
    }

    /**
     * Возвращает отфильтрованную и постранично разбитую выборку логов
     *
     * @param array<string, mixed> $filters
     *
     * @return array{items: AuthLog[], total: int, pages: int, page: int}
     */
    public function findFiltered(array $filters, int $page, int $limit): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);

        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.user', 'u')
            ->addSelect('u')
            ->orderBy('l.createdAt', 'DESC');

        if (!empty($filters['action'])) {
            $qb->andWhere('l.action = :action')->setParameter('action', $filters['action']);
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('l.status = :status')->setParameter('status', $filters['status']);
        }

        if (!empty($filters['userId'])) {
            $qb->andWhere('IDENTITY(l.user) = :userId')->setParameter('userId', $filters['userId']);
        }

        if (!empty($filters['ip'])) {
            $qb->andWhere('l.ip LIKE :ip')->setParameter('ip', '%'.$filters['ip'].'%');
        }

        if (!empty($filters['userAgent'])) {
            $qb->andWhere('l.userAgent LIKE :userAgent')->setParameter('userAgent', '%'.$filters['userAgent'].'%');
        }

        if (!empty($filters['dateFrom'])) {
            $qb->andWhere('l.createdAt >= :dateFrom')->setParameter('dateFrom', $filters['dateFrom']);
        }

        if (!empty($filters['dateTo'])) {
            $qb->andWhere('l.createdAt <= :dateTo')->setParameter('dateTo', $filters['dateTo']);
        }

        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($qb, fetchJoinCollection: false);
        $total = count($paginator);

        return [
            'items' => iterator_to_array($paginator->getIterator()),
            'total' => $total,
            'pages' => (int) max(1, ceil($total / $limit)),
            'page' => $page,
        ];
    }
}

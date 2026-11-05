<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @return Task[]
     */
    public function findByListAndStatus(int $listId, string $status): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('IDENTITY(t.list) = :list')
            ->andWhere('t.status = :status')
            ->setParameter('list', $listId)
            ->setParameter('status', $status)
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

<?php

namespace App\Repository;

use App\Entity\TodoList;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends ServiceEntityRepository<TodoList>
 */
class TodoListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TodoList::class);
    }

    /**
     * Search lists.
     *
     * @return TodoList[]
     */
    public function search(Request $request, User $user): array
    {
        $q = $request->query->get('q', '');

        // search by name
        return $this->getEntityManager()
            ->createQuery("SELECT l FROM App\\Entity\\TodoList l WHERE l.owner = :owner AND l.name LIKE '%".$q."%' ORDER BY l.name ASC")
            ->setParameter('owner', $user)
            ->getResult();
    }
}

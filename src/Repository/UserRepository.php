<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private RequestStack $requestStack)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Get the current user from the session.
     */
    public function findCurrent(): ?User
    {
        $userId = $this->requestStack->getSession()->get('user_id');
        if (!$userId) {
            return null;
        }

        return $this->find($userId);
    }
}

<?php

namespace App\EventListener;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Task::class)]
class TaskActivityListener
{
    public function preUpdate(Task $task, PreUpdateEventArgs $args): void
    {
        if ($args->hasChangedField('status')) {
            $args->getObjectManager()->getConnection()->insert('activity_log', [
                'task_id' => $task->getId(),
                'message' => sprintf('Status changed from %s to %s', $args->getOldValue('status'), $args->getNewValue('status')),
                'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ]);
        }
    }
}

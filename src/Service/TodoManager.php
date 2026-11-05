<?php

namespace App\Service;

use App\Entity\Task;
use App\Entity\TodoList;
use App\Util\Helper;
use App\Service\AppSettings;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class TodoManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
    ) {
    }

    // validates the data
    public function validate(array $data)
    {
        $errors = [];
        if (!isset($data['title']) || trim($data['title']) == '') {
            $errors[] = 'Title is required';
        }
        if (isset($data['title']) && strlen($data['title']) > 255) {
            $errors[] = 'Title is too long';
        }
        if (isset($data['priority']) && !in_array($data['priority'], [1, 2, 3])) {
            $errors[] = 'Invalid priority';
        }
        if (isset($data['dueDate']) && $data['dueDate'] != '' && !\DateTime::createFromFormat('Y-m-d', $data['dueDate'])) {
            $errors[] = 'Invalid due date';
        }

        return $errors;
    }

    public function createTask(array &$data, TodoList $list)
    {
        $task = new Task();
        $task->setTitle(trim($data['title']));
        $task->setDescription($data['description'] ?? null);
        // priority defaults to low
        $task->setPriority(isset($data['priority']) ? (int) $data['priority'] : 2);
        if (!empty($data['dueDate'])) {
            $task->setDueDate(new \DateTime($data['dueDate']));
        }
        $task->setStatus('todo');
        $task->setCreatedAt(new \DateTime());
        $list->addTask($task);

        $this->em->persist($task);
        $this->em->flush();

        $data['id'] = $task->getId();

        $this->logger->info('Task created: '.$task->getTitle().' ('.Helper::priorityLabel($task->getPriority()).')');

        // 2025-03-02: only notify when enabled (was always notifying before)
        if ($task->getPriority() == 3 && AppSettings::getInstance()->get('notify_high_priority')) {
            $this->sendHighPriorityNotification($task);
        }

        return $task;
    }

    public function sendHighPriorityNotification(Task $task)
    {
        // TODO: send a real email
        $this->logger->warning('High priority task created in list '.$task->getList()->getName().': '.$task->getTitle());
    }

    public function getStats(TodoList $list)
    {
        $stats = ['todo' => 0, 'doing' => 0, 'done' => 0, 'overdue' => 0];
        foreach ($list->getTasks() as $task) {
            $stats[$task->getStatus()]++;
            if (Helper::isOverdue($task)) {
                $stats['overdue']++;
            }
        }

        return $stats;
    }
}

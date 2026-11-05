<?php

namespace App\Controller\Api;

use App\Entity\Task;
use App\Entity\TodoList;
use App\Entity\User;
use App\Service\TodoManager;
use App\Util\Helper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class TaskApiController extends AbstractController
{
    #[Route('/lists/{id}/tasks', name: 'api_task_list', methods: ['GET'])]
    public function list(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->find((int) $request->headers->get('X-User-Id'));
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $list = $em->getRepository(TodoList::class)->find($id);
        if (!$list || $list->getOwner()->getId() != $user->getId()) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        $result = [];
        foreach ($list->getTasks() as $task) {
            $result[] = [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus(),
                'priority' => $task->getPriority(),
                'priority_label' => Helper::priorityLabel($task->getPriority()),
                'due_date' => $task->getDueDate() ? $task->getDueDate()->format('Y-m-d') : null,
                'completed_at' => $task->getCompletedAt() ? $task->getCompletedAt()->format(\DATE_ATOM) : null,
                'overdue' => Helper::isOverdue($task),
            ];
        }

        return new JsonResponse(['data' => $result]);
    }

    #[Route('/lists/{id}/tasks', name: 'api_task_create', methods: ['POST'])]
    public function create(int $id, Request $request, EntityManagerInterface $em, TodoManager $manager): JsonResponse
    {
        $user = $em->getRepository(User::class)->find((int) $request->headers->get('X-User-Id'));
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $list = $em->getRepository(TodoList::class)->find($id);
        if (!$list || $list->getOwner()->getId() != $user->getId()) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $errors = $manager->validate($data);
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => $errors], 422);
        }

        $task = $manager->createTask($data, $list);

        return new JsonResponse(['data' => [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus(),
            'priority' => $task->getPriority(),
            'priority_label' => Helper::priorityLabel($task->getPriority()),
            'due_date' => $task->getDueDate() ? $task->getDueDate()->format('Y-m-d') : null,
            'completed_at' => $task->getCompletedAt() ? $task->getCompletedAt()->format(\DATE_ATOM) : null,
            'overdue' => Helper::isOverdue($task),
        ]], 201);
    }

    #[Route('/tasks/{id}', name: 'api_task_update', methods: ['PATCH'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->find((int) $request->headers->get('X-User-Id'));
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $task = $em->getRepository(Task::class)->find($id);
        if (!$task || $task->getList()->getOwner()->getId() != $user->getId()) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        // update the fields that were sent
        if (isset($data['title'])) {
            $task->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $task->setDescription($data['description']);
        }
        if (isset($data['priority'])) {
            $task->setPriority((int) $data['priority']);
        }
        if (isset($data['dueDate'])) {
            $task->setDueDate(new \DateTime($data['dueDate']));
        }
        if (isset($data['status'])) {
            if ($data['status'] == 'done' && $task->getStatus() != 'done') {
                $task->setCompletedAt(new \DateTime());
            }
            $task->setStatus($data['status']);
        }

        $em->flush();

        return new JsonResponse(['data' => [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus(),
            'priority' => $task->getPriority(),
            'priority_label' => Helper::priorityLabel($task->getPriority()),
            'due_date' => $task->getDueDate() ? $task->getDueDate()->format('Y-m-d') : null,
            'completed_at' => $task->getCompletedAt() ? $task->getCompletedAt()->format(\DATE_ATOM) : null,
            'overdue' => Helper::isOverdue($task),
        ]]);
    }
}

<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\TodoList;
use App\Entity\User;
use App\Service\TodoManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TaskController extends AbstractController
{
    #[Route('/lists/{id}/tasks', name: 'task_create', methods: ['POST'])]
    public function create(int $id, Request $request, EntityManagerInterface $em, TodoManager $manager): Response
    {
        $userId = $request->getSession()->get('user_id');
        $user = $userId ? $em->getRepository(User::class)->find($userId) : null;
        if (!$user) {
            return $this->redirectToRoute('dashboard');
        }

        $list = $em->getRepository(TodoList::class)->find($id);
        if (!$list || $list->getOwner()->getId() != $user->getId()) {
            throw $this->createNotFoundException('List not found');
        }

        $data = $request->request->all();
        $errors = $manager->validate($data);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error);
            }

            return $this->redirectToRoute('list_show', ['id' => $id]);
        }

        $manager->createTask($data, $list);
        // $this->addFlash('info', 'Task '.$data['title'].' was added to '.$list->getName());
        // $em->refresh($list);
        $this->addFlash('success', 'Task added');

        return $this->redirectToRoute('list_show', ['id' => $id]);
    }

    #[Route('/tasks/{id}/edit', name: 'task_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $userId = $request->getSession()->get('user_id');
        $user = $userId ? $em->getRepository(User::class)->find($userId) : null;
        if (!$user) {
            return $this->redirectToRoute('dashboard');
        }

        $task = $em->getRepository(Task::class)->find($id);
        if (!$task || $task->getList()->getOwner()->getId() != $user->getId()) {
            throw $this->createNotFoundException('Task not found');
        }

        $errors = [];
        if ($request->isMethod('POST')) {
            $title = trim((string) $request->request->get('title'));
            if ($title == '') {
                $errors[] = 'Title is required';
            } elseif (strlen($title) > 200) {
                $errors[] = 'Title is too long';
            }
            $priority = (int) $request->request->get('priority', 2);
            if ($priority < 1 || $priority > 3) {
                $errors[] = 'Invalid priority';
            }
            $status = $request->request->get('status', 'todo');
            if (!in_array($status, ['todo', 'doing', 'done'])) {
                $errors[] = 'Invalid status';
            }
            $dueDate = $request->request->get('dueDate');

            if (count($errors) == 0) {
                $task->setTitle($title);
                $task->setDescription($request->request->get('description'));
                $task->setPriority($priority);
                $task->setDueDate($dueDate ? new \DateTime($dueDate) : null);
                if ($status == 'done' && $task->getStatus() != 'done') {
                    $task->setCompletedAt(new \DateTime());
                }
                $task->setStatus($status);
                $em->flush();

                $this->addFlash('success', 'Task updated');

                return $this->redirectToRoute('list_show', ['id' => $task->getList()->getId()]);
            }
        }

        return $this->render('task/edit.html.twig', [
            'user' => $user,
            'task' => $task,
            'errors' => $errors,
        ]);
    }

    #[Route('/tasks/{id}/toggle', name: 'task_toggle', methods: ['POST'])]
    public function toggle(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $userId = $request->getSession()->get('user_id');
        $user = $userId ? $em->getRepository(User::class)->find($userId) : null;
        if (!$user) {
            return $this->redirectToRoute('dashboard');
        }

        $task = $em->getRepository(Task::class)->find($id);
        if (!$task || $task->getList()->getOwner()->getId() != $user->getId()) {
            throw $this->createNotFoundException('Task not found');
        }

        if ($task->getStatus() == Task::STATUS_DONE) {
            $task->setStatus(Task::STATUS_TODO);
            $task->setCompletedAt(null);
        } else {
            $task->setStatus(Task::STATUS_DONE);
            $task->setCompletedAt(new \DateTime());
        }
        $em->flush();

        return $this->redirectToRoute('list_show', ['id' => $task->getList()->getId()]);
    }

    #[Route('/tasks/{id}/delete', name: 'task_delete')]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $task = $em->getRepository(Task::class)->find($id);
        if (!$task) {
            throw $this->createNotFoundException('Task not found');
        }
        $listId = $task->getList()->getId();
        $em->remove($task);
        $em->flush();

        return $this->redirectToRoute('list_show', ['id' => $listId]);
    }
}

<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\TodoList;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Service\Sort\SortStrategyFactory;
use App\Service\TodoManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ListController extends AbstractController
{
    #[Route('/lists', name: 'list_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $userId = $request->getSession()->get('user_id');
        $user = $userId ? $em->getRepository(User::class)->find($userId) : null;
        if (!$user) {
            return $this->redirectToRoute('dashboard');
        }

        $name = trim((string) $request->request->get('name'));
        if ($name == '') {
            $this->addFlash('error', 'Name is required');

            return $this->redirectToRoute('dashboard');
        }
        if (strlen($name) > 255) {
            $this->addFlash('error', 'Name is too long');

            return $this->redirectToRoute('dashboard');
        }

        $list = new TodoList();
        $list->setName($name);
        $list->setOwner($user);
        $list->setCreatedAt(new \DateTime());
        $em->persist($list);
        $em->flush();

        $this->addFlash('success', 'List created');

        return $this->redirectToRoute('list_show', ['id' => $list->getId()]);
    }

    #[Route('/lists/{id}', name: 'list_show', methods: ['GET'])]
    public function show(int $id, Request $request, EntityManagerInterface $em, TodoManager $manager): Response
    {
        $userId = $request->getSession()->get('user_id');
        $user = $userId ? $em->getRepository(User::class)->find($userId) : null;
        if (!$user) {
            return $this->redirectToRoute('dashboard');
        }

        $list = $em->getRepository(TodoList::class)->find($id);
        if (!$list) {
            throw $this->createNotFoundException('List not found');
        }

        $status = $request->query->get('status');
        if ($status) {
            $ids = $em->getConnection()->executeQuery("SELECT id FROM task WHERE list_id = ".$id." AND status = '".$status."' ORDER BY id")->fetchFirstColumn();
            $tasks = [];
            foreach ($ids as $taskId) {
                $tasks[] = $em->getRepository(Task::class)->find($taskId);
            }
        } else {
            $tasks = $list->getTasks()->toArray();
        }

        $sort = $request->query->get('sort', $list->getSetting('sort'));
        $tasks = SortStrategyFactory::create($sort)->sort($tasks, $sort ?? 'id');

        return $this->render('list/show.html.twig', [
            'user' => $user,
            'list' => $list,
            'tasks' => $tasks,
            'stats' => $manager->getStats($list),
            'status' => $status,
            'sort' => $sort,
        ]);
    }

    #[Route('/lists/{id}/complete-all', name: 'list_complete_all', methods: ['POST'])]
    public function completeAll(int $id, Request $request, EntityManagerInterface $em, TaskRepository $taskRepository): Response
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

        $count = $taskRepository->completeAllInList($id);
        $this->addFlash('success', $count.' tasks completed');

        return $this->redirectToRoute('list_show', ['id' => $id]);
    }

    #[Route('/lists/{id}/delete', name: 'list_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, EntityManagerInterface $em): Response
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

        $em->remove($list);
        $em->flush();
        $this->addFlash('success', 'List deleted');

        return $this->redirectToRoute('dashboard');
    }
}

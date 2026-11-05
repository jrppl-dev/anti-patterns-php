<?php

namespace App\Controller;

use App\Entity\TodoList;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $userId = $request->getSession()->get('user_id');
        $user = $userId ? $em->getRepository(User::class)->find($userId) : null;
        if (!$user) {
            return $this->render('dashboard/choose_user.html.twig', [
                'users' => $em->getRepository(User::class)->findAll(),
            ]);
        }

        $lists = $em->getRepository(TodoList::class)->findBy(['owner' => $user], ['name' => 'ASC']);

        $conn = $em->getConnection();
        $counts = [];
        foreach ($lists as $list) {
            $rows = $conn->executeQuery('SELECT status, COUNT(*) AS c FROM task WHERE list_id = '.$list->getId().' GROUP BY status')->fetchAllAssociative();
            $counts[$list->getId()] = ['todo' => 0, 'doing' => 0, 'done' => 0];
            foreach ($rows as $row) {
                $counts[$list->getId()][$row['status']] = (int) $row['c'];
            }
        }

        // get the monday of this week
        $monday = new \DateTime('monday this week');
        $completedThisWeek = (int) $conn->executeQuery(
            'SELECT COUNT(*) FROM task t INNER JOIN todo_list l ON l.id = t.list_id WHERE l.owner_id = ? AND t.completed_at >= ?',
            [$user->getId(), $monday->format('Y-m-d 00:00:00')]
        )->fetchOne();

        return $this->render('dashboard/index.html.twig', [
            'user' => $user,
            'lists' => $lists,
            'counts' => $counts,
            'completedThisWeek' => $completedThisWeek,
        ]);
    }

    #[Route('/switch-user/{id}', name: 'switch_user')]
    public function switchUser(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException();
        }
        $request->getSession()->set('user_id', $user->getId());

        return $this->redirectToRoute('dashboard');
    }

    #[Route('/logout', name: 'logout')]
    public function logout(Request $request): Response
    {
        $request->getSession()->remove('user_id');

        return $this->redirectToRoute('dashboard');
    }
}

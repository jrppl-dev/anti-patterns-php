<?php

namespace App\Tests;

use App\Entity\Task;
use App\Entity\TodoList;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AppWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $conn = $this->em->getConnection();
        foreach (['activity_log', 'task', 'todo_list', 'app_user'] as $table) {
            $conn->executeStatement('DELETE FROM '.$table);
        }
    }

    protected function createUser(string $name): User
    {
        $user = (new User())->setName($name)->setEmail(strtolower($name).'@example.com');
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    protected function createList(User $owner, string $name = 'Groceries'): TodoList
    {
        $list = (new TodoList())->setName($name)->setOwner($owner)->setCreatedAt(new \DateTime());
        $this->em->persist($list);
        $this->em->flush();

        return $list;
    }

    protected function createTask(TodoList $list, string $title = 'Milk', string $status = 'todo'): Task
    {
        $task = (new Task())->setTitle($title)->setStatus($status)->setCreatedAt(new \DateTime());
        if ('done' === $status) {
            $task->setCompletedAt(new \DateTime());
        }
        $list->addTask($task);
        $this->em->persist($task);
        $this->em->flush();

        return $task;
    }

    protected function loginAs(User $user): void
    {
        $this->client->request('GET', '/switch-user/'.$user->getId());
    }

    protected function apiRequest(string $method, string $uri, User $user, ?array $body = null): array
    {
        $this->client->request($method, $uri, [], [], [
            'HTTP_X_USER_ID' => (string) $user->getId(),
            'CONTENT_TYPE' => 'application/json',
        ], null === $body ? null : json_encode($body));

        return json_decode($this->client->getResponse()->getContent(), true) ?? [];
    }
}

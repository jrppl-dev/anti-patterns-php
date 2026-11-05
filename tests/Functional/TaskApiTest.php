<?php

namespace App\Tests\Functional;

use App\Tests\AppWebTestCase;

class TaskApiTest extends AppWebTestCase
{
    public function testListsTasks(): void
    {
        $alice = $this->createUser('Alice');
        $list = $this->createList($alice);
        $this->createTask($list, 'Milk');

        $body = $this->apiRequest('GET', '/api/lists/'.$list->getId().'/tasks', $alice);

        $this->assertResponseIsSuccessful();
        $this->assertSame('Milk', $body['data'][0]['title']);
    }

    public function testCreatesTask(): void
    {
        $alice = $this->createUser('Alice');
        $list = $this->createList($alice);

        $body = $this->apiRequest('POST', '/api/lists/'.$list->getId().'/tasks', $alice, ['title' => 'Bread', 'priority' => 3]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertSame('High', $body['data']['priority_label']);
    }

    public function testRejectsTaskWithoutTitle(): void
    {
        $alice = $this->createUser('Alice');
        $list = $this->createList($alice);

        $this->apiRequest('POST', '/api/lists/'.$list->getId().'/tasks', $alice, ['title' => '']);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testOtherUsersListIsNotFound(): void
    {
        $alice = $this->createUser('Alice');
        $bob = $this->createUser('Bob');
        $list = $this->createList($alice);

        $this->apiRequest('GET', '/api/lists/'.$list->getId().'/tasks', $bob);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdatesTitle(): void
    {
        $alice = $this->createUser('Alice');
        $task = $this->createTask($this->createList($alice), 'Milk');

        $body = $this->apiRequest('PATCH', '/api/tasks/'.$task->getId(), $alice, ['title' => 'Oat milk']);

        $this->assertResponseIsSuccessful();
        $this->assertSame('Oat milk', $body['data']['title']);
    }
}

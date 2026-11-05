<?php

namespace App\Tests\Functional;

use App\Entity\Task;
use App\Tests\AppWebTestCase;

class ListFeaturesTest extends AppWebTestCase
{
    public function testSortsTasksByTitle(): void
    {
        $alice = $this->createUser('Alice');
        $list = $this->createList($alice);
        $this->createTask($list, 'Milk');
        $this->createTask($list, 'Bread');
        $this->loginAs($alice);

        $crawler = $this->client->request('GET', '/lists/'.$list->getId().'?sort=title');

        $this->assertStringContainsString('Bread', $crawler->filter('tbody tr')->first()->text());
    }

    public function testCompleteAllMarksTasksDone(): void
    {
        $alice = $this->createUser('Alice');
        $list = $this->createList($alice);
        $task = $this->createTask($list, 'Milk');
        $this->loginAs($alice);

        $this->client->request('POST', '/lists/'.$list->getId().'/complete-all');

        $this->em->clear();
        $this->assertSame('done', $this->em->find(Task::class, $task->getId())->getStatus());
    }

    public function testSearchesListsByName(): void
    {
        $alice = $this->createUser('Alice');
        $this->createList($alice, 'Groceries');
        $this->createList($alice, 'Holidays');
        $this->loginAs($alice);

        $crawler = $this->client->request('GET', '/?q=Holi');

        $this->assertStringNotContainsString('Groceries', $crawler->filter('table')->text());
        $this->assertStringContainsString('Holidays', $crawler->filter('table')->text());
    }

    public function testSnoozeMovesTheDueDate(): void
    {
        $alice = $this->createUser('Alice');
        $task = $this->createTask($this->createList($alice), 'Milk');
        $task->setDueDate(new \DateTime('2026-01-10'));
        $this->em->flush();

        $body = $this->apiRequest('POST', '/api/tasks/'.$task->getId().'/snooze', $alice);

        $this->assertSame('2026-01-11', $body['data']['due_date']);
    }
}

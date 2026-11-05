<?php

namespace App\Tests\Functional;

use App\Entity\Task;
use App\Tests\AppWebTestCase;

class TaskToggleTest extends AppWebTestCase
{
    public function testToggleMarksTaskDone(): void
    {
        $alice = $this->createUser('Alice');
        $task = $this->createTask($this->createList($alice));
        $this->loginAs($alice);

        $this->client->request('POST', '/tasks/'.$task->getId().'/toggle');

        $this->assertResponseRedirects();
        $this->em->clear();
        $this->assertSame('done', $this->em->find(Task::class, $task->getId())->getStatus());
    }
}

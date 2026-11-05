<?php

namespace App\Tests\Unit;

use App\Entity\Task;
use App\Entity\TodoList;
use App\Service\TodoManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TodoManagerTest extends TestCase
{
    public function testCreateTaskPersistsAndFlushes(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')->with($this->isInstanceOf(Task::class));
        $em->expects($this->once())->method('flush');
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('info');

        $manager = new TodoManager($em, $logger);
        $data = ['title' => 'Milk', 'priority' => 1];
        $task = $manager->createTask($data, new TodoList());

        $this->assertSame('Milk', $task->getTitle());
    }

    public function testValidateRequiresTitle(): void
    {
        $manager = new TodoManager($this->createStub(EntityManagerInterface::class), $this->createStub(LoggerInterface::class));

        $this->assertContains('Title is required', $manager->validate(['title' => '']));
    }
}

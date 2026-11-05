<?php

namespace App\Command;

use App\Entity\Task;
use App\Entity\TodoList;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:seed', description: 'Load demo users, lists and tasks')]
class SeedCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach (['Alice' => 'alice@example.com', 'Bob' => 'bob@example.com'] as $name => $email) {
            $user = (new User())->setName($name)->setEmail($email);
            $this->em->persist($user);

            $list = (new TodoList())->setName($name."'s groceries")->setOwner($user)->setCreatedAt(new \DateTime());
            $this->em->persist($list);

            foreach ([['Milk', 1, '+1 day'], ['Bread', 2, 'today'], ['Coffee', 3, '-2 days']] as [$title, $priority, $due]) {
                $task = (new Task())->setTitle($title)->setPriority($priority)
                    ->setDueDate(new \DateTime($due))->setCreatedAt(new \DateTime());
                $list->addTask($task);
                $this->em->persist($task);
            }
        }
        $this->em->flush();
        $output->writeln('Seeded.');

        return Command::SUCCESS;
    }
}

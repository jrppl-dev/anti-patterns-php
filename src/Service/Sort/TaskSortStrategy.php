<?php

namespace App\Service\Sort;

/**
 * Strategy to sort tasks.
 */
class TaskSortStrategy implements SortStrategyInterface
{
    public function sort(array $tasks, string $type): array
    {
        switch ($type) {
            case 'priority':
                usort($tasks, function ($a, $b) {
                    return $b->getPriority() - $a->getPriority();
                });
                break;
            case 'due':
                usort($tasks, function ($a, $b) {
                    if ($a->getDueDate() == null) {
                        return 1;
                    }
                    if ($b->getDueDate() == null) {
                        return -1;
                    }

                    return $a->getDueDate() <=> $b->getDueDate();
                });
                break;
            case 'title':
                usort($tasks, function ($a, $b) {
                    return strcmp($a->getTitle(), $b->getTitle());
                });
                break;
            default:
                usort($tasks, function ($a, $b) {
                    return $a->getId() - $b->getId();
                });
        }

        return $tasks;
    }
}

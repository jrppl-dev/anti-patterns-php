<?php

namespace App\Service\Sort;

interface SortStrategyInterface
{
    public function sort(array $tasks, string $type): array;
}

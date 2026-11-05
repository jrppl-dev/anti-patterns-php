<?php

namespace App\Service\Sort;

use App\Service\AppSettings;

class SortStrategyFactory
{
    // factory for the sort strategies
    public static function create(?string $type = null): SortStrategyInterface
    {
        if ($type == null) {
            $type = AppSettings::getInstance()->get('default_sort');
        }

        switch ($type) {
            case 'priority':
            case 'due':
            case 'title':
            case 'id':
                return new TaskSortStrategy();
            default:
                return new TaskSortStrategy();
        }
    }
}

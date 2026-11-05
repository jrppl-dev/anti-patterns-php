<?php

namespace App\Util;

use App\Entity\Task;

class Helper
{
    public static function priorityLabel($p)
    {
        if ($p == 3) {
            return 'High';
        } elseif ($p == 2) {
            return 'Normal';
        } elseif ($p == 1) {
            return 'Low';
        }
        return 'Unknown';
    }

    public static function formatDate($d)
    {
        if (!$d) {
            return '';
        }
        return $d->format('d/m/Y');
    }

    // check if task is overdue
    public static function isOverdue(Task $task)
    {
        if ($task->getDueDate() == null) {
            return false;
        }
        if ($task->getStatus() == 'done') {
            return false;
        }
        return $task->getDueDate() < new \DateTime();
    }

    public static function slugify($text)
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }
}

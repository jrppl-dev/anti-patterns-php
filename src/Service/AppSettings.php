<?php

namespace App\Service;

class AppSettings
{
    private static $instance = null;

    private $settings = [
        'week_start' => 'monday',
        'default_sort' => 'id',
        'notify_high_priority' => true,
    ];

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new AppSettings();
        }

        return self::$instance;
    }

    public function get($key)
    {
        return $this->settings[$key] ?? null;
    }

    public function set($key, $value)
    {
        $this->settings[$key] = $value;
    }
}

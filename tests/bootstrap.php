<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// Fresh test database from the migrations, so a missing migration fails the suite.
@unlink(dirname(__DIR__).'/var/data_test.db');
passthru(sprintf('php %s/bin/console doctrine:migrations:migrate --env=test -n -q', dirname(__DIR__)), $code);
if (0 !== $code) {
    exit($code);
}

<?php

declare(strict_types=1);

$autoloadCandidates = [
    __DIR__.'/../vendor/autoload.php',
    __DIR__.'/../../../../vendor/autoload.php',
];

foreach ($autoloadCandidates as $autoloadPath) {
    if (is_file($autoloadPath)) {
        require_once $autoloadPath;
        require_once __DIR__.'/TestCase.php';

        return;
    }
}

throw new RuntimeException('Unable to locate Composer autoload.php for package tests.');

<?php

declare(strict_types=1);

use Medas\ConsolePrompts\ConsolePromptsPackage;
use Medas\ServiceManager\{ServiceConfig, ServiceManager};

chdir(__DIR__);

new ServiceManager(function (): ServiceConfig {
    $config = new ServiceConfig();

    $config->addPackages([
        ConsolePromptsPackage::instance(),
    ]);

    return $config;
});

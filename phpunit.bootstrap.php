<?php

declare(strict_types=1);

use Medas\ConsolePrinter\ConsolePrinter;
use Medas\ConsolePrinter\ConsolePrinterPackage;
use Medas\ConsolePrinter\Tables\TablePrinter;
use Medas\ConsolePrompts\ConsolePromptsPackage;
use Medas\ServiceManager\{ServiceConfig, ServiceManager};

chdir(__DIR__);

new ServiceManager(function (): ServiceConfig {
    $config = new ServiceConfig();

    $config->addPackages([
        ConsolePromptsPackage::instance(),
        ConsolePrinterPackage::instance(),
    ]);

    $config->addManualBinding(ConsolePrinter::class, 'nullGlyph', '-');
    $config->addManualBinding(TablePrinter::class, 'nullGlyph', '-');

    return $config;
});

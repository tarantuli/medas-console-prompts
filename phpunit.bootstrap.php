<?php

declare(strict_types=1);

use Medas\ConsolePrinter\{ConsolePrinter, ConsolePrinterPackage, Tables\TablePrinter};
use Medas\ConsolePrompts\ConsolePromptsPackage;
use Medas\ObjectInstantiator\ObjectInstantiator;
use Medas\ServiceManager\{ServiceConfigBuilder, ServiceManager};

chdir(__DIR__);

new ServiceManager(function (): ServiceConfigBuilder {
    $config = new ServiceConfigBuilder(ObjectInstantiator::class);

    $config->addPackages([
        ConsolePromptsPackage::instance(),
        ConsolePrinterPackage::instance(),
    ]);

    $config->addManualBinding(ConsolePrinter::class, 'nullGlyph', '-');
    $config->addManualBinding(TablePrinter::class, 'nullGlyph', '-');

    return $config;
});

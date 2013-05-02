<?php

$applicationRoot = __DIR__ . '/../../../../../';

chdir($applicationRoot);

$loader = include 'vendor/superdweebie/fastloader/get_loader.php';
$loader->add('Sds\\DoctrineExtensionsModule\\Test', __DIR__ . '/../');

// Run the application!
Zend\Mvc\Application::init(require __DIR__ . '/performance.application.config.php')->run();

include 'vendor/superdweebie/fastloader/update_classmap.php';
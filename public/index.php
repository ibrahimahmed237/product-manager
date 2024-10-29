<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Application;

// Initialize and run the application
(new Application())->run();
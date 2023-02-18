<?php
require_once __DIR__ . '/bootstrap.php';


try {
    \EasySub\Task\Daemon::start();
} catch (Exception $e) {
    echo $e->getMessage();
    echo $e->getTraceAsString();
}

<?php
require_once __DIR__ . '/bootstrap.php';


try {
    \EasySub\Task\Command::runScan();
} catch (Exception $e) {
    echo $e->getMessage();
    echo $e->getTraceAsString();
}

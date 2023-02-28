<?php
require_once __DIR__ . '/bootstrap.php';


try {
    \EasySub\Tools\Language::writeCodeFile();
} catch (Exception $e) {
    echo $e->getMessage();
    echo $e->getTraceAsString();
}

<?php
require_once __DIR__ . '/bootstrap.php';


try {
    $fileInfo = simplexml_load_string(file_get_contents(BASE_APP_PATH . '/run/movie.nfo'));

    echo print_r($fileInfo,true);
} catch (Exception $e) {
    echo $e->getMessage();
    echo $e->getTraceAsString();
}

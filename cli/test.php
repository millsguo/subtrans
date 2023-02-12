<?php
// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(__DIR__ . '/../'));

require_once APPLICATION_PATH . '/cli/bootstrap.php';


try {
    echo \EasySub\CheckSub::getLanguageTagFromSubFilename('Tulsa.King.S01E05.Token.Joe.2160p.AMZN.WEB-DL.DDP5.1.H.265-NTb.ass') . "\r\n";
    echo \EasySub\CheckSub::getLanguageTagFromSubFilename('Tulsa King - S01E04 - Visitation Place WEBDL-2160p.eng.srt') . "\r\n";
} catch (Exception $e) {
    echo $e->getMessage();
    echo $e->getTraceAsString();
}

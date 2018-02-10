<?php

/**
************************
** RUN WEEKLY 0 0 * * 0
** Removes old cache files
************************
**/

if (php_sapi_name() !== 'cli') {
    exit;
}

require __DIR__ . '/bootstrap.php';

use App\RssDownloader;

$downloader = new RssDownloader();

$downloader->deleteCache("weekly");
$downloader->deleteCache("daily");


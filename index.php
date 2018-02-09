<?php

require __DIR__ . '/bootstrap.php';

use App\Db;
use App\RssDownloader;
use App\Article;
use App\App;

if (!isset($_GET['debug'])) {
    exit;
}

if ($_GET['debug'] != App::config("debug.key")) {
    exit;
}
App::config('debug', true);

function reply ($str) {
    echo $str . "<br>";
}

if (isset($_GET['cache']) && !empty($_GET['cache'])) {
    die(file_get_contents((new RssDownloader())->getCachePath($_GET['cache'])));
}

$chatId = App::config("debug.chatid");
$url = "http://www.ejercitos.org/feed/";


 $db = new Db($chatId);
$rssDownloader = new RssDownloader();

reply("Let me check what i've got for you... May take a while");

foreach ($db->list() as $url => $date) {
    reply("****************************************************");
    reply("Getting articles from " . $url);
    reply("****************************************************");

    foreach ($rssDownloader->getTodayArticlesFrom($url) as $item) {

        reply("- " . $item["title"]);

        $article = new Article($item["url"]);

        if ($article->save($item["html"])) {
            $link = $article->getPublicPath();
            reply('<a target="_blank" href="' . $link . '">' . $link . '</a> - [<a target="_blank" href="?debug=' . App::config("debug.key") . '&cache=' . $item["url"] . '">original</a>]<br>');
        }
    }
}

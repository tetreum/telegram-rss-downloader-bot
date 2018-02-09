<?php

ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

define('APP_ROOT', __DIR__ . DIRECTORY_SEPARATOR);

return [
    "bot.key" => "YOUR_TELEGRAM_BOT_KEY",
    "bot.nick" => "YOUR_TELEGRAM_BOT_NICK",
    "bot.url" => "https://were_this_project_lays.com/", // MUST BE HTTPS
    "bot.hook" => "https:///were_this_project_lays.com/hook.php", // MUST BE HTTPS
    "db.folder" => ".db",
    "articles.folder" => "articles",
    "cache.folder" => "cache",
    "cache" => true,
    "debug" => false,
    "debug.key" => "CHANGE_THIS_VALUE" // lets you run debugging code on browser
    "debug.chatid" => 999999 // place your chatid so debug code can recover your feed db
];



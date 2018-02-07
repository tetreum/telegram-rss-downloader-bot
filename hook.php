<?php

require __DIR__ . '/bootstrap.php';

use App\App;

$commandsPaths = [
    __DIR__ . '/Commands/',
];

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram(App::config("bot.key"), App::config("bot.nick"));

    $telegram->addCommandsPaths($commandsPaths);
    $telegram->enableLimiter();

    // Handle telegram webhook request
    $telegram->handle();
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // Silence is golden!
    // log telegram errors
    echo $e->getMessage();
}

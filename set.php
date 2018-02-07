<?php
exit;
require __DIR__ . '/bootstrap.php';

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram(App::config("bot.key"), App::config("bot.nick"));

    // Set webhook
    $result = $telegram->setWebhook(App::config("bot.hook"));
    if ($result->isOk()) {
        echo $result->getDescription();
    }
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // log telegram errors
     echo $e->getMessage();
}


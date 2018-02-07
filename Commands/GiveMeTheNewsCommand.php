<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;
use App\RssDownloader;
use App\Db;
use App\Article;

/**
 * User "/help" command
 *
 * Command that lists all available commands and displays them in User and Admin sections.
 */
class GiveMeTheNewsCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'givemethenews';

    /**
     * @var string
     */
    protected $description = 'Starts checking and sending you latests news from your feeds';

    /**
     * @var string
     */
    protected $usage = '/givemethenews';

    /**
     * @var string
     */
    protected $version = '1';

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $message = $this->getMessage();
        $chatId = $this->getMessage()->getChat()->getId();
        $db = new Db($chatId);
        $rssDownloader = new RssDownloader();

        $this->reply("Let me check what i've got for you... May take a while");

        foreach ($rssDownloader->getTodayArticles($chatId) as $item) {
            $article = new Article($item["url"]);

            if ($article->save($item["html"])) {
                Request::sendDocument([
                    "chat_id" => $chatId,
                    "caption" => $item["title"],
                    "document" => Request::encodeFile($article->getFilePath())
//                  "document" => $article->getPublicPath()
                ]);
            }
        }
        return $this->reply("This is all for today.");
    }

    private function reply ($text) {
        return Request::sendMessage([
            "chat_id" => $this->getMessage()->getChat()->getId(),
            "text" => $text
        ]);
    }

    private function grab_dump($var)
    {
        ob_start();
        var_dump($var);
        return ob_get_clean();
    }
}

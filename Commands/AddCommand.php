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

/**
 * User "/help" command
 *
 * Command that lists all available commands and displays them in User and Admin sections.
 */
class AddCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'add';

    /**
     * @var string
     */
    protected $description = 'Adds a new feed';

    /**
     * @var string
     */
    protected $usage = '/add http://domain.com';

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
        $url = $message->getText(true);

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->reply("Usage: " . $this->usage);
        }

        $this->reply("Parsing $url");

        try {
            $rssDownloader = new RssDownloader();
            $feedUrl = $rssDownloader->getFeedUrl($url);
        } catch (\Exception $e) {
            return $this->reply("Wow, that url blown up my interior parts...\nOr maybe my creator is coding right now on production...\nI can't add that website to your list.");
        }

        if (empty($feedUrl)) {
            return $this->reply("Can't find any rss feed there :s");
        }

        $db = new Db($this->getMessage()->getChat()->getId());

        if ($db->add($feedUrl)) {
            return $this->reply("Added " . $feedUrl . " to your list!");
        }

        return $this->reply("Something went wrong, i'm having problems to record anything, must be the age.");
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

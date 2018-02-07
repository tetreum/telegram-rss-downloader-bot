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
use App\Db;

/**
 * User "/help" command
 *
 * Command that lists all available commands and displays them in User and Admin sections.
 */
class DelCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'del';

    /**
     * @var string
     */
    protected $description = 'Deletes a feed from your collection';

    /**
     * @var string
     */
    protected $usage = '/del http://domain.com';

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

        $this->reply("Removing $url");

        $db = new Db($this->getMessage()->getChat()->getId());

        if ($db->del($url)) {
            return $this->reply("Removed " . $url . " from your list!");
        }

        return $this->reply("Something went wrong, i'm having problems to remove anything, must be the age.");
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

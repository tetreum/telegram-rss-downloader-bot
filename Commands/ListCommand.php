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
class ListCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'list';

    /**
     * @var string
     */
    protected $description = 'Lists your feed collection';

    /**
     * @var string
     */
    protected $usage = '/list';

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
        $db = new Db($this->getMessage()->getChat()->getId());
        $response = "";

        foreach ($db->list() as $url => $date) {
            $response .= "\n - " . $url;
        }

        if (empty($response)) {
            return $this->reply("You didn't add any website :S");
        }

        return $this->reply($response);
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

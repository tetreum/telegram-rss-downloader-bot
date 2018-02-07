<?php

namespace App;

class Article {

    private $url;

    public function __construct ($url) {
        $this->url = $url;
    }

    /**
     * Returns article's file internal path
     * @return string
     */
    public function getFilePath () {
        return  APP_ROOT . DIRECTORY_SEPARATOR . App::config("articles.folder") . DIRECTORY_SEPARATOR . md5($this->url) . ".html";
    }

    /**
     * Returns article's file public path
     * @return string
     */
    public function getPublicPath () {
        return App::config("bot.url") . App::config("articles.folder") . DIRECTORY_SEPARATOR . md5($this->url) . ".html";
    }

    /**
     * Checks if article is already saved
     * @return bool
     */
    public function exists () {
        return App::config("cache") && file_exists($this->getFilePath());
    }

    /**
     * Returns article's html
     * @return string
     */
    public function get () {
        return file_get_contents($this->getFilePath());
    }

    /**
     * @param string $html
     * @return bool
     */
    public function save ($html) {

        if ($this->exists()) {
            return true;
        }

        if(!file_put_contents($this->getFilePath(), $html)){
            return false;
        }

        return true;
    }
}

<?php

namespace App;

class Article {

    private $url;

    public function __construct ($url) {
        $this->url = $url;
    }

    public function getFilePath () {
        return  APP_ROOT . DIRECTORY_SEPARATOR . App::config("articles.folder") . DIRECTORY_SEPARATOR . md5($this->url) . ".html";
    }

    public function getPublicPath () {
        return App::config("bot.url") . App::config("articles.folder") . DIRECTORY_SEPARATOR . md5($this->url) . ".html";
    }

    public function exists () {
        return false;
        return App::config("cache") && file_exists($this->getFilePath());
    }

    public function get () {
        return file_get_contents($this->getFilePath());
    }

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

<?php

namespace App;

class App {
    private $config = [];

    private static $instance = null;

    private static function getInstance () {
        if (empty(self::$instance)) {
            self::$instance = new App();
        }
        return self::$instance;
    }

    public static function config ($key) {
        return self::getInstance()->config[$key];
    }

    public static function setConfig (array $config) {
        self::getInstance()->config = $config;
    }
}

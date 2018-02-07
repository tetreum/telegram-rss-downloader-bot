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

    /**
     * Recovers a config entry
     * @param string $key
     * @return mixed
     */
    public static function config ($key) {
        return self::getInstance()->config[$key];
    }

    /**
     * Sets app's config
     * @param array $config
     */
    public static function setConfig (array $config) {
        self::getInstance()->config = $config;
    }
}

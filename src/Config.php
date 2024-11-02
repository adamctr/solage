<?php

class Config {
    private static $instance = null;
    private $cssPath;
    private $jsPath;

    private function __construct() {

        if (APP_ENV === 'production') {
            $this->cssPath = 'assets/minified/style.min.css';
            $this->jsPath = 'assets/minified/index.min.js';
        } else {
            $this->cssPath = 'style/style.css';
            $this->jsPath = 'scripts/index.js';
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Config();
        }
        return self::$instance;
    }

    public function getCssPath() {
        return $this->cssPath;
    }

    public function getJsPath() {
        return $this->jsPath;
    }
}

<?php

namespace App;

class Db {

    private $userId;

    public function __construct ($userId) {
        if (!is_numeric($userId)) {
            throw new \Exception("Invalid user id");
        }
        $this->userId = $userId;
    }

    private function dbPath () {
        return App::config("db.folder") . "/" . $this->userId . ".json";
    }

    private function getData() {

        if (!file_exists($this->dbPath())) {
            return [];
        }

        return json_decode(file_get_contents($this->dbPath()), true);
    }

    public function list () {
        return $this->getData();
    }

    public function add ($url) {
        $data = $this->getData();

        if (isset($data[$url])) {
            return true;
        }

        $data[$url] = time();

        return $this->save($data);
    }

    public function del ($url) {
        $data = $this->getData();

        if (!isset($data[$url])) {
            return true;
        }

        unset($data[$url]);

        return $this->save($data);
    }

    private function save (array $data) {
        return file_put_contents($this->dbPath(), json_encode($data));
    }
}

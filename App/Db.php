<?php

/**
 * This class interacts with json file that is begin used as db
 */

namespace App;

class Db {

    private $userId;

    public function __construct ($userId) {
        if (!is_numeric($userId)) {
            throw new \Exception("Invalid user id");
        }
        $this->userId = $userId;
    }

    /**
     * Returns db path
     * @return string
     */
    private function dbPath () {
        return App::config("db.folder") . "/" . $this->userId . ".json";
    }

    /**
     * Returns all data from json file
     * @return array
     */
    private function getData() {

        if (!file_exists($this->dbPath())) {
            return [];
        }

        return json_decode(file_get_contents($this->dbPath()), true);
    }

    /**
     * Returns all user added domains
     * @return array
     */
    public function list () {
        return $this->getData();
    }

    /**
     * Saves a new feed to user db
     * @param string $url
     * @return bool|int
     */
    public function add ($url) {
        $data = $this->getData();

        if (isset($data[$url])) {
            return true;
        }

        $data[$url] = time();

        return $this->save($data);
    }

    /**
     * Removes given entry from db
     * @param string $url
     * @return bool|int
     */
    public function del ($url) {
        $data = $this->getData();

        if (!isset($data[$url])) {
            return true;
        }

        unset($data[$url]);

        return $this->save($data);
    }

    /**
     * Saves db info into a json file
     * @param array $data
     * @return int
     */
    private function save (array $data) {
        return file_put_contents($this->dbPath(), json_encode($data));
    }
}

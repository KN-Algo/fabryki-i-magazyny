<?php

class Database {

    private $database = null;

    function __construct() {
        $this->database = new SQLite3('database.db');
        if (!$this->prepareDatabase()) {
            echo 'Database preparation failed';
        } else {
            echo 'Database prepared';
        }
    }

    function __destruct() {
        $this->database->close();
    }

    function prepareDatabase() : bool {
        $success = false;
        try {
            $success = $this->database->exec('CREATE TABLE IF NOT EXISTS `ranking` (
                `id` integer primary key NOT NULL UNIQUE,
                `index` INTEGER NOT NULL UNIQUE,
                `username` TEXT NOT NULL UNIQUE,
                `time` INTEGER NOT NULL,
                `distance_diff` INTEGER NOT NULL
                )'
            );
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        return $success;
    }

    public function saveTime($index, $username, $time, $distance_diff) {
        $stmt = $this->database->prepare('INSERT INTO ranking (index, username, time, distance_diff) VALUES (:index, :username, :time, :distance_diff)');
        $stmt->bindValue(':index', $index, SQLITE3_INTEGER);
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $stmt->bindValue(':time', $time, SQLITE3_INTEGER);
        $stmt->bindValue(':distance_diff', $distance_diff, SQLITE3_INTEGER);
        $stmt->execute();
    }

    public function getRanking() {
        $stmt = $this->database->prepare('SELECT username, time, distance_diff FROM ranking ORDER BY time ASC');
        $result = $stmt->execute();
        $ranking = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $ranking[] = $row;
        }
        return $ranking;
    }

    public function getRankingByUsername($username) {
        $stmt = $this->database->prepare('SELECT username, time, distance_diff FROM ranking WHERE username = :username');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $result = $stmt->execute();
        $ranking = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $ranking[] = $row;
        }
        return $ranking;
    }

    public function checkIfUsernameOrIndexExists($username, $index) {
        $stmt = $this->database->prepare('SELECT COUNT(id) FROM ranking WHERE username = :username OR index = :index');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $stmt->bindValue(':index', $index, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row;
    }
}
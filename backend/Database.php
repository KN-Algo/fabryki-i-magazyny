<?php

class Database {

    private $database = null;

    function __construct() {
        $this->database = new SQLite3('database.db');
        if (!$this->prepareDatabase()) {
            echo 'Database preparation failed';
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
                `user_index` INTEGER NOT NULL UNIQUE,
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

    public function saveTime($user_index, $username, $time, $distance_diff) : bool {
        $success = false;
        try {
            $stmt = $this->database->prepare('INSERT INTO ranking (user_index, username, time, distance_diff) VALUES (:user_index, :username, :time, :distance_diff)');
            $stmt->bindValue(':user_index', $user_index, SQLITE3_INTEGER);
            $stmt->bindValue(':username', $username, SQLITE3_TEXT);
            $stmt->bindValue(':time', $time, SQLITE3_INTEGER);
            $stmt->bindValue(':distance_diff', $distance_diff, SQLITE3_INTEGER);
            $stmt->execute();
            $success = true;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        return $success;
    }

    public function getRanking() {
        $stmt = $this->database->prepare('SELECT username, time, distance_diff FROM ranking ORDER BY distance_diff ASC, time ASC LIMIT 10');
        $result = $stmt->execute();
        $ranking = [];
        $position = 1;
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $row['position'] = $position++;
            $ranking[] = $row;
        }
        return $ranking;
    }
    
    public function getRankingByUsername($username) {
        $stmt = $this->database->prepare('SELECT username, time, distance_diff FROM ranking ORDER BY distance_diff ASC, time ASC');
        $result = $stmt->execute();
        $ranking = [];
        $position = 1;
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $row['position'] = $position++;
            $ranking[] = $row;
        }
    
        // Filter the ranking to find the specific user
        $userRanking = array_filter($ranking, function($row) use ($username) {
            return $row['username'] === $username;
        });
    
        return array_values($userRanking); // Re-index the array
    }

    public function checkIfUsernameOrIndexExists($username, $user_index) : bool {
        $exists = false;
        try {
            $stmt = $this->database->prepare('SELECT * FROM ranking WHERE username = :username OR user_index = :user_index');
            $stmt->bindValue(':username', $username, SQLITE3_TEXT);
            $stmt->bindValue(':user_index', $user_index, SQLITE3_INTEGER);
            $result = $stmt->execute();
            $exists = $result->fetchArray() !== false;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        return $exists;
    }
}
<?php

class Database {

    private $database = null;

    function __construct() {
        $this->database = new SQLite3(__DIR__.'/../database.db');
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

    public function getRanking($limit = 10) {
        $stmt = $this->database->prepare('SELECT username, time, distance_diff FROM ranking ORDER BY distance_diff ASC, time ASC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
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
        // Fetch top 10 records
        $stmt = $this->database->prepare('SELECT username, time, distance_diff FROM ranking ORDER BY distance_diff ASC, time ASC LIMIT 10');
        $result = $stmt->execute();
        $ranking = [];
        $position = 1;
        $user_in_top_10 = false;
        $user_row = null;
    
        // Populate top 10 ranking
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $row['position'] = $position++;
            if ($row['username'] === $username) {
                $user_in_top_10 = true;
                $user_row = $row; // Save user's row for later highlight
            }
            $ranking[] = $row;
        }
    
        // If the user is not in the top 10, fetch their position
        if (!$user_in_top_10) {
            $stmt = $this->database->prepare('SELECT username, time, distance_diff FROM ranking WHERE username = :username');
            $stmt->bindValue(':username', $username, SQLITE3_TEXT);
            $result = $stmt->execute();
            $user_row = $result->fetchArray(SQLITE3_ASSOC);
    
            if ($user_row) {
                // Calculate the user's position in the entire ranking
                $stmt = $this->database->prepare('SELECT COUNT(*) as position FROM ranking WHERE distance_diff < :distance_diff OR (distance_diff = :distance_diff AND time < :time)');
                $stmt->bindValue(':distance_diff', $user_row['distance_diff'], SQLITE3_FLOAT);
                $stmt->bindValue(':time', $user_row['time'], SQLITE3_FLOAT);
                $result = $stmt->execute();
                $position_row = $result->fetchArray(SQLITE3_ASSOC);
                $user_row['position'] = $position_row['position'] + 1;
    
                // Add the user's row at the end of the ranking
                $ranking[] = $user_row;
            }
        }
    
        return $ranking;
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
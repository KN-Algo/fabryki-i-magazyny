<?php

require_once __DIR__.'/../Database.php';

$database = new Database();
$index = null;
$username = null;
$time = null;
$optimal_distance = 1219;
$user_distance = null;
$distance_diff = null;

if (!isset($_POST['index']) || !isset($_POST['username']) || !isset($_POST['time']) || !isset($_POST['distance']) 
    || empty($_POST['index']) || empty($_POST['username']) || empty($_POST['time']) || empty($_POST['distance'])) {
    echo json_encode(['error' => 'Nie wszystkie dane zostały wysłane', 'code' => 400, 'line' => __LINE__]);
    exit();
}

$index = $_POST['index'];
$username = $_POST['username'];
$time = $_POST['time'];
$user_distance = $_POST['distance'];

if (!is_numeric($index) || !is_string($username) || !is_numeric($time) || !is_numeric($user_distance)) {
    echo json_encode(['error' => 'Indeks musi być 6 cyfrową liczbą, a nazwa użytkownika ciągiem znaków, czas i dystans muszą być liczbami', 'code' => 400, 'line' => __LINE__]);
    exit();
}

if (strlen($index) != 6) {
    echo json_encode(['error' => 'Indeks musi być 6 cyfrową liczbą', 'code' => 400, 'line' => __LINE__]);
    exit();
}

$distance_diff = $user_distance - $optimal_distance;

if ($database->saveTime($index, $username, $time, $distance_diff)) {
    echo json_encode(['error' => null, 'code' => 200]);
} else {
    echo json_encode(['error' => 'Nie udało się zapisać wyniku', 'code' => 500, 'line' => __LINE__]);
}
die();
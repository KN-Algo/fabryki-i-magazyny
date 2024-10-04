<?php

require_once __DIR__.'/../Database.php';

$database = new Database();
$index = null;
$username = null;

if (!isset($_POST['index']) || !isset($_POST['username']) || empty($_POST['index']) || empty($_POST['username'])) {
    echo json_encode(['error' => 'Indeks albo nazwa użytkownika jest puste', 'code' => 400, 'line' => __LINE__]);
    exit();
}   

$index = $_POST['index'];
$username = $_POST['username'];

if (!is_numeric($index) || !is_string($username)) {
    echo json_encode(['error' => 'Indeks musi być 6 cyfrową liczbą, a nazwa użytkownika ciągiem znaków', 'code' => 400, 'line' => __LINE__]);
    exit();
}

if (strlen($index) != 6) {
    echo json_encode(['error' => 'Indeks musi być 6 cyfrową liczbą', 'code' => 400, 'line' => __LINE__]);
    exit();
}

if (!is_array($database->checkIfUsernameOrIndexExists($username, $index))) {
    echo json_encode(['error' => 'Użytkownik o podanym indeksie lub nazwie użytkownika już istnieje', 'code' => 400, 'line' => __LINE__]);
    exit();
}

echo json_encode(['error' => null, 'code' => 200]);
die();
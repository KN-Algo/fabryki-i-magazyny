<?php

require_once __DIR__.'/../Database.php';

$database = new Database();
$username = null;
$ranking = [];

if (isset($_POST['username']) && !empty($_POST['username'])) {
    $username = $_POST['username'];
}

if ($username === null)
    $ranking = $database->getRanking();
else
    $ranking = $database->getRankingByUsername($username);

echo json_encode($ranking);
die();
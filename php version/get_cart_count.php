<?php
session_start();

header('Content-Type: application/json');

$count = 0;
if (!empty($_SESSION['cart'])) {
    $count = array_sum($_SESSION['cart']);
}

echo json_encode(['count' => $count]);
?>
<?php
// get_cart_count.php
session_start();

$count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
}

header('Content-Type: application/json');
echo json_encode(['count' => $count]);
?>
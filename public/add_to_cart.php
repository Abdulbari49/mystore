<?php
session_start();

if (isset($_POST['add_to_cart'])) {

    $product_id = $_POST['product_id'];

    // Cart session nahi hai
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Product already cart mein hai
    if (isset($_SESSION['cart'][$product_id])) {

        $_SESSION['cart'][$product_id]++;

    } else {

        $_SESSION['cart'][$product_id] = 1;

    }

    header("Location: cart.php");
    exit;
}
?>
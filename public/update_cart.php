<?php
session_start();


if(isset($_POST['update'])){

    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];


    $_SESSION['cart'][$product_id] = $quantity;


}

header("Location: cart.php");
exit;




?>
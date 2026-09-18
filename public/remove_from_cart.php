<?php

session_start();
include('../include/config.php');

if(isset($_POST['remove'])){
    $product_id = $_POST['product_id'];
    unset($_SESSION['cart'][$product_id]);

}
header("Location: cart.php");
exit;


?>

<?php
//PUT THIS HEADER ON TOP OF EACH UNIQUE PAGE
session_start();
include 'mainPage.php';
if (!isset($_SESSION['username'])) {
    header("location:login/main_login.php");


}

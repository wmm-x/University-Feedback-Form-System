<?php
session_start();

if (isset($_SESSION["username"])  && $_SESSION["role"] == "sys_admin") {
    include('dashboard.php');}

else if (isset($_SESSION["username"])  && $_SESSION["role"] == "lecture") {
    include('lectuer.php');}

 else {
    header("Location: login.php");
    exit();
}

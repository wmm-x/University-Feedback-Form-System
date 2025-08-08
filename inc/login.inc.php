<?php

if (isset($_POST["submit"])) {
    $username = $_POST["uname"];
    $pw = $_POST["password"];

    require_once 'dbh.inc.php';
    require_once 'function.inc.php';

    if (emtyInputlogin($username, $pw) !== false) {
        header('Location:../login.php?error=emptyinputs');
        exit();
    }

    loginUser($conn, $username, $pw);
}

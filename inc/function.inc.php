<?php



function emtyInputlogin($username, $pw)
{
    $result = false;
    if (empty($username) || empty($pw)) {

        $result = true;
    } else {
        $result = false;
    }
    return $result;
}

function emptyInputSignup($fname, $username, $acode, $email, $pw)
{
    $result = false;
    if (empty($fname) || empty($username) || empty($pw) || empty($email) || empty($acode)) {

        $result = true;
    } else {
        $result = false;
    }
    return $result;
}



function invalidUname($username)
{

    $result = false;
    if (!preg_match("/^[a-zA-Z0-9]*$/", $username)) {

        $result = true;
    } else {
        $result = false;
    }
    return $result;
}
function generateRandomPassword(int $length = 16): string
    {
        if ($length < 4) {
            throw new InvalidArgumentException('Password length must be at least 4 characters');
        }

        $upper   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower   = 'abcdefghijklmnopqrstuvwxyz';
        $digits  = '0123456789';
        $special = '!@#$%^&*()-_=+[]{}<>?';
        $passwordChars = [
            $upper[random_int(0, strlen($upper) - 1)],
            $lower[random_int(0, strlen($lower) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
            $special[random_int(0, strlen($special) - 1)],
        ];

        $all = $upper . $lower . $digits . $special;
        for ($i = 4; $i < $length; $i++) {
            $passwordChars[] = $all[random_int(0, strlen($all) - 1)];
        }
        shuffle($passwordChars);

        return implode('', $passwordChars);
    }

function generateRandomUsername(int $length = 16): string
    {
        $chars    = 'abcdefghijklmnopqrstuvwxyz'
            . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            . '0123456789';
        $maxIndex = strlen($chars) - 1;
        $username = '';
        for ($i = 0; $i < $length; $i++) {
            $username .= $chars[random_int(0, $maxIndex)];
        }
        return $username;
    }


function userExists($conn, $username)
{
    $sql = "SELECT * FROM user WHERE username = ? OR email=?;";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        header("Location:../login.php?error=stmtfailed");
        exit();
    }


    mysqli_stmt_bind_param($stmt, "ss", $username,$username);
    mysqli_stmt_execute($stmt);
    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        return $row;
    } else {
        return false;
    }

    mysqli_stmt_close($stmt);
}

function loginUser($conn, $username, $pw)
{
    $usaerExists = userExists($conn, $username);
    if ($usaerExists === false) {
        header("Location:../login.php?error=wronglogin");
        exit();
    }
    $hashedpw = $usaerExists["password"];
    $checkpw = password_verify($pw, $hashedpw);

    if ($checkpw === false) {
        header('Location:../login.php?error=wronglogin');
        exit();
    } elseif ($checkpw === true) {
        session_start();
        $_SESSION["username"] = $usaerExists["username"];
        $_SESSION["role"] = $usaerExists["role"];
        $_SESSION["userid"] = $usaerExists["ID"];
        $_SESSION["email"] = $usaerExists["email"];
        header("Location:../index.php");
        exit();
    }
}

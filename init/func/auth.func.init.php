<?php
function emptyInputRegister($name, $email, $password, $confirmPassword)
{
    return empty($name) || empty($email) || empty($password) || empty($confirmPassword);
}

function invalidEmail($email)
{
    return !filter_var($email, FILTER_VALIDATE_EMAIL);
}

function passwordMatch($password, $confirmPassword)
{
    return $password !== $confirmPassword;
}

function userExists($conn, $name, $email)
{
    $sql = "SELECT * FROM users WHERE name = ? OR email = ?;";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "ss", $name, $email);
    mysqli_stmt_execute($stmt);
    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        mysqli_stmt_close($stmt);
        return $row;
    }

    mysqli_stmt_close($stmt);
    return false;
}

function createUser($conn, $name, $email, $password, $photo = 'default.png', $role = 'user')
{
    $sql = "INSERT INTO users (name, email, password, photo, role) VALUES (?, ?, ?, ?, ?);";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        header("Location: ../register.php?error=stmtfailed");
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $hashedPassword, $photo, $role);
    mysqli_stmt_execute($stmt);
    $userId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    $sqlStats = "INSERT INTO user_stats (user_id, wins, losses, draws, total_games, score) VALUES (?, 0, 0, 0, 0, 0);";
    $stmtStats = mysqli_stmt_init($conn);

    if (mysqli_stmt_prepare($stmtStats, $sqlStats)) {
        mysqli_stmt_bind_param($stmtStats, "i", $userId);
        mysqli_stmt_execute($stmtStats);
        mysqli_stmt_close($stmtStats);
    }

    header("Location: ../login.php?success=registered");
    exit();
}

function emptyInputLogin($loginInput, $password)
{
    return empty($loginInput) || empty($password);
}

function loginUser($conn, $loginInput, $password)
{
    $sql = "SELECT * FROM users WHERE name = ? OR email = ?;";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        header("Location: ../login.php?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "ss", $loginInput, $loginInput);
    mysqli_stmt_execute($stmt);
    $resultData = mysqli_stmt_get_result($stmt);

    if (!$user = mysqli_fetch_assoc($resultData)) {
        mysqli_stmt_close($stmt);
        header("Location: ../login.php?error=notfound");
        exit();
    }

    mysqli_stmt_close($stmt);

    if (!password_verify($password, $user['password'])) {
        header("Location: ../login.php?error=wrongpassword");
        exit();
    }

    session_start();
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['photo'] = $user['photo'];
    $_SESSION['role'] = $user['role'];

    header("Location: ../dashboard.php");
    exit();
}
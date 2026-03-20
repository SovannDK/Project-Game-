<?php
function getAllUsers($conn)
{
    $sql = "SELECT u.user_id, u.name, u.email, u.photo, u.role, u.created_at,
                   s.wins, s.losses, s.draws, s.total_games, s.score
            FROM users u
            LEFT JOIN user_stats s ON u.user_id = s.user_id
            ORDER BY u.user_id DESC";

    return mysqli_query($conn, $sql);
}

function getUserById($conn, $userId)
{
    $sql = "SELECT * FROM users WHERE user_id = ?;";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $user;
}

function updateUserData($conn, $userId, $name, $email, $photo)
{
    $sql = "UPDATE users SET name = ?, email = ?, photo = ? WHERE user_id = ?;";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $photo, $userId);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $result;
}

function deleteUserData($conn, $userId)
{
    $sql = "DELETE FROM users WHERE user_id = ?;";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "i", $userId);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $result;
}

function updateGameStats($conn, $userId, $result)
{
    $sql = "SELECT * FROM user_stats WHERE user_id = ?;";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $resultData = mysqli_stmt_get_result($stmt);
    $stats = mysqli_fetch_assoc($resultData);
    mysqli_stmt_close($stmt);

    if (!$stats) {
        return false;
    }

    $wins = (int)$stats['wins'];
    $losses = (int)$stats['losses'];
    $draws = (int)$stats['draws'];
    $totalGames = (int)$stats['total_games'];

    if ($result === 'win') {
        $wins++;
    } elseif ($result === 'loss') {
        $losses++;
    } elseif ($result === 'draw') {
        $draws++;
    }

    $totalGames++;
    $score = ($wins * 3) + $draws;

    $sqlUpdate = "UPDATE user_stats SET wins = ?, losses = ?, draws = ?, total_games = ?, score = ? WHERE user_id = ?;";
    $stmtUpdate = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmtUpdate, $sqlUpdate)) {
        return false;
    }

    mysqli_stmt_bind_param($stmtUpdate, "iiiiii", $wins, $losses, $draws, $totalGames, $score, $userId);
    $ok = mysqli_stmt_execute($stmtUpdate);
    mysqli_stmt_close($stmtUpdate);

    return $ok;
}

function getRankingData($conn)
{
    $sql = "SELECT u.user_id, u.name, u.photo, s.wins, s.losses, s.draws, s.total_games, s.score
            FROM users u
            INNER JOIN user_stats s ON u.user_id = s.user_id
            ORDER BY s.score DESC, s.wins DESC, s.losses ASC, u.name ASC";

    return mysqli_query($conn, $sql);
}

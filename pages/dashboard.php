<?php
require_once 'includes/auth.php';
requireLogin();
require_once 'init/init.php';
require_once 'includes/functions.php';
include 'includes/header.php';

$user = getUserById($conn, $_SESSION['user_id']);
$ranking = getRankingData($conn);
$rankNumber = 1;
$myRank = '-';

while ($row = mysqli_fetch_assoc($ranking)) {
    if ((int)$row['user_id'] === (int)$_SESSION['user_id']) {
        $myRank = $rankNumber;
        break;
    }
    $rankNumber++;
}

$sqlStats = "SELECT * FROM user_stats WHERE user_id = ?;";
$stmtStats = mysqli_stmt_init($conn);
mysqli_stmt_prepare($stmtStats, $sqlStats);
mysqli_stmt_bind_param($stmtStats, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmtStats);
$statsResult = mysqli_stmt_get_result($stmtStats);
$stats = mysqli_fetch_assoc($statsResult);
mysqli_stmt_close($stmtStats);
?>

<section class="card">
    <div class="profile-top">
        <img class="avatar-large" src="<?php echo getPhotoPath($user['photo']); ?>" alt="Profile">
        <div>
            <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?></h2>
            <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
            <p>Role: <?php echo htmlspecialchars($user['role']); ?></p>
            <p>My Rank: <?php echo $myRank; ?></p>
        </div>
    </div>
</section>

<section class="card stats-grid">
    <div class="stat-box"><strong>Wins</strong><span><?php echo $stats['wins'] ?? 0; ?></span></div>
    <div class="stat-box"><strong>Losses</strong><span><?php echo $stats['losses'] ?? 0; ?></span></div>
    <div class="stat-box"><strong>Draws</strong><span><?php echo $stats['draws'] ?? 0; ?></span></div>
    <div class="stat-box"><strong>Total Games</strong><span><?php echo $stats['total_games'] ?? 0; ?></span></div>
    <div class="stat-box"><strong>Score</strong><span><?php echo $stats['score'] ?? 0; ?></span></div>
</section>

<section class="card">
    <h3>Quick Links</h3>
    <div class="button-row">
        <a class="btn link-btn" href="index.php">Play Game</a>
        <a class="btn link-btn" href="profile.php">My Profile</a>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a class="btn link-btn" href="pages/User/list.php">Manage Users</a>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<?php
require_once __DIR__ . '/../init/init.php';
require_once __DIR__ . '/../init/db.init.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Ranking - XO Arena';
$rankings = getRanking($pdo, 50);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="ranking-page">
    <div class="ranking-container">
        <h1 class="page-title">🏆 Leaderboard</h1>
        <p class="page-subtitle">Top XO Arena Players</p>

        <?php if (empty($rankings)): ?>
            <div class="empty-state">
                <p>No games played yet. Be the first to compete!</p>
                <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-primary">Play Now</a>
            </div>
        <?php else: ?>
            <div class="ranking-table-wrapper">
                <table class="ranking-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Player</th>
                            <th>Wins</th>
                            <th>Losses</th>
                            <th>Draws</th>
                            <th>Games</th>
                            <th>Win Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rankings as $i => $player): ?>
                            <tr class="<?php echo $i < 3 ? 'rank-top rank-' . ($i + 1) : ''; ?>
                                       <?php echo (isLoggedIn() && $player['user_id'] == getCurrentUserId()) ? 'rank-me' : ''; ?>">
                                <td class="rank-num">
                                    <?php
                                    if ($i === 0) echo '🥇';
                                    elseif ($i === 1) echo '🥈';
                                    elseif ($i === 2) echo '🥉';
                                    else echo $i + 1;
                                    ?>
                                </td>
                                <td class="rank-player">
                                    <img src="<?php echo BASE_URL; ?>assets/uploads/profiles/<?php echo htmlspecialchars($player['photo']); ?>"
                                         alt="" class="rank-avatar">
                                    <span><?php echo htmlspecialchars($player['name']); ?></span>
                                </td>
                                <td class="rank-wins"><?php echo $player['wins']; ?></td>
                                <td class="rank-losses"><?php echo $player['losses']; ?></td>
                                <td><?php echo $player['draws']; ?></td>
                                <td><?php echo $player['total_games']; ?></td>
                                <td class="rank-winrate"><?php echo $player['win_rate']; ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

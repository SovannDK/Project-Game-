<?php
require_once __DIR__ . '/init/init.php';
require_once __DIR__ . '/init/db.init.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'XO Arena - Play';

// Handle AJAX game result save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_result') {
    header('Content-Type: application/json');

    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }

    $result = $_POST['result'] ?? '';
    if (in_array($result, ['win', 'loss', 'draw'])) {
        updateGameStats($pdo, getCurrentUserId(), $result);
        $user = getUserById($pdo, getCurrentUserId());
        echo json_encode([
            'success' => true,
            'wins' => $user['wins'],
            'losses' => $user['losses'],
            'draws' => $user['draws']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid result']);
    }
    exit;
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="game-page">
    <div class="game-container">
        <div class="game-header">
            <h1 class="game-title">TIC TAC TOE</h1>
            <div class="game-status" id="gameStatus">
                <?php if (isLoggedIn()): ?>
                    Your turn, <?php echo htmlspecialchars(getCurrentUserName()); ?>! You are <span class="x-mark">X</span>
                <?php else: ?>
                    You are <span class="x-mark">X</span> — Play as Guest
                <?php endif; ?>
            </div>
        </div>

        <div class="game-board" id="gameBoard">
            <div class="cell" data-index="0"></div>
            <div class="cell" data-index="1"></div>
            <div class="cell" data-index="2"></div>
            <div class="cell" data-index="3"></div>
            <div class="cell" data-index="4"></div>
            <div class="cell" data-index="5"></div>
            <div class="cell" data-index="6"></div>
            <div class="cell" data-index="7"></div>
            <div class="cell" data-index="8"></div>
        </div>

        <div class="game-score">
            <div class="score-item score-x">
                <span class="score-label">You (X)</span>
                <span class="score-value" id="scoreX">0</span>
            </div>
            <div class="score-item score-draw">
                <span class="score-label">Draw</span>
                <span class="score-value" id="scoreDraw">0</span>
            </div>
            <div class="score-item score-o">
                <span class="score-label">Bot (O)</span>
                <span class="score-value" id="scoreO">0</span>
            </div>
        </div>

        <div class="game-actions">
            <button id="btnRestart" class="btn btn-primary" onclick="restartGame()">Play Again</button>
            <a href="<?php echo BASE_URL; ?>pages/dashboard.php" class="btn btn-secondary">Ranking</a>
        </div>
    </div>

    <!-- Modal: Prompt to register -->
    <div class="modal-overlay" id="registerModal" style="display:none;">
        <div class="modal-box">
            <h2 class="modal-title">Nice Game!</h2>
            <p class="modal-text" id="modalResultText"></p>
            <p class="modal-cta">Create an account to <strong>save your scores</strong> and compete on the leaderboard!</p>
            <div class="modal-actions">
                <a href="<?php echo BASE_URL; ?>pages/register.php" class="btn btn-primary">Create Account</a>
                <a href="<?php echo BASE_URL; ?>pages/login.php" class="btn btn-secondary">Login</a>
                <button onclick="closeModal(); restartGame();" class="btn btn-ghost">Play Again as Guest</button>
            </div>
        </div>
    </div>
</div>

<script>
// ========================
// XO GAME ENGINE
// ========================
const IS_LOGGED_IN = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
const BASE_URL = '<?php echo BASE_URL; ?>';

let board = ['', '', '', '', '', '', '', '', ''];
let currentPlayer = 'X'; // Player is always X
let gameActive = true;
let scoreX = 0, scoreO = 0, scoreDraw = 0;

const winConditions = [
    [0, 1, 2], [3, 4, 5], [6, 7, 8], // rows
    [0, 3, 6], [1, 4, 7], [2, 5, 8], // cols
    [0, 4, 8], [2, 4, 6]             // diagonals
];

const cells = document.querySelectorAll('.cell');
const statusEl = document.getElementById('gameStatus');

cells.forEach(cell => {
    cell.addEventListener('click', () => handleCellClick(cell));
});

function handleCellClick(cell) {
    const index = cell.dataset.index;
    if (board[index] !== '' || !gameActive || currentPlayer !== 'X') return;

    makeMove(index, 'X');

    if (gameActive) {
        currentPlayer = 'O';
        statusEl.innerHTML = 'Bot is thinking...';
        setTimeout(botMove, 400);
    }
}

function makeMove(index, player) {
    board[index] = player;
    const cell = cells[index];
    cell.textContent = player;
    cell.classList.add(player === 'X' ? 'x-played' : 'o-played');

    if (checkWin(player)) {
        gameActive = false;
        highlightWin(player);
        if (player === 'X') {
            scoreX++;
            document.getElementById('scoreX').textContent = scoreX;
            statusEl.innerHTML = '🎉 <span class="x-mark">You Win!</span>';
            saveResult('win');
        } else {
            scoreO++;
            document.getElementById('scoreO').textContent = scoreO;
            statusEl.innerHTML = '😔 <span class="o-mark">Bot Wins!</span>';
            saveResult('loss');
        }
        return;
    }

    if (board.every(cell => cell !== '')) {
        gameActive = false;
        scoreDraw++;
        document.getElementById('scoreDraw').textContent = scoreDraw;
        statusEl.innerHTML = '🤝 It\'s a Draw!';
        saveResult('draw');
        return;
    }
}

function checkWin(player) {
    return winConditions.some(condition =>
        condition.every(index => board[index] === player)
    );
}

function highlightWin(player) {
    winConditions.forEach(condition => {
        if (condition.every(index => board[index] === player)) {
            condition.forEach(index => {
                cells[index].classList.add('winning');
            });
        }
    });
}

// ========================
// BOT AI (Minimax)
// ========================
function botMove() {
    if (!gameActive) return;

    const bestMove = getBestMove();
    makeMove(bestMove, 'O');

    if (gameActive) {
        currentPlayer = 'X';
        statusEl.innerHTML = 'Your turn! You are <span class="x-mark">X</span>';
    }
}

function getBestMove() {
    // Try to win
    for (let i = 0; i < 9; i++) {
        if (board[i] === '') {
            board[i] = 'O';
            if (checkWin('O')) { board[i] = ''; return i; }
            board[i] = '';
        }
    }
    // Block player
    for (let i = 0; i < 9; i++) {
        if (board[i] === '') {
            board[i] = 'X';
            if (checkWin('X')) { board[i] = ''; return i; }
            board[i] = '';
        }
    }
    // Center
    if (board[4] === '') return 4;
    // Corners
    const corners = [0, 2, 6, 8].filter(i => board[i] === '');
    if (corners.length > 0) return corners[Math.floor(Math.random() * corners.length)];
    // Any
    const available = board.map((v, i) => v === '' ? i : null).filter(v => v !== null);
    return available[Math.floor(Math.random() * available.length)];
}

// ========================
// SAVE & MODAL
// ========================
function saveResult(result) {
    if (IS_LOGGED_IN) {
        // Save to server
        fetch(BASE_URL + 'index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=save_result&result=${result}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                console.log('Score saved!');
            }
        })
        .catch(err => console.error(err));
    } else {
        // Show register prompt after short delay
        setTimeout(() => {
            const modal = document.getElementById('registerModal');
            const resultText = document.getElementById('modalResultText');
            if (result === 'win') resultText.textContent = 'You won! 🎉';
            else if (result === 'loss') resultText.textContent = 'Bot wins this round!';
            else resultText.textContent = 'It\'s a draw!';
            modal.style.display = 'flex';
        }, 800);
    }
}

function closeModal() {
    document.getElementById('registerModal').style.display = 'none';
}

function restartGame() {
    board = ['', '', '', '', '', '', '', '', ''];
    gameActive = true;
    currentPlayer = 'X';
    cells.forEach(cell => {
        cell.textContent = '';
        cell.className = 'cell';
    });
    statusEl.innerHTML = 'Your turn! You are <span class="x-mark">X</span>';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

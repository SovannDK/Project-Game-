<?php
require_once __DIR__ . '/../init/init.php';
require_once __DIR__ . '/../init/db.init.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Play vs Friend - XO Arena';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="game-page">
    <div class="game-container">
        <div class="game-header">
            <h1 class="game-title">PLAY VS FRIEND</h1>
            <div class="game-status" id="gameStatus">
                <span class="x-mark">X</span>'s turn (Player 1)
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
                <span class="score-label">Player 1 (X)</span>
                <span class="score-value" id="scoreX">0</span>
            </div>
            <div class="score-item score-draw">
                <span class="score-label">Draw</span>
                <span class="score-value" id="scoreDraw">0</span>
            </div>
            <div class="score-item score-o">
                <span class="score-label">Player 2 (O)</span>
                <span class="score-value" id="scoreO">0</span>
            </div>
        </div>

        <div class="game-actions">
            <button id="btnRestart" class="btn btn-primary" onclick="restartGame()">Play Again</button>
            <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-secondary">Play vs Bot</a>
        </div>
    </div>

    <!-- Result Modal -->
    <div class="modal-overlay" id="resultModal" style="display:none;">
        <div class="modal-box">
            <h2 class="modal-title" id="resultTitle"></h2>
            <p class="modal-text" id="resultText"></p>
            <div style="display:flex; gap:10px; justify-content:center; margin-top:20px;">
                <button class="btn btn-primary" onclick="closeResultModal(); restartGame();">Play Again</button>
                <button class="btn btn-secondary" onclick="closeResultModal();">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let board = ['', '', '', '', '', '', '', '', ''];
let currentPlayer = 'X';
let gameActive = true;
let scoreX = 0, scoreO = 0, scoreDraw = 0;

const winConditions = [
    [0, 1, 2], [3, 4, 5], [6, 7, 8],
    [0, 3, 6], [1, 4, 7], [2, 5, 8],
    [0, 4, 8], [2, 4, 6]
];

const cells = document.querySelectorAll('.cell');
const statusEl = document.getElementById('gameStatus');

cells.forEach(cell => {
    cell.addEventListener('click', () => handleCellClick(cell));
});

function handleCellClick(cell) {
    const index = cell.dataset.index;
    if (board[index] !== '' || !gameActive) return;

    // Place the mark
    board[index] = currentPlayer;
    cell.textContent = currentPlayer;
    cell.classList.add(currentPlayer === 'X' ? 'x-played' : 'o-played');

    // Check win
    if (checkWin(currentPlayer)) {
        gameActive = false;
        highlightWin(currentPlayer);

        if (currentPlayer === 'X') {
            scoreX++;
            document.getElementById('scoreX').textContent = scoreX;
            statusEl.innerHTML = '🎉 <span class="x-mark">Player 1 (X) Wins!</span>';
            showResultModal('Player 1 Wins! 🎉', 'X takes this round!');
        } else {
            scoreO++;
            document.getElementById('scoreO').textContent = scoreO;
            statusEl.innerHTML = '🎉 <span class="o-mark">Player 2 (O) Wins!</span>';
            showResultModal('Player 2 Wins! 🎉', 'O takes this round!');
        }
        return;
    }

    // Check draw
    if (board.every(cell => cell !== '')) {
        gameActive = false;
        scoreDraw++;
        document.getElementById('scoreDraw').textContent = scoreDraw;
        statusEl.innerHTML = '🤝 It\'s a Draw!';
        showResultModal('It\'s a Draw! 🤝', 'No winner this round.');
        return;
    }

    // Switch turn
    currentPlayer = currentPlayer === 'X' ? 'O' : 'X';
    if (currentPlayer === 'X') {
        statusEl.innerHTML = '<span class="x-mark">X</span>\'s turn (Player 1)';
    } else {
        statusEl.innerHTML = '<span class="o-mark">O</span>\'s turn (Player 2)';
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

function restartGame() {
    board = ['', '', '', '', '', '', '', '', ''];
    gameActive = true;
    currentPlayer = 'X';
    cells.forEach(cell => {
        cell.textContent = '';
        cell.className = 'cell';
    });
    statusEl.innerHTML = '<span class="x-mark">X</span>\'s turn (Player 1)';
}

function showResultModal(title, text) {
    document.getElementById('resultTitle').textContent = title;
    document.getElementById('resultText').textContent = text;
    setTimeout(() => {
        document.getElementById('resultModal').style.display = 'flex';
    }, 600);
}

function closeResultModal() {
    document.getElementById('resultModal').style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeResultModal();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
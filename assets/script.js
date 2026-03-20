let board = ["", "", "", "", "", "", "", "", ""];
let gameOver = false;

const winningCombos = [
    [0, 1, 2],
    [3, 4, 5],
    [6, 7, 8],
    [0, 3, 6],
    [1, 4, 7],
    [2, 5, 8],
    [0, 4, 8],
    [2, 4, 6]
];

function checkWinner(player) {
    return winningCombos.some(combo => combo.every(index => board[index] === player));
}

function checkDraw() {
    return board.every(cell => cell !== "");
}

function finishGame(result) {
    gameOver = true;
    const statusBox = document.getElementById('gameStatus');
    const saveBox = document.getElementById('saveBox');

    if (!statusBox || !saveBox) return;

    if (result === 'win') {
        statusBox.textContent = 'You win!';
    } else if (result === 'loss') {
        statusBox.textContent = 'Bot wins!';
    } else {
        statusBox.textContent = 'Draw!';
    }

    if (typeof isLoggedIn !== 'undefined' && isLoggedIn) {
        fetch('index.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'game_result=' + encodeURIComponent(result)
        })
        .then(() => {
            saveBox.innerHTML = '<p class="success-text">Result saved to your account.</p>';
        })
        .catch(() => {
            saveBox.innerHTML = '<p class="error-text">Could not save result.</p>';
        });
    } else {
        saveBox.innerHTML = '<p class="error-text">Create an account to save your score.</p>';
    }
}

function botMove() {
    if (gameOver) return;

    const emptyCells = board
        .map((cell, index) => cell === "" ? index : null)
        .filter(index => index !== null);

    if (emptyCells.length === 0) return;

    const randomIndex = emptyCells[Math.floor(Math.random() * emptyCells.length)];
    board[randomIndex] = 'O';

    const cell = document.querySelector(`.cell[data-index='${randomIndex}']`);
    if (cell) {
        cell.textContent = 'O';
    }

    if (checkWinner('O')) {
        finishGame('loss');
        return;
    }

    if (checkDraw()) {
        finishGame('draw');
        return;
    }

    const statusBox = document.getElementById('gameStatus');
    if (statusBox) {
        statusBox.textContent = 'Your turn: X';
    }
}

function restartGame() {
    board = ["", "", "", "", "", "", "", "", ""];
    gameOver = false;

    document.querySelectorAll('.cell').forEach(cell => {
        cell.textContent = '';
    });

    const statusBox = document.getElementById('gameStatus');
    const saveBox = document.getElementById('saveBox');

    if (statusBox) {
        statusBox.textContent = 'Your turn: X';
    }

    if (saveBox) {
        saveBox.innerHTML = '';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const cells = document.querySelectorAll('.cell');
    if (!cells.length) return;

    cells.forEach(cell => {
        cell.addEventListener('click', () => {
            if (gameOver) return;

            const index = cell.dataset.index;
            if (board[index] !== "") return;

            board[index] = 'X';
            cell.textContent = 'X';

            if (checkWinner('X')) {
                finishGame('win');
                return;
            }

            if (checkDraw()) {
                finishGame('draw');
                return;
            }

            const statusBox = document.getElementById('gameStatus');
            if (statusBox) {
                statusBox.textContent = 'Bot turn: O';
            }

            setTimeout(botMove, 350);
        });
    });
});
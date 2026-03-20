<?php
session_start();
include 'includes/header.php';
?>

<section class="card hero-card">
    <h1>Welcome to Game XO</h1>
    <p>Everyone can play. If you create an account, your score can be saved and shown in ranking.</p>

    <div id="gameStatus" class="status-box">Your turn: X</div>

    <div class="board" id="board">
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

    <button class="btn" onclick="restartGame()">Play Again</button>
    <div id="saveBox" class="message-box"></div>
</section>

<script>
const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
</script>

<?php include 'includes/footer.php'; ?>
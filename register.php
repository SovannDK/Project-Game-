<?php
$success = $_GET['success'] ?? '';
?>

<section class="card form-card">
    <h2>Create Account</h2>

    <?php if ($error === 'emptyinput'): ?>
        <p class="error-text">Please fill in all fields.</p>
    <?php elseif ($error === 'invalidemail'): ?>
        <p class="error-text">Invalid email format.</p>
    <?php elseif ($error === 'passwordnotmatch'): ?>
        <p class="error-text">Password and confirm password do not match.</p>
    <?php elseif ($error === 'userexists'): ?>
        <p class="error-text">Name or email already exists.</p>
    <?php elseif ($error === 'stmtfailed'): ?>
        <p class="error-text">Something went wrong.</p>
    <?php endif; ?>

    <?php if ($success === 'updated'): ?>
        <p class="success-text">Profile updated successfully.</p>
    <?php endif; ?>

    <form action="register.php" method="POST" enctype="multipart/form-data">
        <label>Name</label>
        <input type="text" name="name" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required>

        <label>Photo (optional)</label>
        <input type="file" name="photo" accept="image/*">

        <button type="submit" name="submit" class="btn">Register</button>
    </form>
</section>

<?php
if (isset($_POST['submit'])) {
    $name = cleanInput($_POST['name'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $photo = 'default.png';

    if (emptyInputRegister($name, $email, $password, $confirmPassword)) {
        header('Location: register.php?error=emptyinput');
        exit();
    }

    if (invalidEmail($email)) {
        header('Location: register.php?error=invalidemail');
        exit();
    }

    if (passwordMatch($password, $confirmPassword)) {
        header('Location: register.php?error=passwordnotmatch');
        exit();
    }

    if (userExists($conn, $name, $email) !== false) {
        header('Location: register.php?error=userexists');
        exit();
    }

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $fileName = $_FILES['photo']['name'];
        $fileTmpName = $_FILES['photo']['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($fileExt, $allowed)) {
            $newName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName);
            move_uploaded_file($fileTmpName, 'assets/img/' . $newName);
            $photo = $newName;
        }
    }

    createUser($conn, $name, $email, $password, $photo);
}

include 'includes/footer.php';
<?php
require_once '../../includes/auth.php';
requireAdminFromUserPage();
require_once '../../init/init.php';
require_once '../../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>
    <main class="container">
        <section class="card form-card">
            <h2>Create User</h2>
            <form action="create.php" method="POST" enctype="multipart/form-data">
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

                <button type="submit" name="submit" class="btn">Create User</button>
            </form>

            <div class="button-row">
                <a class="btn link-btn" href="list.php">Back to List</a>
            </div>
        </section>
    </main>
</body>
</html>
<?php
if (isset($_POST['submit'])) {
    $name = cleanInput($_POST['name'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $photo = 'default.png';

    if (!emptyInputRegister($name, $email, $password, $confirmPassword) && !invalidEmail($email) && !passwordMatch($password, $confirmPassword) && userExists($conn, $name, $email) === false) {
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $fileName = $_FILES['photo']['name'];
            $fileTmpName = $_FILES['photo']['tmp_name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($fileExt, $allowed)) {
                $newName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName);
                move_uploaded_file($fileTmpName, '../../assets/img/' . $newName);
                $photo = $newName;
            }
        }

        createUser($conn, $name, $email, $password, $photo, 'user');
    }
}
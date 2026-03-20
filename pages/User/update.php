<?php
require_once '../../includes/auth.php';
requireAdminFromUserPage();
require_once '../../init/init.php';
require_once '../../includes/functions.php';

$userId = $_GET['user_id'] ?? 0;
$user = getUserById($conn, $userId);

if (!$user) {
    header('Location: list.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>
<body>
    <main class="container">
        <section class="card form-card">
            <h2>Update User</h2>
            <form action="update.php?user_id=<?php echo $user['user_id']; ?>" method="POST" enctype="multipart/form-data">
                <label>Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                <label>Photo (optional)</label>
                <input type="file" name="photo" accept="image/*">

                <button type="submit" name="submit" class="btn">Update User</button>
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
    $photo = $user['photo'];

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $fileName = $_FILES['photo']['name'];
        $fileTmpName = $_FILES['photo']['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($fileExt, $allowed)) {
            $newName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName);
            move_uploaded_file($fileTmpName, '../../assets/img/' . $newName);
            $photo = $newName;

            if ($user['photo'] !== 'default.png' && file_exists('../../assets/img/' . $user['photo'])) {
                unlink('../../assets/img/' . $user['photo']);
            }
        }
    }

    updateUserData($conn, $userId, $name, $email, $photo);
    header('Location: list.php');
    exit();
}
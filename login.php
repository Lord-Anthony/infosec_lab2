<?php
session_start();
require_once __DIR__ . '/db.php';

$error = '';

if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } elseif (strlen($username) > 100) {
        $error = 'Invalid login.';
    } else {
        $stmt = mysqli_prepare($conn, 'SELECT id, username, password FROM users WHERE username = ? LIMIT 1');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($row && password_verify($password, $row['password'])) {
                session_regenerate_id(true);
                $_SESSION['user'] = $row['username'];
                $_SESSION['user_id'] = (int) $row['id'];
                header('Location: dashboard.php');
                exit;
            }
        }
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Admin Login</h2>

    <?php if ($error !== ''): ?>
        <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" maxlength="100" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit" name="login" value="1">Login</button>
    </form>
</div>

</body>
</html>

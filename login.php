<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$loginInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginInput = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($loginInput === '' || $password === '') {
        $errors[] = 'Please enter your username/email and password.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, username, email, password FROM users WHERE username = :login_user OR email = :login_email LIMIT 1'
        );
        $stmt->execute([
            ':login_user' => $loginInput,
            ':login_email' => $loginInput,
        ]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['username'] = $user['username'];

            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Invalid username/email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TaskFlow</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>TaskFlow</h1>
        <p>Log in to access your tasks</p>
    </header>

    <main>
        <section class="task-section">
            <h2>Log In</h2>

            <?php if (!empty($errors)): ?>
                <div class="error-box">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <input
                    type="text"
                    name="login"
                    placeholder="Username or Email"
                    value="<?= htmlspecialchars($loginInput, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >

                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    required
                >

                <button type="submit">Log In</button>
            </form>

            <p>Need an account? <a href="register.php">Register here</a>.</p>
        </section>
    </main>

    <footer>
        <p>TaskFlow | SOFE 2800 Web Programming</p>
    </footer>
</body>
</html>

<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $username = strtolower(trim(post_string('username')));
    $password = post_string('password');

    if ($username === '' || $password === '') {
        $errors[] = 'Enter your username and password.';
    } else {
        try {
            $statement = db()->prepare(
                'SELECT id, fullname, username, password FROM users WHERE username = :username'
            );
            $statement->execute(['username' => $username]);
            $user = $statement->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                $errors[] = 'Invalid username or password.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id' => (string) $user['id'],
                    'name' => $user['fullname'],
                    'username' => $user['username'],
                ];
                unset($_SESSION['csrf_token']);
                csrf_token();
                header('Location: dashboard.php');
                exit;
            }
        } catch (PDOException $exception) {
            error_log('CipherLab login database error: ' . $exception->getMessage());
            $errors[] = 'Unable to connect to PostgreSQL. Check the database settings and try again.';
        }
    }
}

$page_title = 'Log in';
require __DIR__ . '/includes/header.php';
?>
<main class="auth-layout">
    <section class="auth-intro">
        <span class="eyebrow">A hands-on cryptography lab</span>
        <h1>Every cipher has a story.<br><span>See every step.</span></h1>
        <p>Explore the ideas behind four classic ciphers with clear transformations, live tables, and worked examples.</p>
        <div class="auth-points">
            <span><b>01</b> Learn the rules</span>
            <span><b>02</b> Follow each calculation</span>
            <span><b>03</b> Try it yourself</span>
        </div>
    </section>
    <section class="panel auth-panel">
        <span class="eyebrow">Welcome back</span>
        <h2>Log in to CipherLab</h2>
        <p class="text-muted">Your learning workspace is ready.</p>
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="notice success" role="status"><?= e($_SESSION['flash']) ?></div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="notice error" role="alert"><?= e(implode(' ', $errors)) ?></div>
        <?php endif; ?>
        <form method="post" action="index.php">
            <?= csrf_field() ?>
            <label for="username">Username</label>
            <input id="username" name="username" value="<?= e($username) ?>" autocomplete="username" maxlength="100" required>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            <button class="button button-primary button-wide" type="submit">Log in <span aria-hidden="true">→</span></button>
        </form>
        <p class="auth-switch">New to CipherLab? <a href="register.php">Create an account</a></p>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>

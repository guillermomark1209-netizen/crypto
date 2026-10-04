<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$values = ['full_name' => '', 'username' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    foreach ($values as $field => $_) {
        $values[$field] = trim(post_string($field));
    }
    $password = post_string('password');
    $confirmation = post_string('confirm_password');

    $nameLength = preg_match_all('/./us', $values['full_name']);
    if ($values['full_name'] === '' || $nameLength === false || $nameLength > 100) {
        $errors[] = 'Full name is required and must be at most 100 characters.';
    }
    if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $values['username'])) {
        $errors[] = 'Username must be 3–50 letters, numbers, or underscores.';
    }
    if (strlen($values['email']) > 255 || !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address of at most 255 characters.';
    }
    if (strlen($password) < 8 || strlen($password) > 72) {
        $errors[] = 'Password must be between 8 and 72 bytes.';
    }
    if ($password !== $confirmation) {
        $errors[] = 'Password confirmation does not match.';
    }

    if (!$errors) {
        try {
            $statement = db()->prepare(
                'INSERT INTO users (fullname, username, email, password)
                 VALUES (:fullname, :username, :email, :password)'
            );
            $statement->execute([
                'fullname' => $values['full_name'],
                'username' => strtolower($values['username']),
                'email' => strtolower($values['email']),
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            $_SESSION['flash'] = 'Your account was created. Please log in.';
            header('Location: index.php');
            exit;
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23505') {
                $errors[] = 'That username or email address is already registered.';
            } else {
                error_log('CipherLab registration database error: ' . $exception->getMessage());
                $errors[] = 'Unable to save your account. Check the PostgreSQL connection and try again.';
            }
        }
    }
}

$page_title = 'Create account';
require __DIR__ . '/includes/header.php';
?>
<main class="auth-layout">
    <section class="auth-intro">
        <span class="eyebrow">Start exploring</span>
        <h1>Curiosity is the<br><span>key to every code.</span></h1>
        <p>Create a free account to access step-by-step demonstrations of Caesar, Vigenère, Playfair, and Bifid ciphers.</p>
        <div class="auth-points">
            <span><b>04</b> Classic ciphers</span>
            <span><b>∞</b> Ways to experiment</span>
            <span><b>0</b> prior experience needed</span>
        </div>
    </section>
    <section class="panel auth-panel">
        <span class="eyebrow">Join the lab</span>
        <h2>Create your account</h2>
        <p class="text-muted">All fields are required.</p>
        <?php if ($errors): ?>
            <div class="notice error" role="alert"><?= e(implode(' ', $errors)) ?></div>
        <?php endif; ?>
        <form method="post" action="register.php">
            <?= csrf_field() ?>
            <label for="full_name">Full name</label>
            <input id="full_name" name="full_name" value="<?= e($values['full_name']) ?>" maxlength="100" autocomplete="name" required>
            <label for="username">Username</label>
            <input id="username" name="username" value="<?= e($values['username']) ?>" minlength="3" maxlength="50" pattern="[A-Za-z0-9_]+" autocomplete="username" required>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?= e($values['email']) ?>" maxlength="255" autocomplete="email" required>
            <label for="password">Password <span class="label-hint">(8–72 bytes)</span></label>
            <input id="password" name="password" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
            <label for="confirm_password">Confirm password</label>
            <input id="confirm_password" name="confirm_password" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
            <button class="button button-primary button-wide" type="submit">Create account <span aria-hidden="true">→</span></button>
        </form>
        <p class="auth-switch">Already have an account? <a href="index.php">Log in</a></p>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>

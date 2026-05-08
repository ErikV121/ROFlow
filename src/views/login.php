<?php

require_once __DIR__ . '/../../src/auth/auth.php';
$pdo = require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../src/repository/UserRepository.php';

if (is_logged_in()) {
    redirect('/dashboard');
}

$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        set_flash('error', 'Please enter both username and password.');
    } else {
        $userRepo = new UserRepository($pdo);
        $user = $userRepo->findByEmail($username);

        if ($user && password_verify($password, $user->getPasswordHash())) {
            login_user($user);
            set_flash('success', 'Welcome back, ' . $user->getFullName() . '.');
            redirect('/dashboard');
        } else {
            set_flash('error', 'Invalid username or password.');
        }
    }
}

$pageTitle = 'Sign In';
require_once __DIR__ . '/../views/templates/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-card__brand">
            <div class="auth-card__logo">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="#fff">
                    <path d="M2 4h16v2H2zM2 9h10v2H2zM2 14h7v2H2z"/>
                </svg>
            </div>
            <span class="auth-card__brand-name">ROFlow</span>
        </div>

        <h1 class="auth-card__title">Sign in to your account</h1>

        <form method="POST" action="<?= e(url('/login')) ?>" class="auth-form">
            <?= csrf_field() ?>

            <label class="form-field">
                <span class="form-field__label">Username</span>
                <input type="text" name="username" value="<?= e($username) ?>"
                       autocomplete="username" required autofocus>
            </label>

            <label class="form-field">
                <span class="form-field__label">Password</span>
                <input type="password" name="password"
                       autocomplete="current-password" required>
            </label>

            <button type="submit" class="btn btn--primary btn--full">Sign in</button>
        </form>

        <p class="auth-card__alt">
            Don't have an account? <a href="<?= e(url('/register')) ?>">Register</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../views/templates/footer.php'; ?>

<?php

require_once __DIR__ . '/../../src/auth/auth.php';
$pdo = require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../repository/UserRepository.php';

if (is_logged_in()) {
    redirect('/dashboard');
}

$form = ['username' => '', 'full_name' => '', 'role' => 'advisor'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $form['username']  = trim($_POST['username'] ?? '');
    $form['full_name'] = trim($_POST['full_name'] ?? '');
    $form['role']      = $_POST['role'] ?? 'advisor';
    $password          = $_POST['password'] ?? '';
    $passwordConfirm   = $_POST['password_confirm'] ?? '';

    $errors = [];
    if (strlen($form['username']) < 3)              $errors[] = 'Username must be at least 3 characters.';
    if (strlen($form['full_name']) < 2)             $errors[] = 'Please enter your full name.';
    if (strlen($password) < 8)                      $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $passwordConfirm)             $errors[] = 'Passwords do not match.';
    if (!in_array($form['role'], ['advisor','technician'], true)) $errors[] = 'Pick a valid role.';

    $userRepo = new UserRepository($pdo);
    // Username goes into the unique `email` column — check that exact field.
    if (empty($errors) && $userRepo->findByEmail($form['username'])) {
        $errors[] = 'That username is already taken.';
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $newId = $userRepo->create(
                $form['username'],
                $hashedPassword,
                $form['full_name'],
                \Enum\Role::from($form['role'])
        );
        $newUser = $userRepo->findById($newId);
        login_user($newUser);
        set_flash('success', 'Account created. Welcome aboard!');
        redirect('/dashboard');
    }

    foreach ($errors as $err) set_flash('error', $err);
}

$pageTitle = 'Register';
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

            <h1 class="auth-card__title">Create an account</h1>

            <form method="POST" action="<?= e(url('/register')) ?>" class="auth-form">
                <?= csrf_field() ?>

                <label class="form-field">
                    <span class="form-field__label">Full name</span>
                    <input type="text" name="full_name" value="<?= e($form['full_name']) ?>" required>
                </label>

                <label class="form-field">
                    <span class="form-field__label">Username</span>
                    <input type="text" name="username" value="<?= e($form['username']) ?>" required>
                </label>

                <label class="form-field">
                    <span class="form-field__label">Role</span>
                    <select name="role">
                        <option value="advisor"    <?= $form['role'] === 'advisor'    ? 'selected' : '' ?>>Service Advisor</option>
                        <option value="technician" <?= $form['role'] === 'technician' ? 'selected' : '' ?>>Technician</option>
                    </select>
                </label>

                <label class="form-field">
                    <span class="form-field__label">Password</span>
                    <input type="password" name="password" required>
                </label>

                <label class="form-field">
                    <span class="form-field__label">Confirm password</span>
                    <input type="password" name="password_confirm" required>
                </label>

                <button type="submit" class="btn btn--primary btn--full">Create account</button>
            </form>

            <p class="auth-card__alt">
                Already have an account? <a href="<?= e(url('/login')) ?>">Sign in</a>
            </p>
        </div>
    </div>

<?php require_once __DIR__ . '/../views/templates/footer.php'; ?>

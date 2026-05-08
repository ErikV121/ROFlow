<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($errorTitle ?? 'Link not available') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background: #f6f7f9;
            color: #1a1a1a;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .invalid-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 40px 32px;
            max-width: 460px;
            text-align: center;
            margin: 16px;
        }
        .invalid-card h1 {
            font-size: 20px;
            margin: 0 0 12px;
        }
        .invalid-card p {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }
        .invalid-icon {
            font-size: 32px;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>
<div class="invalid-card">
    <div class="invalid-icon">🔒</div>
    <h1><?= e($errorTitle ?? 'Link not available') ?></h1>
    <p><?= e($errorMessage ?? 'Please contact the shop for assistance.') ?></p>
</div>
</body>
</html>
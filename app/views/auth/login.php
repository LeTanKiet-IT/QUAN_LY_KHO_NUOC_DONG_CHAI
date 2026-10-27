
<?php
require_once __DIR__ . '/../../config/config.php';

$f = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập | Quản lý kho hàng</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/app.css">
</head>
<body>

<div class="auth-page">

    <section class="auth-visual">
        <div class="brand" style="color:white">
            <span class="brand-mark">▦</span>
            QUẢN LÝ KHO HÀNG
        </div>

        <div>
            <div class="auth-art">▤</div>

            <h1>
                Quản lý hiệu quả.<br>
                Kiểm soát chính xác.
            </h1>

            <p>
                Theo dõi hàng hóa, xử lý hàng lỗi và kiểm soát
                quyền truy cập trong một không gian làm việc thống nhất.
            </p>
        </div>

        <small>
            © <?= date('Y') ?> Hệ thống Quản lý kho hàng
        </small>
    </section>

    <section class="auth-card-wrap">
        <div class="auth-card">

            <div class="brand-mark"
                 style="display:inline-block;margin-bottom:20px">
                ▦
            </div>

            <h2>Chào mừng trở lại</h2>
            <p>Đăng nhập để tiếp tục sử dụng hệ thống</p>

            <?php if ($f): ?>
                <div class="flash <?= htmlspecialchars(
                    $f['type'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>">
                    <?= htmlspecialchars(
                        $f['text'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= BASE_URL ?>/login">

                <div class="field">
                    <label for="username">Tên đăng nhập *</label>
                    <input
                        class="input"
                        id="username"
                        type="text"
                        name="username"
                        placeholder="Nhập tên đăng nhập"
                        autocomplete="username"
                        required
                        autofocus
                    >
                </div>

                <div class="field">
                    <label for="password">Mật khẩu *</label>
                    <input
                        class="input"
                        id="password"
                        type="password"
                        name="password"
                        placeholder="Nhập mật khẩu"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary">
                    Đăng nhập →
                </button>

            </form>

            <div class="hint">
                Tài khoản demo: <b>admin</b><br>
                Mật khẩu demo: <b>password</b><br>
                Đổi mật khẩu demo trước khi triển khai thực tế.
            </div>

        </div>
    </section>

</div>

</body>
</html>

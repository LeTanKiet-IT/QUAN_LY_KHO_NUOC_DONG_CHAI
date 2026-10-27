<?php

$u = \Auth::user();

$f = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';

?>

<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        <?= htmlspecialchars($title ?? APP_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/app.css"
    >
</head>

<body>

<?php if (($layout ?? '') === 'auth') return; ?>

<header class="topbar">

    <a class="brand" href="<?= BASE_URL ?>/">
        <span class="brand-mark">▦</span>
        QUẢN LÝ KHO HÀNG
    </a>

    <div class="top-user">

        <span class="avatar">
            <?= htmlspecialchars(mb_substr($u['HoTen'] ?? 'U', 0, 1)) ?>
        </span>

        <div class="uname">
            <b>
                <?= htmlspecialchars($u['HoTen'] ?? '') ?>
            </b>

            <small>
                <?= htmlspecialchars($u['TenVaiTro'] ?? '') ?>
            </small>
        </div>

    </div>

</header>

<div class="app-shell">

    <aside class="sidebar">

        <div class="nav-label">
            KHÔNG GIAN LÀM VIỆC
        </div>

        <!-- Trang chủ -->
        <a
            class="nav-link <?= str_ends_with($path, '/') ? 'active' : '' ?>"
            href="<?= BASE_URL ?>/"
        >
            <span class="ico">⌂</span>
            <span class="nav-text">Trang chủ</span>
        </a>

        <!-- Quản lý tài khoản -->
        <?php
        if (
            ($u['TenVaiTro'] ?? '') === 'Admin'
            || in_array(
                'TAIKHOAN_XEM',
                $u['permissions'] ?? [],
                true
            )
        ):
        ?>

            <a
                class="nav-link <?= str_contains($path, 'accounts') ? 'active' : '' ?>"
                href="<?= BASE_URL ?>/accounts"
            >
                <span class="ico">♙</span>
                <span class="nav-text">Quản lý tài khoản</span>
            </a>

        <?php endif; ?>

        <!-- Quản lý hàng lỗi / trả về -->
        <a
            class="nav-link <?= str_contains($path, 'defects') ? 'active' : '' ?>"
            href="<?= BASE_URL ?>/defects"
        >
            <span class="ico">▤</span>
            <span class="nav-text">Hàng lỗi / Trả về</span>
        </a>
        <!--Nhập kho-->
        <a
            class="nav-link <?= str_contains($path, 'imports') ? 'active' : '' ?>"
            href="<?= BASE_URL ?>/imports"
        >
            <span class="ico">▤</span>
            <span class="nav-text">Nhập kho</span>
        </a>

        <!-- Đăng xuất -->
        <div class="sidebar-bottom">

            <form
                method="post"
                action="<?= BASE_URL ?>/logout"
                onsubmit="return confirm('Bạn có chắc chắn muốn đăng xuất?')"
            >
                <button class="nav-link" type="submit">
                    <span class="ico">⇥</span>
                    <span class="nav-text">Đăng xuất</span>
                </button>
            </form>

        </div>

    </aside>

    <main class="main">

        <!-- Thông báo -->
        <?php if ($f): ?>

            <div class="flash <?= htmlspecialchars($f['type']) ?>">
                <?= htmlspecialchars($f['text']) ?>
            </div>

        <?php endif; ?>
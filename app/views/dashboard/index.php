
<div class="page-heading">
    <div>
        <h1>
            Xin chào,
            <?= htmlspecialchars(\Auth::user()['HoTen']) ?>
            👋
        </h1>

        <p>
            Tổng quan nhanh tình hình xử lý hàng lỗi và trả về.
        </p>
    </div>

    <a class="btn btn-primary" href="<?= BASE_URL ?>/defects">
        ＋ Quản lý hàng lỗi
    </a>
</div>

<div class="stat-grid">

    <div class="stat">
        <span class="stat-icon">▤</span>

        <div>
            <small>Tổng phiếu hàng lỗi</small>

            <strong>
                <?= number_format((int) ($stats['total'] ?? 0)) ?>
            </strong>
        </div>
    </div>

    <div class="stat">
        <span class="stat-icon">◷</span>

        <div>
            <small>Chờ duyệt</small>

            <strong>
                <?= number_format((int) ($stats['pending'] ?? 0)) ?>
            </strong>
        </div>
    </div>

    <div class="stat">
        <span class="stat-icon">⚙</span>

        <div>
            <small>Đang xử lý</small>

            <strong>
                <?= number_format((int) ($stats['processing'] ?? 0)) ?>
            </strong>
        </div>
    </div>

    <div class="stat">
        <span class="stat-icon">✓</span>

        <div>
            <small>Đã xử lý</small>

            <strong>
                <?= number_format((int) ($stats['done'] ?? 0)) ?>
            </strong>
        </div>
    </div>

</div>

<div class="panel">

    <div class="panel-title">
        <h2>Truy cập nhanh</h2>
    </div>

    <p class="muted">
        Chọn nghiệp vụ theo vai trò được cấp.
        Admin quản lý tài khoản và phân quyền;
        nhân viên kho ghi nhận phiếu hàng lỗi/trả về
        và cập nhật hướng xử lý.
    </p>

    
    <div class="quick-actions">
        <a class="btn btn-light" href="<?= BASE_URL ?>/defects">
            ▤ Hàng lỗi / Trả về
        </a>

        <a class="btn btn-light" href="<?= BASE_URL ?>/imports">
            ▤ Nhập kho
        </a>

        <?php if ((Auth::user()['TenVaiTro'] ?? '') === 'Admin'): ?>
            <a class="btn btn-light" href="<?= BASE_URL ?>/accounts">
                ♙ Quản lý tài khoản &amp; phân quyền
            </a>
        <?php endif; ?>
    </div>


</div>

<?php
$can = fn(string $p) => (($_SESSION['user']['TenVaiTro'] ?? '') === 'Admin') || in_array($p, $_SESSION['user']['permissions'] ?? [], true);
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$qs = fn(int $p) => BASE_URL . '/plans?' . http_build_query(['q' => $q, 'status' => $status, 'page' => $p]);
?>
<div class="page-heading">
    <div>
        <h1>Kế hoạch nguyên liệu</h1>
        <p>Lập, theo dõi và chỉnh sửa kế hoạch nguyên liệu gửi Ban giám đốc phê duyệt.</p>
    </div>
    <?php if ($can('KEHOACH_QUANLY')): ?>
        <a class="btn btn-primary" href="<?= BASE_URL ?>/plans/create">＋ Lập kế hoạch</a>
    <?php endif; ?>
</div>

<section class="panel">
    <form class="filters" method="get" action="<?= BASE_URL ?>/plans">
        <div class="field search">
            <label for="q">Tìm kiếm</label>
            <input class="input" id="q" type="text" name="q" value="<?= $h($q) ?>" placeholder="Mã kế hoạch hoặc người lập...">
        </div>
        <div class="field">
            <label for="status">Trạng thái</label>
            <select class="select" id="status" name="status">
                <option value="">Tất cả</option>
                <?php foreach (PlanModel::STATUS as $k => [$label]): ?>
                    <option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $h($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>&nbsp;</label>
            <button class="btn btn-primary" type="submit">Tìm kiếm / Lọc</button>
        </div>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Mã kế hoạch</th><th>Ngày lập</th><th>Người lập</th>
                    <th>Thời gian sử dụng</th><th>Số dòng</th><th>Trạng thái</th><th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$items): ?>
                <tr><td colspan="7">Không có kế hoạch nào phù hợp.</td></tr>
            <?php endif; ?>
            <?php foreach ($items as $r): [$label, $color] = PlanModel::STATUS[$r['TrangThai']] ?? [$r['TrangThai'], 'gray']; ?>
                <tr>
                    <td><b><?= $h($r['MaKeHoach']) ?></b></td>
                    <td><?= $h(date('d/m/Y H:i', strtotime($r['NgayLap']))) ?></td>
                    <td><?= $h($r['NguoiLap']) ?></td>
                    <td><?= $h(date('d/m/Y', strtotime($r['ThoiGianSuDung']))) ?></td>
                    <td><?= (int)$r['SoDong'] ?></td>
                    <td><span class="badge <?= $color ?>"><?= $h($label) ?></span></td>
                    <td class="actions">
                        <a class="btn btn-light btn-sm" href="<?= BASE_URL ?>/plans/detail?id=<?= urlencode($r['MaKeHoach']) ?>">Xem</a>
                        <?php if ($can('KEHOACH_QUANLY') && in_array($r['TrangThai'], PlanModel::EDITABLE, true)): ?>
                            <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/plans/edit?id=<?= urlencode($r['MaKeHoach']) ?>">Sửa</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
        <div class="form-actions" style="justify-content:space-between;align-items:center">
            <span class="hint">Tổng <?= (int)$total ?> kế hoạch · Trang <?= $page ?>/<?= $pages ?></span>
            <span>
                <?php if ($page > 1): ?><a class="btn btn-light btn-sm" href="<?= $qs($page - 1) ?>">‹ Trước</a><?php endif; ?>
                <?php if ($page < $pages): ?><a class="btn btn-light btn-sm" href="<?= $qs($page + 1) ?>">Sau ›</a><?php endif; ?>
            </span>
        </div>
    <?php endif; ?>
</section>

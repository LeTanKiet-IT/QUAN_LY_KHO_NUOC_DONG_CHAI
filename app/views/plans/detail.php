<?php
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$num = fn($v) => rtrim(rtrim(number_format((float)$v, 2, ',', '.'), '0'), ',');
[$label, $color] = PlanModel::STATUS[$plan['TrangThai']] ?? [$plan['TrangThai'], 'gray'];
$canEdit = (($_SESSION['user']['TenVaiTro'] ?? '') === 'Admin' || in_array('KEHOACH_QUANLY', $_SESSION['user']['permissions'] ?? [], true))
    && in_array($plan['TrangThai'], PlanModel::EDITABLE, true);
?>
<div class="page-heading">
    <div>
        <h1>Kế hoạch <?= $h($plan['MaKeHoach']) ?></h1>
        <p>Lập bởi <?= $h($plan['NguoiLap']) ?> lúc <?= $h(date('d/m/Y H:i', strtotime($plan['NgayLap']))) ?></p>
    </div>
    <div class="actions">
        <a class="btn btn-light" href="<?= BASE_URL ?>/plans">‹ Danh sách</a>
        <?php if ($canEdit): ?>
            <a class="btn btn-primary" href="<?= BASE_URL ?>/plans/edit?id=<?= urlencode($plan['MaKeHoach']) ?>">Sửa kế hoạch</a>
        <?php endif; ?>
    </div>
</div>

<section class="panel">
    <div class="form-grid">
        <div class="field"><label>Trạng thái</label><div><span class="badge <?= $color ?>"><?= $h($label) ?></span></div></div>
        <div class="field"><label>Thời gian sử dụng</label><div><?= $h(date('d/m/Y', strtotime($plan['ThoiGianSuDung']))) ?></div></div>
        <div class="field span2"><label>Ghi chú</label><div><?= $plan['GhiChu'] ? nl2br($h($plan['GhiChu'])) : '—' ?></div></div>
        <?php if (!empty($plan['LyDo']) && in_array($plan['TrangThai'], ['CHO_CHINH_SUA', 'TU_CHOI'], true)): ?>
            <div class="field span2">
                <label><?= $plan['TrangThai'] === 'TU_CHOI' ? 'Lý do từ chối' : 'Ban giám đốc yêu cầu chỉnh sửa' ?></label>
                <div class="flash error" style="margin:0"><?= nl2br($h($plan['LyDo'])) ?></div>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="panel">
    <div class="panel-title">Danh sách nguyên liệu</div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Mã</th><th>Tên nguyên liệu</th><th>ĐVT</th><th>Số lượng cần</th><th>Tồn kho</th><th>Cần mua thêm (dự kiến)</th><th>Ghi chú</th></tr>
            </thead>
            <tbody>
            <?php foreach ($lines as $l): ?>
                <tr>
                    <td><?= $h($l['MaHang']) ?></td>
                    <td><?= $h($l['TenHang']) ?></td>
                    <td><?= $h($l['DonViTinh']) ?></td>
                    <td><?= $num($l['SoLuongCan']) ?></td>
                    <td><?= $num($l['TonKho']) ?></td>
                    <td><b><?= $num($l['CanMua']) ?></b></td>
                    <td><?= $h($l['GhiChu'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="hint">Tồn kho là tổng tồn của các kho, chỉ để tham khảo. Cần mua thêm = số lượng cần − tồn kho (không âm).</p>
</section>

<?php
$h = static fn($x) => htmlspecialchars((string)($x ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$fmt = static fn($x) => number_format((float)$x, 2, ',', '.');
?>
<div class="page-heading">
    <div><h1>Phiếu xuất kho <?= $h($export['MaPhieuXuat']) ?></h1><p>Đã xuất kho – ngày <?= $h($export['NgayXuat']) ?></p></div>
    <a class="btn btn-light" href="<?= BASE_URL ?>/exports">‹ Quay về Lập phiếu xuất</a>
</div>
<section class="panel">
    <div class="form-grid">
        <div class="field"><label>Mã yêu cầu</label><div><?= $h($export['MaYeuCau']) ?></div></div>
        <div class="field"><label>Chứng từ liên quan</label><div><?= $h($export['MaChungTu'] ?: '—') ?></div></div>
        <div class="field"><label>Thành phẩm</label><div><?= $h($export['MaHang'] . ' - ' . $export['TenHang']) ?></div></div>
        <div class="field"><label>Kho xuất</label><div><?= $h($export['TenKho']) ?></div></div>
        <div class="field"><label>Người nhận</label><div><?= $h($export['NguoiNhan']) ?></div></div>
        <div class="field"><label>Số lượng xuất</label><div><strong><?= $fmt($export['SoLuongXuat']) . ' ' . $h($export['DonViTinh']) ?></strong></div></div>
    </div>
</section>
<section class="panel">
    <h2>Chi tiết lô hàng đã xuất (FEFO)</h2>
    <div class="table-wrap"><table>
        <thead><tr><th>Mã lô</th><th>Hạn sử dụng</th><th>Số lượng xuất</th></tr></thead>
        <tbody>
            <?php foreach ($lines as $line): ?><tr>
                <td><?= $h($line['MaLo']) ?></td><td><?= $h($line['HanSuDung'] ?? '—') ?></td>
                <td><?= $fmt($line['SoLuongXuat']) . ' ' . $h($export['DonViTinh']) ?></td>
            </tr><?php endforeach; ?>
        </tbody>
    </table></div>
</section>

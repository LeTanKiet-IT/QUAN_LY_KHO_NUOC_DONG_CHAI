<?php
$h = static fn($x) => htmlspecialchars((string)($x ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$fmt = static fn($x) => number_format((float)$x, 2, ',', '.');
$now = date('Y-m-d');
$hasUnknownExpiry = false;
foreach ($lots as $lot) { if (!$lot['HanSuDung']) { $hasUnknownExpiry = true; } }
?>
<div class="export-page">
    <div class="page-heading"><div><h1>LẬP PHIẾU XUẤT KHO</h1>
    <p>FR17 – Xuất thành phẩm theo yêu cầu, chọn lô theo FEFO và cập nhật tồn kho.</p></div></div>

    <section class="export-card">
        <h2>Thông tin yêu cầu xuất kho</h2>
        <form method="get" action="<?= BASE_URL ?>/exports" class="export-fields">
            <div class="export-field full"><label for="req">Mã yêu cầu xuất kho *</label>
                <select id="req" name="request" required>
                    <option value="">-- Chọn yêu cầu xuất kho --</option>
                    <?php foreach ($requests as $r): ?>
                    <option value="<?= $h($r['MaYeuCau']) ?>" <?= ($selected['MaYeuCau'] ?? '') === $r['MaYeuCau'] ? 'selected' : '' ?>>
                        <?= $h($r['MaYeuCau'] . ' — ' . $r['TenHang'] . ' — còn ' . $r['SoLuongConLai'] . ' ' . $r['DonViTinh']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div><button type="submit" class="export-button">Xem yêu cầu</button></div>
        </form>
        <?php if (!$requests): ?>
        <p class="export-warning">Chưa có yêu cầu xuất đủ điều kiện. FR17 không tự tạo yêu cầu mẫu; cần nhập yêu cầu thực tế vào bảng <code>yeu_cau_xuat_kho</code> trước khi lập phiếu.</p>
        <?php endif; ?>

        <?php if ($selected): ?>
            <div class="export-fields export-readonly">
                <div><label>Mã thành phẩm</label><input readonly value="<?= $h($selected['MaHang']) ?>"></div>
                <div><label>Tên thành phẩm</label><input readonly value="<?= $h($selected['TenHang']) ?>"></div>
                <div><label>Kho xuất</label><input readonly value="<?= $h($selected['TenKho']) ?>"></div>
                <div><label>Chứng từ liên quan</label><input readonly value="<?= $h($selected['MaChungTu'] ?: 'Không có') ?>"></div>
                <div><label>Số lượng yêu cầu</label><input readonly value="<?= $fmt($selected['SoLuongYeuCau']) . ' ' . $h($selected['DonViTinh']) ?>"></div>
                <div><label>Số lượng còn được xuất</label><input readonly value="<?= $fmt($selected['SoLuongConLai']) . ' ' . $h($selected['DonViTinh']) ?>"></div>
                <div><label>Tồn tổng tại kho xuất</label><input readonly value="<?= $fmt($stock) . ' ' . $h($selected['DonViTinh']) ?>"></div>
            </div>
            <h2>Danh sách lô hàng tại kho (FEFO)</h2>
            <p class="export-hint">Hệ thống gợi ý lô chưa hết hạn có HSD gần nhất. Nhân viên có thể xác nhận hoặc chọn lô khác. Mỗi phiếu xuất dùng một lô theo đặc tả.</p>
            <div class="export-table-wrap"><table class="export-table">
                <thead><tr><th>Mã lô</th><th>HSD</th><th>Còn trong lô</th><th>Điều kiện</th></tr></thead>
                <tbody>
                <?php foreach ($lots as $lot): ?>
                <tr><td><?= $h($lot['MaLo']) ?></td><td><?= $h($lot['HanSuDung'] ?: 'Chưa có HSD') ?></td>
                    <td><?= $fmt($lot['SoLuongCon']) . ' ' . $h($selected['DonViTinh']) ?></td>
                    <td><?= !$lot['HanSuDung'] ? 'Cần bổ sung HSD' : ($lot['HanSuDung'] < $now ? 'Đã hết hạn – không xuất' : 'Có thể xuất') ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$lots): ?><tr><td colspan="4">Không tìm thấy lô còn hàng.</td></tr><?php endif; ?>
                </tbody>
            </table></div>
            <?php if ($hasUnknownExpiry): ?>
            <p class="export-warning">Có lô thiếu HSD. Cần cập nhật HSD thực tế trên MySQL trước khi xuất, để bảo đảm đúng FEFO.</p>
            <?php endif; ?>

            <h2>Thông tin phiếu xuất kho</h2>
            <form id="export-submit" method="post" action="<?= BASE_URL ?>/exports/save" onsubmit="return confirm('Xác nhận lập phiếu xuất kho và trừ tồn kho thực tế?')">
                <input type="hidden" name="csrf_token" value="<?= $h($_SESSION['csrf_export'] ?? '') ?>">
                <input type="hidden" name="request_id" value="<?= $h($selected['MaYeuCau']) ?>">
                <div class="export-fields">
                    <div><label for="lot_id">Lô hàng xuất (gợi ý FEFO) *</label>
                        <select id="lot_id" name="lot_id" required>
                            <option value="">-- Chọn lô hàng --</option>
                            <?php $suggested = false; foreach ($lots as $lot): ?>
                                <?php $eligible = $lot['HanSuDung'] && $lot['HanSuDung'] >= $now && (float)$lot['SoLuongCon'] > 0; ?>
                                <option value="<?= $h($lot['MaLo']) ?>" data-available="<?= $h($lot['SoLuongCon']) ?>"
                                    <?= !$eligible ? 'disabled' : '' ?>
                                    <?= $eligible && !$suggested ? 'selected' : '' ?>>
                                    <?= $h($lot['MaLo'].' – HSD '.($lot['HanSuDung'] ?: 'chưa có').' – tồn '.$lot['SoLuongCon']) ?>
                                    <?= $eligible && !$suggested ? ' (FEFO đề xuất)' : '' ?>
                                </option>
                                <?php if ($eligible && !$suggested) { $suggested = true; } ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div><label for="recipient">Người nhận hàng *</label>
                        <input id="recipient" name="recipient" maxlength="120" required value="<?= $h($selected['NguoiNhan']) ?>"></div>
                    <div><label for="quantity">Số lượng thực xuất (<?= $h($selected['DonViTinh']) ?>) *</label>
                        <input id="quantity" name="quantity" type="number" min="0.01" step="0.01"
                               max="<?= $h($selected['SoLuongConLai']) ?>" data-request-max="<?= $h($selected['SoLuongConLai']) ?>"
                               placeholder="Nhập số lượng" value="<?= $h($selected['SoLuongConLai']) ?>" required></div>
                </div>
                <div class="export-actions"><button type="submit" class="export-button">Lưu phiếu và xuất kho</button></div>
                <p class="export-hint">Thao tác ghi thật vào MySQL và trừ tồn của lô đã xác nhận. Nếu muốn xuất tiếp từ lô khác, lập phiếu tiếp theo cho phần yêu cầu còn lại.</p>
            </form>
        <?php endif; ?>
    </section>

    <section class="export-card">
        <h2>Phiếu xuất đã lập</h2>
        <div class="export-table-wrap"><table class="export-table">
            <thead><tr><th>Mã phiếu</th><th>Ngày lập</th><th>Yêu cầu</th><th>Thành phẩm</th><th>Kho</th><th>Số lượng</th></tr></thead>
            <tbody>
            <?php foreach ($history as $row): ?>
                <tr><td><a href="<?= BASE_URL ?>/exports/detail?id=<?= rawurlencode($row['MaPhieuXuat']) ?>"><?= $h($row['MaPhieuXuat']) ?></a></td>
                    <td><?= $h($row['NgayXuat']) ?></td><td><?= $h($row['MaYeuCau']) ?></td>
                    <td><?= $h($row['TenHang']) ?></td><td><?= $h($row['TenKho']) ?></td>
                    <td><?= $fmt($row['SoLuongXuat']) . ' ' . $h($row['DonViTinh']) ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$history): ?><tr><td colspan="6">Chưa có phiếu xuất nào.</td></tr><?php endif; ?>
            </tbody>
        </table></div>
    </section>
</div>
<style>
.export-page{max-width:1100px;margin:0 auto;display:grid;gap:18px}.export-page h1{color:#183b68}
.export-card{background:#fff;border:1px solid #e2e8f0;border-radius:13px;padding:26px;box-shadow:0 4px 15px #0000000a}
.export-card h2{font-size:19px;color:#2878d0;margin:20px 0 14px;padding-bottom:12px;border-bottom:1px solid #ddd}
.export-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;align-items:end}
.export-fields .full{grid-column:1/-1}.export-field label,.export-fields label{display:block;font-weight:600;margin-bottom:5px}
.export-fields input,.export-fields select{width:100%;padding:11px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:#1e293b}
.export-readonly{margin-top:18px}.export-readonly input{background:#f8fafc}.export-button{background:#2878d0;border:0;border-radius:8px;color:#fff;font-weight:600;padding:12px 18px;cursor:pointer}
.export-actions{margin-top:18px}.export-warning{background:#fff7ed;border:1px solid #fdba74;color:#9a3412;padding:12px;border-radius:8px}
.export-hint{color:#64748b;font-size:13px}.export-table-wrap{overflow-x:auto}.export-table{width:100%;border-collapse:collapse;min-width:650px}
.export-table th,.export-table td{text-align:left;padding:11px;border-bottom:1px solid #e2e8f0}.export-table th{background:#f1f5f9;font-size:12px;text-transform:uppercase;color:#475569}
@media(max-width:740px){.export-fields{grid-template-columns:1fr}.export-card{padding:16px}}
</style>

<script>
(function() {
    const select = document.getElementById('lot_id');
    const quantity = document.getElementById('quantity');
    if (!select || !quantity) return;
    function updateMax() {
        const option = select.options[select.selectedIndex];
        const available = Number(option && option.dataset.available || 0);
        const requestMax = Number(quantity.dataset.requestMax || 0);
        const max = Math.max(0, Math.min(available, requestMax));
        quantity.max = String(max);
        if (max > 0 && (!quantity.value || Number(quantity.value) > max)) quantity.value = String(max);
    }
    select.addEventListener('change', updateMax);
    updateMax();
})();
</script>

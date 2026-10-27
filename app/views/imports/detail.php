<div class="page-heading import-detail-heading">
    <div>
        <h1>Chi tiết phiếu nhập kho</h1>
        <p>Xem thông tin phiếu và các mặt hàng đã nhập.</p>
    </div>
    <a class="btn btn-secondary" href="<?= BASE_URL ?>/imports">← Quay lại danh sách</a>
</div>

<div class="import-info-card">
    <div class="import-info-top">
        <div>
            <span class="info-label">Mã phiếu nhập</span>
            <h2><?= htmlspecialchars($import['MaPhieuNhap'] ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
        </div>
        <span class="status-pill"><?= htmlspecialchars($import['TrangThai'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    </div>

    <div class="import-info-grid">
        <div><span class="info-label">Ngày nhập</span><strong><?= htmlspecialchars($import['NgayNhap'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></div>
        <div><span class="info-label">Loại nhập</span><strong><?= ($import['LoaiNhap'] ?? '') === 'NGUYEN_LIEU' ? 'Nguyên liệu' : 'Thành phẩm' ?></strong></div>
        <div><span class="info-label">Chứng từ nguồn</span><strong><?= htmlspecialchars($import['MaChungTu'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></div>
        <div><span class="info-label">Kho tiếp nhận</span><strong><?= htmlspecialchars($import['TenKho'] ?? $import['MaKho'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></div>
        <div><span class="info-label">Mã người lập</span><strong><?= htmlspecialchars($import['MaNguoiLap'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></div>
    </div>
</div>

<div class="import-detail-section">
    <h2>Danh sách hàng hóa đã nhập</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Mã hàng</th>
                    <th>Tên hàng</th>
                    <th>Đơn vị tính</th>
                    <th>Số lượng nhập</th>
                    <th>Mã chi tiết nguồn</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($details)): ?>
                <tr><td colspan="6" style="text-align:center;padding:24px">Phiếu này chưa có chi tiết hàng hóa.</td></tr>
            <?php else: ?>
                <?php $totalQuantity = 0; ?>
                <?php foreach ($details as $index => $item): ?>
                    <?php $quantity = (float)($item['SoLuong'] ?? 0); $totalQuantity += $quantity; ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($item['MaHang'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($item['TenHang'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($item['DonViTinh'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= number_format($quantity, 2) ?></td>
                        <td><?= htmlspecialchars((string)($item['MaChiTietNguon'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="import-total-row">
                    <td colspan="4" style="text-align:right"><strong>Tổng số lượng nhập</strong></td>
                    <td><strong><?= number_format($totalQuantity, 2) ?></strong></td>
                    <td></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.import-detail-heading{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:24px}
.import-info-card{padding:22px;background:#fff;border:1px solid #e2e8f0;border-radius:14px;margin-bottom:24px}
.import-info-top{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;padding-bottom:18px;border-bottom:1px solid #e2e8f0}
.import-info-top h2{margin:6px 0 0;font-size:20px;overflow-wrap:anywhere}
.info-label{display:block;color:#64748b;font-size:12px;margin-bottom:7px}
.import-info-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:22px;padding-top:20px}
.import-info-grid strong{display:block;font-size:14px;overflow-wrap:anywhere}
.import-detail-section h2{font-size:20px;margin:0 0 14px}
.import-total-row{background:#f8fafc}
@media(max-width:760px){.import-info-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:480px){.import-info-grid{grid-template-columns:1fr}}
</style>

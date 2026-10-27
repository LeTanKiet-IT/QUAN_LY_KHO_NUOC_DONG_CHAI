<div class="page-heading" style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:20px">
    <div>
        <h1>Phiếu nhập kho</h1>
        <p>Theo dõi, tìm kiếm và lọc các phiếu nhập kho đã được tạo.</p>
    </div>
    <a class="btn btn-primary" href="<?= BASE_URL ?>/imports/create">+ Lập phiếu nhập kho</a>
</div>

<?php if (!empty($_GET['success'])): ?>
    <div role="alert" style="display:flex;gap:12px;align-items:flex-start;padding:16px;border:1px solid #bbf7d0;background:#f0fdf4;color:#166534;border-radius:12px;margin-bottom:20px">
        <span style="font-size:22px">✓</span>
        <div><strong>Lập phiếu nhập kho thành công!</strong><br>
            Mã phiếu: <strong><?= htmlspecialchars($_GET['success'], ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
        <button type="button" onclick="this.parentElement.remove()" style="margin-left:auto;border:0;background:transparent;color:inherit;font-size:22px;cursor:pointer" aria-label="Đóng">&times;</button>
    </div>
<?php endif; ?>

<form method="get" action="<?= BASE_URL ?>/imports"
      class="import-filter-form"
      style="padding:18px;border:1px solid #dbe4f0;border-radius:12px;background:#fff;margin-bottom:20px">

    <div class="import-filter-grid"
         style="display:grid;grid-template-columns:minmax(220px,2fr) repeat(4,minmax(140px,1fr));gap:12px;align-items:end">

        <div>
            <label for="import-q">Từ khóa</label>
            <input id="import-q" type="search" name="q"
                   value="<?= htmlspecialchars($q ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   placeholder="Mã phiếu, chứng từ, kho, người lập"
                   style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px">
        </div>

        <div>
            <label for="import-type">Loại nhập</label>
            <select id="import-type" name="type"
                    style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px">
                <option value="">Tất cả loại nhập</option>
                <option value="NGUYEN_LIEU" <?= ($type ?? '') === 'NGUYEN_LIEU' ? 'selected' : '' ?>>Nguyên liệu</option>
                <option value="THANH_PHAM" <?= ($type ?? '') === 'THANH_PHAM' ? 'selected' : '' ?>>Thành phẩm</option>
            </select>
        </div>

        <div>
            <label for="import-status">Trạng thái</label>
            <select id="import-status" name="status"
                    style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px">
                <option value="">Tất cả trạng thái</option>
                <option value="DA_NHAP" <?= ($status ?? '') === 'DA_NHAP' ? 'selected' : '' ?>>Đã nhập</option>
            </select>
        </div>

        <div>
            <label for="from_date">Từ ngày</label>
            <input
                type="date"
                id="from_date"
                name="from_date"
                value="<?= htmlspecialchars($fromDate ?? '', ENT_QUOTES, 'UTF-8') ?>"
                max="<?= htmlspecialchars($toDate ?? '', ENT_QUOTES, 'UTF-8') ?>"
                style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px">
        </div>

        <div>
            <label for="to_date">Đến ngày</label>
            <input
                type="date"
                id="to_date"
                name="to_date"
                value="<?= htmlspecialchars($toDate ?? '', ENT_QUOTES, 'UTF-8') ?>"
                min="<?= htmlspecialchars($fromDate ?? '', ENT_QUOTES, 'UTF-8') ?>"
                style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px">
        </div>
    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px">
        <button class="btn btn-primary" type="submit">Tìm kiếm / Lọc</button>
        <a class="btn" href="<?= BASE_URL ?>/imports"
           style="display:inline-flex;align-items:center;padding:9px 14px;border:1px solid #cbd5e1;border-radius:8px;text-decoration:none">
            Xóa bộ lọc
        </a>
    </div>
</form>

<!-- Kết quả tìm kiếm -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;gap:12px;flex-wrap:wrap">
    <strong>Kết quả: <?= count($imports ?? []) ?> phiếu</strong>
</div>

<!-- Danh sách phiếu nhập kho -->
<div class="table-wrap" style="overflow-x:auto">
    <table>
        <thead>
            <tr>
                <th>Mã phiếu nhập</th>
                <th>Ngày nhập</th>
                <th>Loại nhập</th>
                <th>Chứng từ nguồn</th>
                <th>Kho tiếp nhận</th>
                <th>Người lập</th>
                <th>Trạng thái</th>
            </tr>
        </thead>

        <tbody>
            <?php if (empty($imports)): ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:28px">
                        Không tìm thấy phiếu nhập kho phù hợp.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($imports as $item): ?>
                    <tr>
                        <td>
                            <a
                                class="import-detail-link"
                                href="<?= BASE_URL ?>/imports/detail?id=<?= urlencode($item['MaPhieuNhap'] ?? '') ?>"
                            >
                                <strong>
                                    <?= htmlspecialchars(
                                        $item['MaPhieuNhap'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </strong>
                            </a>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $item['NgayNhap'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>

                        <td>
                            <?= ($item['LoaiNhap'] ?? '') === 'NGUYEN_LIEU'
                                ? 'Nguyên liệu'
                                : 'Thành phẩm' ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $item['MaChungTu'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $item['TenKho'] ?? $item['MaKho'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $item['MaNguoiLap'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>

                        <td>
                            <span class="status-pill">
                                <?= htmlspecialchars(
                                    $item['TrangThai'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.import-detail-link {
    color: #1769c2;
    text-decoration: none;
}

.import-detail-link:hover {
    text-decoration: underline;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const fromDate = document.getElementById('from_date');
    const toDate = document.getElementById('to_date');

    if (!fromDate || !toDate) return;

    function updateDateLimits(changed) {
        // Không cho ngày bắt đầu lớn hơn ngày kết thúc.
        toDate.min = fromDate.value || '';
        fromDate.max = toDate.value || '';

        if (
            fromDate.value &&
            toDate.value &&
            fromDate.value > toDate.value
        ) {
            if (changed === 'from') {
                toDate.value = fromDate.value;
            } else if (changed === 'to') {
                fromDate.value = toDate.value;
            }
        }
    }

    fromDate.addEventListener('change', function () {
        updateDateLimits('from');
    });

    toDate.addEventListener('change', function () {
        updateDateLimits('to');
    });

    updateDateLimits();
});
</script>

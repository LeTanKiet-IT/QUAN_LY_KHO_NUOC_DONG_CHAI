<?php
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$isEdit = $plan !== null;
$date = $plan['ThoiGianSuDung'] ?? '';
$note = $plan['GhiChu'] ?? '';
?>
<div class="page-heading">
    <div>
        <h1><?= $isEdit ? 'Chỉnh sửa kế hoạch ' . $h($plan['MaKeHoach']) : 'Lập kế hoạch nguyên liệu' ?></h1>
        <p>Chọn nguyên liệu và số lượng cần dùng. Sau khi lưu, kế hoạch chuyển sang “Chờ phê duyệt”.</p>
    </div>
    <a class="btn btn-light" href="<?= BASE_URL ?>/plans">‹ Danh sách</a>
</div>

<?php if ($isEdit && !empty($plan['LyDo'])): ?>
    <div class="flash error">Ban giám đốc yêu cầu: <?= $h($plan['LyDo']) ?></div>
<?php endif; ?>

<form id="plan-form" method="post" action="<?= BASE_URL ?>/plans/save">
    <input type="hidden" name="id" value="<?= $isEdit ? $h($plan['MaKeHoach']) : '' ?>">

    <section class="panel">
        <div class="form-grid">
            <div class="field">
                <label for="date">Thời gian sử dụng *</label>
                <input class="input" type="date" id="date" name="date" required min="<?= date('Y-m-d') ?>" value="<?= $h($date) ?>">
            </div>
            <div class="field span2">
                <label for="note">Ghi chú</label>
                <textarea class="textarea" id="note" name="note" maxlength="500" rows="2"><?= $h($note) ?></textarea>
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-title">Nguyên liệu</div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th style="min-width:240px">Nguyên liệu</th><th>ĐVT</th><th>Tồn kho</th><th>Số lượng cần *</th><th>Ghi chú</th><th></th></tr>
                </thead>
                <tbody id="lines"></tbody>
            </table>
        </div>
        <div class="form-actions">
            <button type="button" class="btn btn-light" id="add-line">＋ Thêm nguyên liệu</button>
        </div>
        <p class="hint">Tồn kho chỉ để tham khảo. Mỗi nguyên liệu chỉ xuất hiện một lần.</p>
    </section>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Lưu và gửi phê duyệt</button>
        <a class="btn btn-light" href="<?= BASE_URL ?>/plans">Hủy</a>
    </div>
</form>

<script>
(function () {
    const materials = <?= json_encode($materials, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const existing = <?= json_encode(array_map(fn($l) => ['code' => $l['MaHang'], 'qty' => $l['SoLuongCan'], 'note' => $l['GhiChu']], $lines), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const tbody = document.getElementById('lines');
    const fmt = n => new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 2 }).format(n);

    function addLine(v) {
        v = v || {};
        const tr = document.createElement('tr');
        const opts = materials.map(m =>
            `<option value="${m.MaHang}" ${m.MaHang === v.code ? 'selected' : ''}>${m.MaHang} - ${m.TenHang.replace(/</g, '&lt;')}</option>`).join('');
        tr.innerHTML = `
            <td><select class="select" name="code[]" required><option value="">-- Chọn --</option>${opts}</select></td>
            <td class="unit">—</td>
            <td class="stock">—</td>
            <td><input class="input" type="number" name="qty[]" min="0.01" step="0.01" required value="${v.qty ?? ''}" style="width:120px"></td>
            <td><input class="input" type="text" name="line_note[]" maxlength="300" value="${(v.note || '').replace(/"/g, '&quot;')}"></td>
            <td><button type="button" class="btn btn-danger btn-sm remove">Xóa</button></td>`;
        const sel = tr.querySelector('select');
        const sync = () => {
            const m = materials.find(x => x.MaHang === sel.value);
            tr.querySelector('.unit').textContent = m ? m.DonViTinh : '—';
            tr.querySelector('.stock').textContent = m ? fmt(m.TonKho) : '—';
        };
        sel.addEventListener('change', sync);
        tr.querySelector('.remove').addEventListener('click', () => tr.remove());
        tbody.appendChild(tr);
        sync();
    }

    document.getElementById('add-line').addEventListener('click', () => addLine());
    (existing.length ? existing : [{}]).forEach(addLine);

    document.getElementById('plan-form').addEventListener('submit', function (e) {
        const codes = [...tbody.querySelectorAll('select')].map(s => s.value).filter(Boolean);
        if (!codes.length) { alert('Vui lòng thêm ít nhất một nguyên liệu.'); e.preventDefault(); return; }
        if (new Set(codes).size !== codes.length) { alert('Có nguyên liệu bị trùng trong kế hoạch.'); e.preventDefault(); return; }
        if (!confirm('Xác nhận lưu kế hoạch và gửi chờ phê duyệt?')) e.preventDefault();
    });
})();
</script>

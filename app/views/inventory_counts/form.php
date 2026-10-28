<?php
$h = static fn($v) => htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');
?>
<div class="page-heading"><div><h1>Lập biên bản kiểm kê</h1>
<p>Kế hoạch: <strong><?= $h($planId) ?></strong> · Kho: <strong><?= $h($warehouseName) ?></strong></p></div></div>
<p><a class="btn btn-secondary" href="<?= BASE_URL ?>/inventory-counts?plan=<?= urlencode($planId) ?>">← Quay lại</a></p>
<p>Nhập số lượng thực tế của từng hàng hóa. Có thể nhập <strong>0</strong> khi đã đếm và không có hàng; để trống nếu <strong>chưa kiểm đếm</strong> và muốn lưu tạm.</p>
<?php if(!$rows): ?><p>Kho chưa có dòng dữ liệu tồn kho. Không thể lập biên bản.</p><?php else: ?>
<form method="post" action="<?= BASE_URL ?>/inventory-counts/save" id="uc22-form">
<input type="hidden" name="csrf" value="<?= $h($csrf) ?>">
<input type="hidden" name="plan" value="<?= $h($planId) ?>">
<input type="hidden" name="warehouse" value="<?= $h($warehouseId) ?>">
<div style="overflow-x:auto"><table style="background:#fff;width:100%;border-collapse:collapse">
<thead><tr style="background:#f3f5fa">
<th style="padding:12px;text-align:left">Hàng hóa</th><th>ĐVT</th><th>Tồn hệ thống</th><th>Thực tế</th><th>Chênh lệch</th><th>Tình trạng thực tế</th><th>Ghi chú bất thường</th>
</tr></thead><tbody>
<?php foreach($rows as $row):
 $code=(string)$row['MaHang']; $draft=$draftRows[$code]??[];
 $actual=$draft['SoLuongThucTe']??'';
?>
<tr style="border-top:1px solid #e2e2e2">
<td style="padding:10px"><?= $h($row['TenHang']) ?><small style="display:block"><?= $h($code) ?></small></td>
<td><?= $h($row['DonViTinh']) ?></td>
<td><span class="uc22-stock" data-value="<?= $h($row['SoLuongTon']) ?>"><?= $h($row['SoLuongTon']) ?></span></td>
<td><input type="hidden" name="snapshots[<?= $h($code) ?>]" value="<?= $h($row['SoLuongTon']) ?>"><input aria-label="Số lượng thực tế <?= $h($code) ?>" style="width:125px;padding:7px" type="number" min="0" step="0.01" max="999999999999.99" name="counts[<?= $h($code) ?>]" class="uc22-actual" value="<?= $h($actual) ?>"></td>
<td><strong class="uc22-diff">—</strong></td>
<td><input style="width:170px;padding:7px" maxlength="100" placeholder="Bình thường / Hư hỏng..." name="conditions[<?= $h($code) ?>]" value="<?= $h($draft['TinhTrangThucTe']??'') ?>"></td>
<td><input style="width:200px;padding:7px" maxlength="500" placeholder="Ghi nhận tình trạng bất thường" name="notes[<?= $h($code) ?>]" value="<?= $h($draft['GhiChu']??'') ?>"></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<div style="margin:16px 0;display:flex;gap:10px;flex-wrap:wrap">
<button type="submit" class="btn btn-secondary" name="action" value="draft">Lưu tạm</button>
<button type="submit" class="btn" name="action" value="confirm" onclick="return confirm('Xác nhận lập biên bản kiểm kê? Sau khi xác nhận sẽ không sửa được trong UC22.')">Xác nhận lập biên bản</button>
<a class="btn btn-secondary" href="<?= BASE_URL ?>/inventory-counts">Hủy thao tác / quay lại</a>
</div>
</form>
<?php if($existing): ?>
<form method="post" action="<?= BASE_URL ?>/inventory-counts/cancel" onsubmit="return confirm('Xóa biên bản nháp hiện tại? Thao tác này không thể hoàn tác.')">
<input type="hidden" name="csrf" value="<?= $h($csrf) ?>">
<input type="hidden" name="id" value="<?= $h($existing['MaBienBan']) ?>">
<button class="btn btn-secondary" type="submit">Xóa biên bản nháp</button>
</form>
<?php endif; ?>
<?php endif; ?>
<script>
// Hien thi chenh lech de nguoi kiem ke xem truoc. May chu van tinh lai khi luu.
document.querySelectorAll('#uc22-form tbody tr').forEach(tr => {
  const actual=tr.querySelector('.uc22-actual');
  const stock=Number(tr.querySelector('.uc22-stock').dataset.value);
  const diff=tr.querySelector('.uc22-diff');
  const update=()=>{
    if(actual.value===''){ diff.textContent='—'; return; }
    const v=Number(actual.value);
    diff.textContent=Number.isFinite(v) ? (v-stock).toFixed(2) : 'Không hợp lệ';
  };
  actual.addEventListener('input',update);
  update();
});
</script>

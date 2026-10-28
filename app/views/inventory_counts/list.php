<div class="page-heading"><div><h1>UC22 – Lập biên bản kiểm kê</h1>
<p>Chỉ kiểm kê theo kế hoạch đã được phê duyệt và kho thuộc phạm vi.</p></div></div>
<div style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:10px;margin-bottom:20px">
<form method="get" action="<?= BASE_URL ?>/inventory-counts">
<label for="plan">1. Chọn kế hoạch kiểm kê đã duyệt</label><br>
<select id="plan" name="plan" required style="min-width:280px;padding:9px;margin:8px 0">
<option value="">-- Chọn kế hoạch --</option>
<?php foreach($plans as $p): ?>
<option value="<?= htmlspecialchars($p['MaKeHoachKiemKe'],ENT_QUOTES) ?>" <?= $selectedPlan===$p['MaKeHoachKiemKe']?'selected':'' ?>>
<?= htmlspecialchars($p['MaKeHoachKiemKe'].' - '.$p['NgayKiemKe'].' - '.$p['PhamViKiemKe']) ?>
</option>
<?php endforeach; ?>
</select>
<button class="btn" type="submit">Xem kho trong kế hoạch</button>
</form>
<?php if(!$plans): ?><p>Chưa có kế hoạch đã phê duyệt. Cần lập và duyệt kế hoạch trước.</p><?php endif; ?>
<?php if($selectedPlan): ?>
<h3>2. Chọn kho cần kiểm kê</h3>
<?php if(!$warehouses): ?><p>Không có kho thuộc kế hoạch này, hoặc kế hoạch không hợp lệ.</p><?php endif; ?>
<?php foreach($warehouses as $w): ?>
<div style="margin:8px 0">
<?= htmlspecialchars($w['TenKho'].' ('.$w['MaKho'].')') ?>
<?php if($w['TrangThai']==='ACTIVE'): ?>
<a class="btn" href="<?= BASE_URL ?>/inventory-counts/create?plan=<?= urlencode($selectedPlan) ?>&warehouse=<?= urlencode($w['MaKho']) ?>">Mở kiểm kê</a>
<?php else: ?><em>Kho đang bị khóa</em><?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
<h2>Biên bản đã tạo</h2>
<div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse;background:#fff">
<thead><tr><th style="padding:12px">Mã biên bản</th><th>Kế hoạch</th><th>Kho</th><th>Người lập</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
<?php foreach($records as $r): ?>
<tr style="border-top:1px solid #ddd">
<td style="padding:10px"><?= htmlspecialchars($r['MaBienBan']) ?></td>
<td><?= htmlspecialchars($r['MaKeHoachKiemKe']) ?></td>
<td><?= htmlspecialchars($r['TenKho']) ?></td>
<td><?= htmlspecialchars($r['NguoiLapTen']) ?></td>
<td><?= $r['TrangThai']==='NHAP'?'Đang thực hiện':htmlspecialchars($r['TrangThai']) ?></td>
<td><a href="<?= BASE_URL ?>/inventory-counts/detail?id=<?= urlencode($r['MaBienBan']) ?>">Xem chi tiết</a></td>
</tr>
<?php endforeach; ?>
<?php if(!$records): ?><tr><td colspan="6" style="padding:16px">Chưa có biên bản.</td></tr><?php endif; ?>
</tbody></table></div>

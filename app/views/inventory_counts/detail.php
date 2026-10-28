<?php $h=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); ?>
<div class="page-heading"><div><h1>Chi tiết biên bản kiểm kê</h1>
<p><?= $h($record['MaBienBan']) ?> · <?= $h($record['NgayKiemKe']) ?></p></div></div>
<p><a class="btn btn-secondary" href="<?= BASE_URL ?>/inventory-counts">← Danh sách</a></p>
<div style="background:#fff;padding:18px;border:1px solid #ddd;border-radius:10px;margin-bottom:16px">
<p><strong>Kế hoạch:</strong> <?= $h($record['MaKeHoachKiemKe']) ?> · <?= $h($record['PhamViKiemKe']) ?></p>
<p><strong>Kho:</strong> <?= $h($record['TenKho']) ?> · <strong>Người lập:</strong> <?= $h($record['NguoiLapTen']) ?></p>
<p><strong>Trạng thái:</strong> <?= $record['TrangThai']==='NHAP'?'Đang thực hiện':$h($record['TrangThai']) ?></p>
<?php if($record['TrangThai']==='NHAP'): ?>
<a class="btn" href="<?= BASE_URL ?>/inventory-counts/create?plan=<?= urlencode($record['MaKeHoachKiemKe']) ?>&warehouse=<?= urlencode($record['MaKho']) ?>">Tiếp tục kiểm kê</a>
<?php endif; ?>
</div>
<div style="overflow-x:auto"><table style="background:#fff;width:100%;border-collapse:collapse">
<thead><tr style="background:#f3f5fa"><th style="padding:10px">Mặt hàng</th><th>ĐVT</th><th>Hệ thống</th><th>Thực tế</th><th>Chênh lệch</th><th>Tình trạng</th><th>Ghi chú</th><th>Xử lý</th></tr></thead>
<tbody><?php foreach($rows as $r): ?>
<tr style="border-top:1px solid #ddd"><td style="padding:10px"><?= $h($r['TenHang']) ?> (<?= $h($r['MaHang']) ?>)</td>
<td><?= $h($r['DonViTinh']) ?></td><td><?= $h($r['SoLuongHeThong']) ?></td>
<td><?= $r['SoLuongThucTe']===null?'Chưa đếm':$h($r['SoLuongThucTe']) ?></td>
<td><?= $r['SoLuongChenhLech']===null?'—':$h($r['SoLuongChenhLech']) ?></td>
<td><?= $h($r['TinhTrangThucTe']) ?></td><td><?= $h($r['GhiChu']) ?></td>
<td><?= $h($r['TrangThaiXuLy']) ?></td></tr>
<?php endforeach; ?></tbody></table></div>
<p style="margin-top:12px"><small>UC22 chỉ ghi nhận chênh lệch. Không tự điều chỉnh tồn kho. Chênh lệch chờ nghiệp vụ phê duyệt/xử lý riêng.</small></p>

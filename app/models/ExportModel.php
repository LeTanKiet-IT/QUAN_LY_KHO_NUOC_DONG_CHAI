<?php
/** FR17: nhân viên xác nhận lô FEFO hoặc chọn lô khác theo đặc tả. */
class ExportModel
{
    private PDO $db;
    public function __construct() { $this->db = Database::conn(); }

    /** Số lượng thập phân 2 chữ số; dùng số nguyên (x100) để tránh lỗi float. */
    public static function toCents(string $raw): int
    {
        $raw = trim($raw);
        if (!preg_match('/^(0|[1-9][0-9]{0,9})(?:\.([0-9]{1,2}))?$/D', $raw, $m)) {
            throw new InvalidArgumentException('Số lượng phải là số dương, tối đa 2 chữ số thập phân.');
        }
        $value = (int)$m[1] * 100 + (int)str_pad($m[2] ?? '', 2, '0');
        if ($value <= 0) { throw new InvalidArgumentException('Số lượng phải lớn hơn 0.'); }
        return $value;
    }

    private static function units(int $cents): string
    {
        return intdiv($cents, 100) . '.' . str_pad((string)($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    /** Chuyển DECIMAL DB sang cents (cho phép giá trị 0). */
    private static function dbCents($value): int
    {
        $str = (string)$value;
        if (!preg_match('/^(0|[1-9][0-9]{0,9})(?:\.([0-9]{1,2}))?$/D', $str, $m)) {
            throw new RuntimeException('Số lượng dữ liệu không hợp lệ: ' . $str);
        }
        return (int)$m[1] * 100 + (int)str_pad($m[2] ?? '', 2, '0');
    }

    /** Chỉ các yêu cầu đã điều phối đến nhân viên đang đăng nhập (Admin quản trị có thể xem tất cả). */
    public function requests(string $userId, bool $isAdmin = false): array
    {
        $sql = "SELECT y.*, h.TenHang, h.DonViTinh, k.TenKho,
                       (y.SoLuongYeuCau - y.SoLuongDaXuat) AS SoLuongConLai
                FROM yeu_cau_xuat_kho y
                JOIN hang_hoa h ON y.MaHang = h.MaHang
                JOIN kho k ON y.MaKho = k.MaKho
                WHERE y.TrangThai IN ('CHO_XUAT', 'DA_XUAT_MOT_PHAN')
                  AND h.LoaiHang = 'THANH_PHAM' AND h.TrangThai = 'ACTIVE'
                  AND k.TrangThai = 'ACTIVE'";
        if (!$isAdmin) { $sql .= ' AND y.MaNhanVienDuocGiao = ?'; }
        $sql .= ' ORDER BY y.NgayTao DESC, y.MaYeuCau DESC';
        $s = $this->db->prepare($sql);
        $s->execute($isAdmin ? [] : [$userId]);
        return $s->fetchAll();
    }

    public function request(string $id, bool $lock = false, ?string $userId = null, bool $isAdmin = false): ?array
    {
        $sql = "SELECT y.*, h.TenHang, h.DonViTinh, h.LoaiHang, h.TrangThai AS HangTrangThai,
                       k.TenKho, k.TrangThai AS KhoTrangThai,
                       (y.SoLuongYeuCau - y.SoLuongDaXuat) AS SoLuongConLai
                FROM yeu_cau_xuat_kho y
                JOIN hang_hoa h ON h.MaHang = y.MaHang
                JOIN kho k ON k.MaKho = y.MaKho
                WHERE y.MaYeuCau = ?";
        $params = [$id];
        if (!$isAdmin && $userId !== null) {
            $sql .= ' AND y.MaNhanVienDuocGiao = ?';
            $params[] = $userId;
        }
        $sql .= $lock ? ' FOR UPDATE' : '';
        $s = $this->db->prepare($sql);
        $s->execute($params);
        return $s->fetch() ?: null;
    }

    /** Các lô còn hàng, gồm lô thiếu HSD để hiển thị cảnh báo. */
    public function lots(string $warehouse, string $product, bool $lock = false): array
    {
        $sql = "SELECT l.MaLo, l.HanSuDung, l.SoLuongNhap, l.SoLuongDaXuat,
                       (l.SoLuongNhap - l.SoLuongDaXuat) AS SoLuongCon
                FROM lo_hang l
                JOIN phieu_nhap_kho pn ON pn.MaPhieuNhap=l.MaPhieuNhap
                WHERE pn.MaKho=? AND l.MaHang=? AND l.TrangThai='DA_NHAP_KHO'
                  AND l.SoLuongNhap > l.SoLuongDaXuat
                ORDER BY CASE WHEN l.HanSuDung IS NULL THEN 0 ELSE 1 END,
                         l.HanSuDung ASC, l.MaLo ASC" . ($lock ? ' FOR UPDATE' : '');
        $s = $this->db->prepare($sql);
        $s->execute([$warehouse, $product]);
        return $s->fetchAll();
    }

    public function stock(string $warehouse, string $product): string
    {
        $s = $this->db->prepare('SELECT SoLuongTon FROM ton_kho WHERE MaKho=? AND MaHang=?');
        $s->execute([$warehouse, $product]);
        return (string)($s->fetchColumn() ?: '0.00');
    }

    public function history(string $userId, bool $isAdmin = false): array
    {
        $sql = "SELECT p.MaPhieuXuat, p.MaYeuCau, p.NgayXuat,
                       p.SoLuongXuat, h.TenHang, h.DonViTinh, k.TenKho
                FROM phieu_xuat_kho p
                JOIN hang_hoa h ON h.MaHang=p.MaHang
                JOIN kho k ON k.MaKho=p.MaKho";
        if (!$isAdmin) { $sql .= ' WHERE p.MaNguoiLap=?'; }
        $sql .= ' ORDER BY p.NgayXuat DESC LIMIT 30';
        $s = $this->db->prepare($sql);
        $s->execute($isAdmin ? [] : [$userId]);
        return $s->fetchAll();
    }

    public function detail(string $id, string $userId, bool $isAdmin = false): ?array
    {
        $sql = "SELECT p.*, h.TenHang, h.DonViTinh, k.TenKho, y.MaChungTu
                FROM phieu_xuat_kho p
                JOIN hang_hoa h ON p.MaHang=h.MaHang
                JOIN kho k ON p.MaKho=k.MaKho
                JOIN yeu_cau_xuat_kho y ON p.MaYeuCau=y.MaYeuCau
                WHERE p.MaPhieuXuat=?";
        $params = [$id];
        if (!$isAdmin) { $sql .= ' AND p.MaNguoiLap=?'; $params[] = $userId; }
        $s = $this->db->prepare($sql);
        $s->execute($params);
        return $s->fetch() ?: null;
    }

    public function detailLines(string $id): array
    {
        $s = $this->db->prepare("SELECT d.MaLo, d.SoLuongXuat, l.HanSuDung
            FROM chi_tiet_phieu_xuat d JOIN lo_hang l ON l.MaLo=d.MaLo
            WHERE d.MaPhieuXuat=? ORDER BY l.HanSuDung, d.MaLo");
        $s->execute([$id]);
        return $s->fetchAll();
    }

    /** Xuất từ đúng MỘT lô đã chọn; server kiểm tra tồn lô trong transaction. */
    public function issue(string $requestId, string $lotId, string $rawQuantity, string $recipient,
                          string $userId, string $requestNonce, bool $isAdmin = false): string
    {
        if (!preg_match('/^[a-f0-9]{64}$/D', $requestNonce)) {
            throw new InvalidArgumentException('Mã xác thực biểu mẫu không hợp lệ.');
        }
        $lotId = trim($lotId);
        if ($lotId === '' || mb_strlen($lotId) > 30) {
            throw new InvalidArgumentException('Vui lòng chọn lô hàng cần xuất.');
        }
        $amount = self::toCents($rawQuantity);
        $recipient = trim($recipient);
        if ($recipient === '' || mb_strlen($recipient) > 120) {
            throw new InvalidArgumentException('Người nhận hàng không hợp lệ (tối đa 120 ký tự).');
        }
        $this->db->beginTransaction();
        try {
            // Lock request -> aggregate stock -> lots in the same order on each export.
            $request = $this->request($requestId, true, $userId, $isAdmin);
            if (!$request || !in_array($request['TrangThai'], ['CHO_XUAT','DA_XUAT_MOT_PHAN'], true)) {
                throw new RuntimeException('Yêu cầu xuất không tồn tại, chưa được giao cho bạn hoặc đã đóng.');
            }
            if ($request['LoaiHang'] !== 'THANH_PHAM' || $request['HangTrangThai'] !== 'ACTIVE' || $request['KhoTrangThai'] !== 'ACTIVE') {
                throw new RuntimeException('Kho hoặc thành phẩm không đủ điều kiện xuất.');
            }
            $remainingRequest = self::dbCents($request['SoLuongConLai']);
            if ($amount > $remainingRequest) { throw new RuntimeException('Số lượng vượt yêu cầu còn lại.'); }

            $s = $this->db->prepare('SELECT SoLuongTon FROM ton_kho WHERE MaKho=? AND MaHang=? FOR UPDATE');
            $s->execute([$request['MaKho'], $request['MaHang']]);
            $rawStock = $s->fetchColumn();
            $stock = $rawStock === false ? 0 : self::dbCents($rawStock);
            if ($stock < $amount) {
                throw new RuntimeException('Số lượng thực xuất không được vượt quá số lượng tồn kho.');
            }

            $lots = $this->lots($request['MaKho'], $request['MaHang'], true);
            $totalLots = 0;
            $selectedLot = null;
            foreach ($lots as $lot) {
                $left = self::dbCents($lot['SoLuongCon']);
                $totalLots += $left;
                if (!$lot['HanSuDung']) {
                    throw new RuntimeException('Có lô chưa có hạn sử dụng. Cần bổ sung HSD thực tế để xác định FEFO.');
                }
                if ($lot['MaLo'] === $lotId) { $selectedLot = $lot; }
            }
            if ($totalLots !== $stock) {
                throw new RuntimeException('Tồn tổng và tồn theo lô không khớp. Cần đối soát trước khi xuất kho.');
            }
            if (!$selectedLot) {
                throw new RuntimeException('Lô hàng không thuộc thành phẩm/kho xuất hoặc đã hết số lượng tồn.');
            }
            if ($selectedLot['HanSuDung'] < date('Y-m-d')) {
                throw new RuntimeException('Không được xuất lô hàng đã hết hạn sử dụng.');
            }
            if ($amount > self::dbCents($selectedLot['SoLuongCon'])) {
                throw new RuntimeException('Số lượng thực xuất không được vượt quá số lượng tồn kho của lô đã chọn.');
            }

            $id = 'PX' . date('ymdHis') . strtoupper(bin2hex(random_bytes(4)));
            $qty = self::units($amount);
            $s = $this->db->prepare("INSERT INTO phieu_xuat_kho
                 (MaPhieuXuat,MaYeuCau,RequestNonce,MaKho,MaHang,MaNguoiLap,NguoiNhan,SoLuongXuat,TrangThai)
                 VALUES (?,?,?,?,?,?,?,?,'DA_XUAT')");
            $s->execute([$id,$requestId,$requestNonce,$request['MaKho'],$request['MaHang'],$userId,$recipient,$qty]);

            $s = $this->db->prepare('UPDATE lo_hang SET SoLuongDaXuat=SoLuongDaXuat+? WHERE MaLo=? AND SoLuongNhap-SoLuongDaXuat>=?');
            $s->execute([$qty,$lotId,$qty]);
            if ($s->rowCount() !== 1) { throw new RuntimeException('Tồn lô vừa thay đổi. Vui lòng thử lại.'); }
            $s = $this->db->prepare('INSERT INTO chi_tiet_phieu_xuat (MaPhieuXuat,MaLo,SoLuongXuat) VALUES (?,?,?)');
            $s->execute([$id,$lotId,$qty]);

            $s = $this->db->prepare('UPDATE ton_kho SET SoLuongTon=SoLuongTon-? WHERE MaKho=? AND MaHang=? AND SoLuongTon>=?');
            $s->execute([$qty,$request['MaKho'],$request['MaHang'],$qty]);
            if ($s->rowCount() !== 1) { throw new RuntimeException('Không thể cập nhật tồn kho.'); }

            $newStatus = $amount === $remainingRequest ? 'HOAN_THANH' : 'DA_XUAT_MOT_PHAN';
            $s = $this->db->prepare('UPDATE yeu_cau_xuat_kho SET SoLuongDaXuat=SoLuongDaXuat+?, TrangThai=? WHERE MaYeuCau=?');
            $s->execute([$qty,$newStatus,$requestId]);

            $s = $this->db->prepare('INSERT INTO lich_su_thao_tac (MaTaiKhoan,HanhDong,DoiTuong,MaDoiTuong,NoiDung) VALUES (?,?,?,?,?)');
            $s->execute([$userId, 'LAP_PHIEU_XUAT', 'phieu_xuat_kho', $id, 'Yêu cầu '.$requestId.'; lô '.$lotId.'; số lượng '.$qty]);
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            throw $e;
        }
    }
}


<?php

class ImportModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conn();
    }

    /**
     * Lấy danh sách kho đang hoạt động.
     */
    public function getActiveWarehouses(): array
    {
        $sql = "SELECT
            k.MaKho,
            k.TenKho,
            k.SucChua,
            COALESCE(t.TongTon, 0) AS TongTon,
            k.SucChua - COALESCE(t.TongTon, 0) AS SucChuaConLai
        FROM kho k
        LEFT JOIN (
            SELECT MaKho, SUM(SoLuongTon) AS TongTon
            FROM ton_kho
            GROUP BY MaKho
        ) t ON t.MaKho = k.MaKho
        WHERE k.TrangThai = 'ACTIVE'
                ";

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Lấy danh sách chứng từ nguồn đủ điều kiện nhập.
     *
     * Nguyên liệu:
     * - Đơn mua đã duyệt.
     * - Kết quả kiểm định đạt.
     *
     * Thành phẩm:
     * - Chứng từ sản xuất hoàn thành.
     */
    public function getEligibleSources(string $type): array
    {
        if (!in_array(
            $type,
            ['NGUYEN_LIEU', 'THANH_PHAM'],
            true
        )) {
            return [];
        }

        $sql = "
            SELECT
                c.MaChungTu,
                c.LoaiNhap,
                c.NgayChungTu,
                COUNT(ct.MaChiTiet) AS SoMatHang
            FROM chung_tu_nguon c
            INNER JOIN chi_tiet_chung_tu_nguon ct
                ON ct.MaChungTu = c.MaChungTu
            WHERE c.LoaiNhap = ?
              AND (
                    (
                        c.LoaiNhap = 'NGUYEN_LIEU'
                        AND c.TrangThai = 'DA_DUYET'
                        AND c.KetQuaKiemDinh = 'DAT'
                    )
                    OR
                    (
                        c.LoaiNhap = 'THANH_PHAM'
                        AND c.TrangThai = 'HOAN_THANH'
                    )
              )
              AND ct.SoLuong > ct.SoLuongDaNhap
            GROUP BY
                c.MaChungTu,
                c.LoaiNhap,
                c.NgayChungTu
            ORDER BY c.NgayChungTu DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$type]);

        return $stmt->fetchAll();
    }

    /**
     * Lấy chi tiết hàng hóa của chứng từ nguồn.
     */
    
    public function getSourceDetails(string $sourceId): array
    {
        $sql = "
    SELECT
        ct.MaChiTiet,
        ct.MaHang,
        h.TenHang,
        h.LoaiHang,
        h.DonViTinh,
        ct.SoLuong,
        ct.SoLuongDaNhap,
        (ct.SoLuong - ct.SoLuongDaNhap) AS SoLuongConLai
    FROM chi_tiet_chung_tu_nguon ct
    INNER JOIN hang_hoa h
        ON h.MaHang = ct.MaHang
    INNER JOIN chung_tu_nguon c
        ON c.MaChungTu = ct.MaChungTu
    WHERE c.MaChungTu = ?
      AND (
          (
              c.LoaiNhap = 'NGUYEN_LIEU'
              AND c.TrangThai = 'DA_DUYET'
              AND c.KetQuaKiemDinh = 'DAT'
          )
          OR
          (
              c.LoaiNhap = 'THANH_PHAM'
              AND c.TrangThai = 'HOAN_THANH'
          )
      )
      AND h.TrangThai = 'ACTIVE'
      AND ct.SoLuong > ct.SoLuongDaNhap
";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sourceId]);

        return $stmt->fetchAll();
    }
        
    
/**
 * Lưu phiếu nhập kho và cập nhật tồn kho trong một transaction.
 *
 * $quantities có dạng:
 * [
 *     MaChiTietNguon => SoLuongThucNhan
 * ]
 */
public function saveImport(
    string $sourceId,
    string $warehouseId,
    string $userId,
    array $quantities
): string {
    if ($sourceId === '' || $warehouseId === '' || empty($quantities)) {
        throw new InvalidArgumentException(
            'Vui lòng chọn chứng từ, kho và nhập số lượng.'
        );
    }

    $this->db->beginTransaction();

    try {
        // 1. Khóa chứng từ nguồn để tránh nhập vượt số lượng.
        $stmt = $this->db->prepare("
            SELECT MaChungTu, LoaiNhap, TrangThai, KetQuaKiemDinh
            FROM chung_tu_nguon
            WHERE MaChungTu = ?
            FOR UPDATE
        ");
        $stmt->execute([$sourceId]);
        $source = $stmt->fetch();

        if (!$source) {
            throw new RuntimeException('Chứng từ nguồn không tồn tại.');
        }

        $type = $source['LoaiNhap'];

        $eligible =
            ($type === 'NGUYEN_LIEU'
                && $source['TrangThai'] === 'DA_DUYET'
                && $source['KetQuaKiemDinh'] === 'DAT')
            ||
            ($type === 'THANH_PHAM'
                && $source['TrangThai'] === 'HOAN_THANH');

        if (!$eligible) {
            throw new RuntimeException(
                'Chứng từ chưa đủ điều kiện nhập kho.'
            );
        }

        // 2. Kiểm tra kho tiếp nhận.
        $stmt = $this->db->prepare("
            SELECT MaKho, SucChua
            FROM kho
            WHERE MaKho = ? AND TrangThai = 'ACTIVE'
            FOR UPDATE
        ");
        $stmt->execute([$warehouseId]);
        $warehouse = $stmt->fetch();

        if (!$warehouse) {
            throw new RuntimeException(
                'Kho không tồn tại hoặc đã bị khóa.'
            );
        }

        // 3. Kiểm tra tài khoản người lập.
        $stmt = $this->db->prepare("
            SELECT MaTaiKhoan
            FROM tai_khoan
            WHERE MaTaiKhoan = ? AND TrangThai = 'ACTIVE'
        ");
        $stmt->execute([$userId]);

        if (!$stmt->fetch()) {
            throw new RuntimeException(
                'Tài khoản lập phiếu không hợp lệ.'
            );
        }

        // 4. Đọc và khóa các dòng chi tiết nguồn.
        $stmt = $this->db->prepare("
            SELECT
                ct.MaChiTiet,
                ct.MaHang,
                ct.SoLuong,
                ct.SoLuongDaNhap,
                h.TenHang,
                h.TrangThai AS TrangThaiHang
            FROM chi_tiet_chung_tu_nguon ct
            INNER JOIN hang_hoa h ON h.MaHang = ct.MaHang
            WHERE ct.MaChungTu = ?
            ORDER BY ct.MaChiTiet
            FOR UPDATE
        ");
        $stmt->execute([$sourceId]);
        $sourceDetails = $stmt->fetchAll();

        $detailMap = [];
        foreach ($sourceDetails as $row) {
            $detailMap[(string)$row['MaChiTiet']] = $row;
        }

        $items = [];
        $totalQuantity = 0.0;

        foreach ($quantities as $detailId => $rawQuantity) {
            $detailId = (string)$detailId;

            // Bỏ qua dòng không được chọn hoặc để trống.
            if ($rawQuantity === '' || $rawQuantity === null) {
                continue;
            }

            if (!isset($detailMap[$detailId])) {
                throw new RuntimeException(
                    'Chi tiết hàng hóa không thuộc chứng từ đã chọn.'
                );
            }

            $row = $detailMap[$detailId];

            if ($row['TrangThaiHang'] !== 'ACTIVE') {
                throw new RuntimeException(
                    'Có hàng hóa đã bị khóa.'
                );
            }

            if (!is_numeric($rawQuantity) || (float)$rawQuantity <= 0) {
                throw new RuntimeException(
                    'Số lượng thực nhận phải lớn hơn 0.'
                );
            }

            $quantity = (float)$rawQuantity;
            $remaining = (float)$row['SoLuong']
                - (float)$row['SoLuongDaNhap'];

            if ($quantity > $remaining) {
                throw new RuntimeException(
                    'Số lượng nhập vượt số lượng còn lại của hàng '
                    . $row['MaHang'] . '.'
                );
            }

            $items[] = [
                'detailId' => $detailId,
                'maHang' => $row['MaHang'],
                'quantity' => $quantity
            ];

            $totalQuantity += $quantity;
        }

        if (empty($items)) {
            throw new RuntimeException(
                'Bạn chưa nhập số lượng thực nhận.'
            );
        }

        // 5. Kiểm tra sức chứa.
        // Bản hiện tại giả định tất cả mặt hàng dùng cùng đơn vị
        // sức chứa. Cần đổi sang thể tích/trọng lượng khi có quy tắc thật.
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(SoLuongTon), 0)
            FROM ton_kho
            WHERE MaKho = ?
        ");
        $stmt->execute([$warehouseId]);
        $currentStock = (float)$stmt->fetchColumn();

        if ($currentStock + $totalQuantity
            > (float)$warehouse['SucChua']) {
            throw new RuntimeException(
                'Kho không đủ sức chứa cho số lượng nhập này.'
            );
        }

        // 6. Sinh mã phiếu nhập.
        $importId = 'PN' . date('YmdHis')
            . strtoupper(bin2hex(random_bytes(3)));

        $stmt = $this->db->prepare("
            INSERT INTO phieu_nhap_kho
                (MaPhieuNhap, LoaiNhap, MaChungTu,
                 MaKho, MaNguoiLap, TrangThai)
            VALUES (?, ?, ?, ?, ?, 'DA_NHAP')
        ");
        $stmt->execute([
            $importId,
            $type,
            $sourceId,
            $warehouseId,
            $userId
        ]);

        // 7. Lưu chi tiết, tạo lô hàng và cập nhật tồn.
        foreach ($items as $item) {
            $stmt = $this->db->prepare("
                INSERT INTO chi_tiet_phieu_nhap
                    (MaPhieuNhap, MaHang, MaChiTietNguon, SoLuong)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $importId,
                $item['maHang'],
                $item['detailId'],
                $item['quantity']
            ]);

            $lotId = 'LO' . date('YmdHis')
                . strtoupper(bin2hex(random_bytes(3)));

            $stmt = $this->db->prepare("
                INSERT INTO lo_hang
                    (MaLo, MaHang, MaPhieuNhap, SoLuongNhap, TrangThai)
                VALUES (?, ?, ?, ?, 'DA_NHAP_KHO')
            ");
            $stmt->execute([
                $lotId,
                $item['maHang'],
                $importId,
                $item['quantity']
            ]);

            $stmt = $this->db->prepare("
                INSERT INTO ton_kho (MaKho, MaHang, SoLuongTon)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    SoLuongTon = SoLuongTon + VALUES(SoLuongTon)
            ");
            $stmt->execute([
                $warehouseId,
                $item['maHang'],
                $item['quantity']
            ]);

            $stmt = $this->db->prepare("
                UPDATE chi_tiet_chung_tu_nguon
                SET SoLuongDaNhap = SoLuongDaNhap + ?
                WHERE MaChiTiet = ?
            ");
            $stmt->execute([
                $item['quantity'],
                $item['detailId']
            ]);
        }

        // 8. Ghi nhật ký thao tác.
        $stmt = $this->db->prepare("
            INSERT INTO lich_su_thao_tac
                (MaTaiKhoan, HanhDong, DoiTuong, MaDoiTuong, NoiDung)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            'LAP_PHIEU_NHAP',
            'phieu_nhap_kho',
            $importId,
            'Lập phiếu nhập từ chứng từ ' . $sourceId
        ]);

        $this->db->commit();

        return $importId;

    } catch (Throwable $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }

        throw $e;
    }
}


public function getImportHistory(): array
{
    $sql = "
        SELECT
            pn.MaPhieuNhap,
            pn.NgayNhap,
            pn.LoaiNhap,
            pn.MaChungTu,
            pn.MaKho,
            k.TenKho,
            pn.MaNguoiLap,
            pn.TrangThai,
            COALESCE(ct.TongSoLuongNhap, 0) AS TongSoLuongNhap
        FROM phieu_nhap_kho pn
        LEFT JOIN kho k
            ON k.MaKho = pn.MaKho
        LEFT JOIN (
            SELECT
                MaPhieuNhap,
                SUM(SoLuong) AS TongSoLuongNhap
            FROM chi_tiet_phieu_nhap
            GROUP BY MaPhieuNhap
        ) ct
            ON ct.MaPhieuNhap = pn.MaPhieuNhap
        ORDER BY pn.NgayNhap DESC
    ";

    return $this->db
        ->query($sql)
        ->fetchAll(PDO::FETCH_ASSOC);
}

public function getImportById(string $importId): ?array
{
    $sql = "
        SELECT
            pn.MaPhieuNhap,
            pn.NgayNhap,
            pn.LoaiNhap,
            pn.MaChungTu,
            pn.MaKho,
            k.TenKho,
            pn.MaNguoiLap,
            pn.TrangThai
        FROM phieu_nhap_kho pn
        LEFT JOIN kho k ON k.MaKho = pn.MaKho
        WHERE pn.MaPhieuNhap = ?
        LIMIT 1
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$importId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

/** Lấy các dòng hàng hóa thuộc một phiếu nhập kho. */
public function getImportDetails(string $importId): array
{
    $sql = "
        SELECT
            ctpn.MaHang,
            h.TenHang,
            h.DonViTinh,
            ctpn.SoLuong,
            ctpn.MaChiTietNguon
        FROM chi_tiet_phieu_nhap ctpn
        LEFT JOIN hang_hoa h ON h.MaHang = ctpn.MaHang
        WHERE ctpn.MaPhieuNhap = ?
        ORDER BY ctpn.MaHang
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$importId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getFilteredImportHistory(array $filters = []): array
{
    $sql = "
        SELECT
            pn.MaPhieuNhap,
            pn.NgayNhap,
            pn.LoaiNhap,
            pn.MaChungTu,
            pn.MaKho,
            k.TenKho,
            pn.MaNguoiLap,
            pn.TrangThai
        FROM phieu_nhap_kho pn
        LEFT JOIN kho k ON k.MaKho = pn.MaKho
        WHERE 1 = 1
    ";

    $params = [];

    // Tim theo ma phieu, chung tu nguon, ma kho hoac nguoi lap.
    if (!empty($filters['q'])) {
        $sql .= " AND (
            pn.MaPhieuNhap LIKE ?
            OR pn.MaChungTu LIKE ?
            OR pn.MaKho LIKE ?
            OR k.TenKho LIKE ?
            OR pn.MaNguoiLap LIKE ?
        )";

        $keyword = '%' . $filters['q'] . '%';
        array_push($params, $keyword, $keyword, $keyword, $keyword, $keyword);
    }

    if (!empty($filters['type'])) {
        $sql .= " AND pn.LoaiNhap = ?";
        $params[] = $filters['type'];
    }

    if (!empty($filters['status'])) {
        $sql .= " AND pn.TrangThai = ?";
        $params[] = $filters['status'];
    }

    if (!empty($filters['date_from'])) {
        $sql .= " AND pn.NgayNhap >= ?";
        $params[] = $filters['date_from'] . ' 00:00:00';
    }

    if (!empty($filters['date_to'])) {
        $sql .= " AND pn.NgayNhap < DATE_ADD(?, INTERVAL 1 DAY)";
        $params[] = $filters['date_to'];
    }

    $sql .= " ORDER BY pn.NgayNhap DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}

<?php
class PlanModel
{
    public const STATUS = [
        'NHAP'          => ['Nháp', 'gray'],
        'CHO_PHE_DUYET' => ['Chờ phê duyệt', 'amber'],
        'CHO_CHINH_SUA' => ['Chờ chỉnh sửa', 'blue'],
        'DA_PHE_DUYET'  => ['Đã phê duyệt', 'green'],
        'TU_CHOI'       => ['Từ chối', 'red'],
    ];
    public const EDITABLE = ['NHAP', 'CHO_CHINH_SUA'];

    private PDO $db;
    public function __construct() { $this->db = Database::conn(); }

    public function search(string $q, string $status, int $page, int $perPage): array
    {
        $where = '(k.MaKeHoach LIKE ? OR t.HoTen LIKE ?)';
        $params = ['%' . $q . '%', '%' . $q . '%'];
        if ($status !== '' && isset(self::STATUS[$status])) { $where .= ' AND k.TrangThai=?'; $params[] = $status; }

        $c = $this->db->prepare("SELECT COUNT(*) FROM ke_hoach_nguyen_lieu k JOIN tai_khoan t ON t.MaTaiKhoan=k.MaNguoiLap WHERE $where");
        $c->execute($params);
        $total = (int)$c->fetchColumn();

        $offset = max(0, ($page - 1) * $perPage);
        $sql = "SELECT k.*, t.HoTen AS NguoiLap,
                       (SELECT COUNT(*) FROM chi_tiet_ke_hoach d WHERE d.MaKeHoach=k.MaKeHoach) AS SoDong
                FROM ke_hoach_nguyen_lieu k JOIN tai_khoan t ON t.MaTaiKhoan=k.MaNguoiLap
                WHERE $where ORDER BY k.NgayLap DESC LIMIT $perPage OFFSET $offset";
        $s = $this->db->prepare($sql);
        $s->execute($params);
        return ['rows' => $s->fetchAll(), 'total' => $total];
    }

    public function find(string $id): ?array
    {
        $s = $this->db->prepare('SELECT k.*, t.HoTen AS NguoiLap FROM ke_hoach_nguyen_lieu k JOIN tai_khoan t ON t.MaTaiKhoan=k.MaNguoiLap WHERE k.MaKeHoach=?');
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    /** Dòng chi tiết kèm tồn kho (tổng các kho) và số lượng cần mua thêm dự kiến. */
    public function items(string $id): array
    {
        $s = $this->db->prepare("SELECT d.MaHang, h.TenHang, h.DonViTinh, d.SoLuongCan, d.GhiChu,
                COALESCE((SELECT SUM(tk.SoLuongTon) FROM ton_kho tk WHERE tk.MaHang=d.MaHang),0) AS TonKho
            FROM chi_tiet_ke_hoach d JOIN hang_hoa h ON h.MaHang=d.MaHang WHERE d.MaKeHoach=? ORDER BY d.MaChiTiet");
        $s->execute([$id]);
        $rows = $s->fetchAll();
        foreach ($rows as &$r) { $r['CanMua'] = max(0, (float)$r['SoLuongCan'] - (float)$r['TonKho']); }
        return $rows;
    }

    /** Danh sách nguyên liệu đang hoạt động để chọn trong form. */
    public function materials(): array
    {
        return $this->db->query("SELECT h.MaHang, h.TenHang, h.DonViTinh,
                COALESCE((SELECT SUM(tk.SoLuongTon) FROM ton_kho tk WHERE tk.MaHang=h.MaHang),0) AS TonKho
            FROM hang_hoa h WHERE h.LoaiHang='NGUYEN_LIEU' AND h.TrangThai='ACTIVE' ORDER BY h.TenHang")->fetchAll();
    }

    public function validMaterialCodes(): array
    {
        return array_column($this->materials(), 'MaHang');
    }

    /** Tạo mới (id = null) hoặc cập nhật. Lưu xong chuyển sang "Chờ phê duyệt". */
    public function save(array $d, array $items, ?string $id, string $userId): string
    {
        $this->db->beginTransaction();
        try {
            if ($id === null) {
                $id = 'KH' . date('ymd') . strtoupper(bin2hex(random_bytes(3)));
                $s = $this->db->prepare("INSERT INTO ke_hoach_nguyen_lieu(MaKeHoach,MaNguoiLap,ThoiGianSuDung,TrangThai,GhiChu) VALUES(?,?,?,'CHO_PHE_DUYET',?)");
                $s->execute([$id, $userId, $d['date'], $d['note'] ?: null]);
            } else {
                $s = $this->db->prepare("UPDATE ke_hoach_nguyen_lieu SET ThoiGianSuDung=?, GhiChu=?, TrangThai='CHO_PHE_DUYET' WHERE MaKeHoach=?");
                $s->execute([$d['date'], $d['note'] ?: null, $id]);
                $this->db->prepare('DELETE FROM chi_tiet_ke_hoach WHERE MaKeHoach=?')->execute([$id]);
            }
            $ins = $this->db->prepare('INSERT INTO chi_tiet_ke_hoach(MaKeHoach,MaHang,SoLuongCan,GhiChu) VALUES(?,?,?,?)');
            foreach ($items as $it) { $ins->execute([$id, $it['code'], $it['qty'], $it['note'] ?: null]); }
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}

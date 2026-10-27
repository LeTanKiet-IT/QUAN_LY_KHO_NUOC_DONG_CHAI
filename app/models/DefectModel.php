<?php
class DefectModel
{
    private PDO $db;
    public function __construct() { $this->db = Database::conn(); }

    public function search(string $q = '', string $type = '', string $status = ''): array
    {
        $sql = "SELECT p.*, t.HoTen AS NguoiLap FROM phieu_hang_loi p JOIN tai_khoan t ON t.MaTaiKhoan=p.MaNguoiLap WHERE (p.MaPhieu LIKE ? OR p.MaHang LIKE ? OR p.TenHang LIKE ? OR COALESCE(p.NhaCungCap,'') LIKE ?)";
        $params = array_fill(0, 4, '%' . $q . '%');
        if ($type !== '') { $sql .= ' AND p.LoaiHang=?'; $params[] = $type; }
        if ($status !== '') { $sql .= ' AND p.TrangThai=?'; $params[] = $status; }
        $sql .= ' ORDER BY p.NgayLap DESC, p.NgayTao DESC';
        $stmt = $this->db->prepare($sql); $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(string $id): ?array
    { $stmt = $this->db->prepare('SELECT * FROM phieu_hang_loi WHERE MaPhieu=?'); $stmt->execute([$id]); $row = $stmt->fetch(); return $row ?: null; }

    public function save(array $d, ?string $id, string $creatorId): string
    {
        if (!$id) {
            $id = 'HL' . date('ymd') . strtoupper(bin2hex(random_bytes(2)));
            $stmt = $this->db->prepare('INSERT INTO phieu_hang_loi(MaPhieu,NgayLap,LoaiHang,MaHang,TenHang,SoLuong,DonViTinh,LyDo,HuongXuLy,NhaCungCap,TrangThai,GhiChu,MaNguoiLap) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$id,$d['date'],$d['type'],$d['code'],$d['product'],$d['qty'],$d['unit'],$d['reason'],$d['action'],$d['supplier'] ?: null,$d['status'],$d['note'] ?: null,$creatorId]);
            return $id;
        }
        $stmt = $this->db->prepare('UPDATE phieu_hang_loi SET NgayLap=?,LoaiHang=?,MaHang=?,TenHang=?,SoLuong=?,DonViTinh=?,LyDo=?,HuongXuLy=?,NhaCungCap=?,TrangThai=?,GhiChu=? WHERE MaPhieu=?');
        $stmt->execute([$d['date'],$d['type'],$d['code'],$d['product'],$d['qty'],$d['unit'],$d['reason'],$d['action'],$d['supplier'] ?: null,$d['status'],$d['note'] ?: null,$id]);
        return $id;
    }

    public function delete(string $id): int
    { $stmt = $this->db->prepare('DELETE FROM phieu_hang_loi WHERE MaPhieu=?'); $stmt->execute([$id]); return $stmt->rowCount(); }

    public function updateStatus(string $id, string $status): int
    { $stmt = $this->db->prepare('UPDATE phieu_hang_loi SET TrangThai=? WHERE MaPhieu=?'); $stmt->execute([$status,$id]); return $stmt->rowCount(); }

    public function stats(): array
    { return $this->db->query("SELECT COUNT(*) total, COALESCE(SUM(TrangThai='CHO_DUYET'),0) pending, COALESCE(SUM(TrangThai='DANG_XU_LY'),0) processing, COALESCE(SUM(TrangThai='DA_XU_LY'),0) done FROM phieu_hang_loi")->fetch(); }
}

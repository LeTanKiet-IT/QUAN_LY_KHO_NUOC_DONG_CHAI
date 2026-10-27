<?php
class UserModel
{
    private PDO $db;
    public function __construct() { $this->db = Database::conn(); }

    public function findForLogin(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT t.*,v.TenVaiTro FROM tai_khoan t JOIN vai_tro v ON v.MaVaiTro=t.MaVaiTro WHERE t.TenDangNhap=?');
        $stmt->execute([$username]); $row = $stmt->fetch(); return $row ?: null;
    }

    public function permissions(string $roleId): array
    { $stmt = $this->db->prepare('SELECT MaQuyen FROM vai_tro_quyen WHERE MaVaiTro=?'); $stmt->execute([$roleId]); return array_column($stmt->fetchAll(), 'MaQuyen'); }

    public function recordLogin(string $id): void
    { $stmt = $this->db->prepare('UPDATE tai_khoan SET LanDangNhapCuoi=NOW() WHERE MaTaiKhoan=?'); $stmt->execute([$id]); }
}

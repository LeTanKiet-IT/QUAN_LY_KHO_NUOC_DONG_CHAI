<?php
class AccountModel
{
    private PDO $db;
    public function __construct() { $this->db = Database::conn(); }

    public function search(string $q = '', string $role = '', string $status = ''): array
    {
        $sql = 'SELECT t.*, v.TenVaiTro FROM tai_khoan t JOIN vai_tro v ON v.MaVaiTro=t.MaVaiTro WHERE (t.TenDangNhap LIKE ? OR t.HoTen LIKE ?)';
        $params = ['%' . $q . '%', '%' . $q . '%'];
        if ($role !== '') { $sql .= ' AND t.MaVaiTro=?'; $params[] = $role; }
        if ($status !== '') { $sql .= ' AND t.TrangThai=?'; $params[] = $status; }
        $sql .= ' ORDER BY t.NgayTao DESC';
        $stmt = $this->db->prepare($sql); $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function activeRoles(): array
    { return $this->db->query("SELECT * FROM vai_tro WHERE TrangThai='ACTIVE' ORDER BY TenVaiTro")->fetchAll(); }

    public function roleExists(string $role): bool
    { $stmt = $this->db->prepare("SELECT COUNT(*) FROM vai_tro WHERE MaVaiTro=? AND TrangThai='ACTIVE'"); $stmt->execute([$role]); return (int)$stmt->fetchColumn() > 0; }

    public function save(array $data, ?string $id = null): string
    {
        if ($id === null || $id === '') {
            $id = 'TK' . strtoupper(bin2hex(random_bytes(3)));
            $stmt = $this->db->prepare('INSERT INTO tai_khoan(MaTaiKhoan,TenDangNhap,MatKhauHash,HoTen,Email,SoDienThoai,MaVaiTro,TrangThai) VALUES(?,?,?,?,?,?,?,?)');
            $stmt->execute([$id, $data['username'], password_hash($data['password'], PASSWORD_DEFAULT), $data['name'], $data['email'] ?: null, $data['phone'] ?: null, $data['role'], $data['status']]);
            return $id;
        }
        if ($data['password'] !== '') {
            $stmt = $this->db->prepare('UPDATE tai_khoan SET TenDangNhap=?,MatKhauHash=?,HoTen=?,Email=?,SoDienThoai=?,MaVaiTro=?,TrangThai=? WHERE MaTaiKhoan=?');
            $stmt->execute([$data['username'], password_hash($data['password'], PASSWORD_DEFAULT), $data['name'], $data['email'] ?: null, $data['phone'] ?: null, $data['role'], $data['status'], $id]);
        } else {
            $stmt = $this->db->prepare('UPDATE tai_khoan SET TenDangNhap=?,HoTen=?,Email=?,SoDienThoai=?,MaVaiTro=?,TrangThai=? WHERE MaTaiKhoan=?');
            $stmt->execute([$data['username'], $data['name'], $data['email'] ?: null, $data['phone'] ?: null, $data['role'], $data['status'], $id]);
        }
        return $id;
    }

    public function lock(string $id): int
    { $stmt = $this->db->prepare("UPDATE tai_khoan SET TrangThai='LOCKED' WHERE MaTaiKhoan=?"); $stmt->execute([$id]); return $stmt->rowCount(); }
}

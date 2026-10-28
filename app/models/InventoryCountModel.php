<?php
/** UC22 - Lap bien ban kiem ke. Khong thay doi so luong ton kho. */
class InventoryCountModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conn();
    }

    public function plans(): array
    {
        return $this->db->query("SELECT MaKeHoachKiemKe,NgayKiemKe,PhamViKiemKe,TrangThai
            FROM ke_hoach_kiem_ke
            WHERE TrangThai IN ('DA_DUYET','DANG_KIEM_KE')
            ORDER BY NgayKiemKe DESC,MaKeHoachKiemKe")->fetchAll();
    }

    public function warehousesForPlan(string $planId): array
    {
        $s = $this->db->prepare("SELECT k.MaKho,k.TenKho,k.TrangThai
            FROM ke_hoach_kiem_ke_kho p
            JOIN kho k ON k.MaKho=p.MaKho
            JOIN ke_hoach_kiem_ke kh ON kh.MaKeHoachKiemKe=p.MaKeHoachKiemKe
            WHERE p.MaKeHoachKiemKe=? AND kh.TrangThai IN ('DA_DUYET','DANG_KIEM_KE')
            ORDER BY k.TenKho");
        $s->execute([$planId]);
        return $s->fetchAll();
    }

    public function listRecords(): array
    {
        return $this->db->query("SELECT b.MaBienBan,b.MaKeHoachKiemKe,b.MaKho,k.TenKho,
                b.TrangThai,b.NgayKiemKe,tk.HoTen AS NguoiLapTen
            FROM bien_ban_kiem_ke b
            JOIN kho k ON k.MaKho=b.MaKho
            JOIN tai_khoan tk ON tk.MaTaiKhoan=b.NguoiLap
            ORDER BY b.NgayCapNhat DESC, b.MaBienBan DESC LIMIT 100")->fetchAll();
    }

    public function recordByScope(string $planId,string $warehouseId): ?array
    {
        $s=$this->db->prepare('SELECT * FROM bien_ban_kiem_ke WHERE MaKeHoachKiemKe=? AND MaKho=?');
        $s->execute([$planId,$warehouseId]);
        return $s->fetch() ?: null;
    }

    public function record(string $id): ?array
    {
        $s=$this->db->prepare("SELECT b.*, k.TenKho, kh.PhamViKiemKe, kh.NgayKiemKe AS NgayKeHoach,
                tk.HoTen AS NguoiLapTen
            FROM bien_ban_kiem_ke b JOIN kho k ON k.MaKho=b.MaKho
            JOIN ke_hoach_kiem_ke kh ON kh.MaKeHoachKiemKe=b.MaKeHoachKiemKe
            JOIN tai_khoan tk ON tk.MaTaiKhoan=b.NguoiLap
            WHERE b.MaBienBan=?");
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    public function detailRows(string $id): array
    {
        $s=$this->db->prepare("SELECT d.*,h.TenHang,h.DonViTinh FROM chi_tiet_kiem_ke d
            JOIN hang_hoa h ON h.MaHang=d.MaHang
            WHERE d.MaBienBan=? ORDER BY h.TenHang,d.MaHang");
        $s->execute([$id]);
        return $s->fetchAll();
    }

    public function stockRows(string $warehouseId): array
    {
        $s=$this->db->prepare("SELECT t.MaHang,h.TenHang,h.DonViTinh,t.SoLuongTon
            FROM ton_kho t JOIN hang_hoa h ON h.MaHang=t.MaHang
            WHERE t.MaKho=? ORDER BY h.TenHang,t.MaHang");
        $s->execute([$warehouseId]);
        return $s->fetchAll();
    }

    public function validScope(string $planId,string $warehouseId): bool
    {
        $s=$this->db->prepare("SELECT 1 FROM ke_hoach_kiem_ke_kho p
            JOIN ke_hoach_kiem_ke kh ON kh.MaKeHoachKiemKe=p.MaKeHoachKiemKe
            JOIN kho k ON k.MaKho=p.MaKho
            WHERE p.MaKeHoachKiemKe=? AND p.MaKho=?
              AND kh.TrangThai IN ('DA_DUYET','DANG_KIEM_KE') AND k.TrangThai='ACTIVE'");
        $s->execute([$planId,$warehouseId]);
        return (bool)$s->fetchColumn();
    }

    /**
     * $counts[$maHang] = raw string, blank only for draft.
     * $conditions[$maHang], $notes[$maHang] = raw strings.
     * Returns [id, status]. Everything is validated again server-side.
     */
    public function save(string $planId,string $warehouseId,string $userId,
                         bool $isAdmin,array $counts,array $conditions,array $notes,
                         array $snapshots,string $action): array
    {
        if (!in_array($action,['draft','confirm'],true) || !$planId || !$warehouseId || !$userId) {
            throw new InvalidArgumentException('Thông tin biên bản không hợp lệ.');
        }
        $this->db->beginTransaction();
        try {
            // Khoa ke hoach: ngan phe duyet/huy song song voi khi luu.
            $s=$this->db->prepare("SELECT TrangThai FROM ke_hoach_kiem_ke WHERE MaKeHoachKiemKe=? FOR UPDATE");
            $s->execute([$planId]);
            $plan=$s->fetch();
            if (!$plan || !in_array($plan['TrangThai'],['DA_DUYET','DANG_KIEM_KE'],true)) {
                throw new RuntimeException('Kế hoạch chưa được duyệt hoặc đã kết thúc.');
            }
            $s=$this->db->prepare("SELECT k.TrangThai FROM ke_hoach_kiem_ke_kho p
                JOIN kho k ON k.MaKho=p.MaKho WHERE p.MaKeHoachKiemKe=? AND p.MaKho=?");
            $s->execute([$planId,$warehouseId]);
            $wh=$s->fetch();
            if (!$wh || $wh['TrangThai'] !== 'ACTIVE') {
                throw new RuntimeException('Kho không thuộc kế hoạch hoặc đã bị khóa.');
            }
            $s=$this->db->prepare("SELECT * FROM bien_ban_kiem_ke
                WHERE MaKeHoachKiemKe=? AND MaKho=? FOR UPDATE");
            $s->execute([$planId,$warehouseId]);
            $old=$s->fetch();
            if ($old && ($old['TrangThai'] !== 'NHAP' || (!$isAdmin && $old['NguoiLap']!==$userId))) {
                throw new RuntimeException('Biên bản đã được xác nhận hoặc thuộc người lập khác.');
            }
            // Snapshot so he thong tai thoi diem xac nhan (hoac luu nhap).
            $s=$this->db->prepare("SELECT t.MaHang,t.SoLuongTon
                FROM ton_kho t JOIN hang_hoa h ON h.MaHang=t.MaHang
                WHERE t.MaKho=? ORDER BY t.MaHang FOR UPDATE");
            $s->execute([$warehouseId]);
            $stock=$s->fetchAll();
            if (!$stock) {
                throw new RuntimeException('Kho chưa có dữ liệu tồn kho để kiểm kê.');
            }
            $expected=array_column($stock,'MaHang');
            sort($expected);
            $received=array_map('strval',array_keys($counts));
            sort($received);
            if ($expected !== $received) {
                throw new RuntimeException('Danh sách hàng đã thay đổi. Tải lại trang và nhập lại số lượng.');
            }
            $items=[];
            foreach ($stock as $row) {
                $code=(string)$row['MaHang'];
                $snapshot=(string)($snapshots[$code]??'');
                if (!is_numeric($snapshot) || round((float)$snapshot,2)!==round((float)$row['SoLuongTon'],2)) {
                    throw new RuntimeException('Tồn kho trên hệ thống đã thay đổi. Tải lại màn hình kiểm kê.');
                }
                $raw=$counts[$code];
                if (!is_string($raw) && !is_int($raw) && !is_float($raw)) {
                    throw new InvalidArgumentException('Số lượng kiểm kê không hợp lệ.');
                }
                $qty=trim((string)$raw);
                if ($qty === '') {
                    if ($action === 'confirm') {
                        throw new InvalidArgumentException('Phải nhập đầy đủ số lượng thực tế (có thể nhập 0).');
                    }
                    $actual=null;
                } elseif (!preg_match('/^\d{1,12}(?:\.\d{1,2})?$/D',$qty)) {
                    throw new InvalidArgumentException('Số lượng thực tế phải >= 0 và tối đa 2 chữ số thập phân.');
                } else {
                    $actual=$qty;
                }
                $condition=trim((string)($conditions[$code]??''));
                $note=trim((string)($notes[$code]??''));
                if (mb_strlen($condition)>100 || mb_strlen($note)>500) {
                    throw new InvalidArgumentException('Tình trạng hoặc ghi chú vượt quá độ dài cho phép.');
                }
                $diff=$actual!==null && round((float)$actual,2)!==round((float)$row['SoLuongTon'],2);
                $items[]=[$code,$row['SoLuongTon'],$actual,$condition?:null,$note?:null,
                    $diff ? 'CHO_DUYET' : 'KHONG_CHENH_LECH'];
            }
            if ($old) {
                $id=$old['MaBienBan'];
                $s=$this->db->prepare('UPDATE bien_ban_kiem_ke SET TrangThai=?,NgayKiemKe=NOW() WHERE MaBienBan=?');
                $s->execute([$action==='confirm'?'CHO_XU_LY':'NHAP',$id]);
                $s=$this->db->prepare('DELETE FROM chi_tiet_kiem_ke WHERE MaBienBan=?');
                $s->execute([$id]);
            } else {
                $id='BB'.date('ymdHis').strtoupper(bin2hex(random_bytes(4)));
                $s=$this->db->prepare('INSERT INTO bien_ban_kiem_ke
                    (MaBienBan,MaKeHoachKiemKe,MaKho,TrangThai,NguoiLap)
                    VALUES (?,?,?,?,?)');
                $s->execute([$id,$planId,$warehouseId,$action==='confirm'?'CHO_XU_LY':'NHAP',$userId]);
            }
            $s=$this->db->prepare('INSERT INTO chi_tiet_kiem_ke
                (MaBienBan,MaHang,SoLuongHeThong,SoLuongThucTe,TinhTrangThucTe,GhiChu,TrangThaiXuLy)
                VALUES (?,?,?,?,?,?,?)');
            foreach ($items as $item) {
                $s->execute(array_merge([$id],$item));
            }
            // Ke hoach chi ket thuc khi tat ca kho pham vi da lap bien ban.
            if ($action==='confirm') {
                $s=$this->db->prepare("SELECT COUNT(*) FROM ke_hoach_kiem_ke_kho p
                    LEFT JOIN bien_ban_kiem_ke b
                      ON b.MaKeHoachKiemKe=p.MaKeHoachKiemKe AND b.MaKho=p.MaKho
                     AND b.TrangThai IN ('CHO_XU_LY','DA_XU_LY')
                    WHERE p.MaKeHoachKiemKe=? AND b.MaBienBan IS NULL");
                $s->execute([$planId]);
                $newPlanStatus=(int)$s->fetchColumn()===0?'HOAN_TAT':'DANG_KIEM_KE';
            } else {
                $newPlanStatus='DANG_KIEM_KE';
            }
            $s=$this->db->prepare('UPDATE ke_hoach_kiem_ke SET TrangThai=? WHERE MaKeHoachKiemKe=?');
            $s->execute([$newPlanStatus,$planId]);
            $this->db->commit();
            return [$id,$action==='confirm'?'CHO_XU_LY':'NHAP'];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function cancelDraft(string $id,string $userId,bool $isAdmin): void
    {
        $this->db->beginTransaction();
        try {
            $s=$this->db->prepare('SELECT NguoiLap,TrangThai FROM bien_ban_kiem_ke WHERE MaBienBan=? FOR UPDATE');
            $s->execute([$id]);
            $row=$s->fetch();
            if (!$row || $row['TrangThai']!=='NHAP' || (!$isAdmin && $row['NguoiLap']!==$userId)) {
                throw new RuntimeException('Không thể hủy biên bản này.');
            }
            $s=$this->db->prepare('DELETE FROM chi_tiet_kiem_ke WHERE MaBienBan=?');
            $s->execute([$id]);
            $s=$this->db->prepare('DELETE FROM bien_ban_kiem_ke WHERE MaBienBan=?');
            $s->execute([$id]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
}

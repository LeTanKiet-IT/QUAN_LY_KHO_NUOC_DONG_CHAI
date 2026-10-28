
-- =====================================================
-- FR17 - LAP PHIEU XUAT KHO
-- Nguyen Thi My Oanh - Nhom Dom Dom
-- Migration bo sung, khong xoa du lieu cu
-- CHI CHAY MOT LAN sau khi sao luu database
-- =====================================================

-- 1. BO SUNG THONG TIN LO HANG
-- HanSuDung: han su dung thuc te cua lo
-- SoLuongDaXuat: tong so luong da xuat theo lo

ALTER TABLE lo_hang
    ADD COLUMN HanSuDung DATE NULL
        COMMENT 'Ngay het han xac minh tu lo hang',
    ADD COLUMN SoLuongDaXuat DECIMAL(12,2)
        NOT NULL DEFAULT 0.00
        COMMENT 'Tong so luong da xuat cua lo',
    ADD INDEX idx_lo_hsd (MaHang, HanSuDung);


-- =====================================================
-- 2. BANG YEU CAU XUAT KHO
-- =====================================================

CREATE TABLE IF NOT EXISTS yeu_cau_xuat_kho (
    MaYeuCau VARCHAR(30) NOT NULL PRIMARY KEY,
    MaKho VARCHAR(20) NOT NULL,
    MaHang VARCHAR(30) NOT NULL,

    MaNhanVienDuocGiao VARCHAR(20) NOT NULL
        COMMENT 'Nhan vien kho duoc phan cong xu ly',

    SoLuongYeuCau DECIMAL(12,2) NOT NULL,
    SoLuongDaXuat DECIMAL(12,2) NOT NULL DEFAULT 0,

    NguoiNhan VARCHAR(120) NOT NULL,
    MaChungTu VARCHAR(50) NULL,

    TrangThai ENUM(
        'CHO_XUAT',
        'DA_XUAT_MOT_PHAN',
        'HOAN_THANH',
        'HUY'
    ) NOT NULL DEFAULT 'CHO_XUAT',

    NgayTao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY idx_ycx_status (TrangThai),

    KEY idx_ycx_assigned (
        MaNhanVienDuocGiao,
        TrangThai
    ),

    CONSTRAINT fk_ycx_assigned
        FOREIGN KEY (MaNhanVienDuocGiao)
        REFERENCES tai_khoan(MaTaiKhoan),

    CONSTRAINT fk_ycx_kho
        FOREIGN KEY (MaKho)
        REFERENCES kho(MaKho),

    CONSTRAINT fk_ycx_hang
        FOREIGN KEY (MaHang)
        REFERENCES hang_hoa(MaHang),

    CONSTRAINT chk_ycx_qty
        CHECK (
            SoLuongYeuCau > 0
            AND SoLuongDaXuat >= 0
            AND SoLuongDaXuat <= SoLuongYeuCau
        )

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- 3. BANG PHIEU XUAT KHO
-- =====================================================

CREATE TABLE IF NOT EXISTS phieu_xuat_kho (
    MaPhieuXuat VARCHAR(30) NOT NULL PRIMARY KEY,
    MaYeuCau VARCHAR(30) NOT NULL,

    RequestNonce CHAR(64) NOT NULL
        COMMENT 'Ngan gui trung bieu mau',

    NgayXuat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    MaKho VARCHAR(20) NOT NULL,
    MaHang VARCHAR(30) NOT NULL,
    MaNguoiLap VARCHAR(20) NOT NULL,

    NguoiNhan VARCHAR(120) NOT NULL,
    SoLuongXuat DECIMAL(12,2) NOT NULL,

    TrangThai ENUM('DA_XUAT')
        NOT NULL DEFAULT 'DA_XUAT',

    UNIQUE KEY uq_px_nonce (RequestNonce),
    KEY idx_px_ycx (MaYeuCau),

    CONSTRAINT fk_px_ycx
        FOREIGN KEY (MaYeuCau)
        REFERENCES yeu_cau_xuat_kho(MaYeuCau),

    CONSTRAINT fk_px_kho
        FOREIGN KEY (MaKho)
        REFERENCES kho(MaKho),

    CONSTRAINT fk_px_hang
        FOREIGN KEY (MaHang)
        REFERENCES hang_hoa(MaHang),

    CONSTRAINT fk_px_user
        FOREIGN KEY (MaNguoiLap)
        REFERENCES tai_khoan(MaTaiKhoan),

    CONSTRAINT chk_px_qty
        CHECK (SoLuongXuat > 0)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- 4. BANG CHI TIET PHIEU XUAT
-- =====================================================

CREATE TABLE IF NOT EXISTS chi_tiet_phieu_xuat (
    MaChiTiet BIGINT UNSIGNED
        NOT NULL AUTO_INCREMENT PRIMARY KEY,

    MaPhieuXuat VARCHAR(30) NOT NULL,
    MaLo VARCHAR(30) NOT NULL,
    SoLuongXuat DECIMAL(12,2) NOT NULL,

    UNIQUE KEY uq_px_lo (MaPhieuXuat, MaLo),

    CONSTRAINT fk_ctx_px
        FOREIGN KEY (MaPhieuXuat)
        REFERENCES phieu_xuat_kho(MaPhieuXuat),

    CONSTRAINT fk_ctx_lo
        FOREIGN KEY (MaLo)
        REFERENCES lo_hang(MaLo),

    CONSTRAINT chk_ctx_qty
        CHECK (SoLuongXuat > 0)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- 5. PHAN QUYEN LAP PHIEU XUAT KHO
-- =====================================================

INSERT IGNORE INTO quyen (
    MaQuyen,
    TenQuyen,
    MoTa
) VALUES (
    'XUATKHO_QUANLY',
    'Lap phieu xuat kho',
    'Lap va xem phieu xuat kho theo yeu cau FEFO'
);

INSERT IGNORE INTO vai_tro_quyen (
    MaVaiTro,
    MaQuyen
) VALUES
    ('ADMIN', 'XUATKHO_QUANLY'),
    ('NVKHO', 'XUATKHO_QUANLY');


-- =====================================================
-- 6. GHI CHU
-- =====================================================
-- Yeu cau xuat kho phai duoc phan cong nhan vien.
-- Khong tu tao yeu cau xuat kho gia.
-- Khong tu gan HanSuDung khi chua duoc xac minh.
-- Khong thay doi SoLuongTon hien tai.
-- Khong chay lai migration neu da import thanh cong.
-- =====================================================

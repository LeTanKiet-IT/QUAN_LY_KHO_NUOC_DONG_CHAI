-- Kế hoạch nguyên liệu (FR11) - chạy trên database qlkho_domdom (MySQL/MariaDB)
-- Chạy 1 lần sau khi đã import qlkho_domdom.sql

CREATE TABLE IF NOT EXISTS `ke_hoach_nguyen_lieu` (
  `MaKeHoach` varchar(30) NOT NULL,
  `NgayLap` datetime NOT NULL DEFAULT current_timestamp(),
  `MaNguoiLap` varchar(20) NOT NULL,
  `ThoiGianSuDung` date NOT NULL,
  `TrangThai` enum('NHAP','CHO_PHE_DUYET','CHO_CHINH_SUA','DA_PHE_DUYET','TU_CHOI') NOT NULL DEFAULT 'NHAP',
  `LyDo` varchar(500) DEFAULT NULL COMMENT 'Lý do từ chối / nội dung yêu cầu chỉnh sửa của Ban giám đốc',
  `GhiChu` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`MaKeHoach`),
  KEY `MaNguoiLap` (`MaNguoiLap`),
  KEY `TrangThai` (`TrangThai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chi_tiet_ke_hoach` (
  `MaChiTiet` int(11) NOT NULL AUTO_INCREMENT,
  `MaKeHoach` varchar(30) NOT NULL,
  `MaHang` varchar(30) NOT NULL,
  `SoLuongCan` decimal(14,2) NOT NULL,
  `GhiChu` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`MaChiTiet`),
  UNIQUE KEY `uq_kehoach_hang` (`MaKeHoach`,`MaHang`),
  KEY `MaHang` (`MaHang`),
  CONSTRAINT `fk_ctkh_kehoach` FOREIGN KEY (`MaKeHoach`) REFERENCES `ke_hoach_nguyen_lieu` (`MaKeHoach`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quyền mới, gán cho Admin và Nhân viên kho
INSERT IGNORE INTO `quyen` (`MaQuyen`,`TenQuyen`,`MoTa`) VALUES
('KEHOACH_XEM','Xem kế hoạch nguyên liệu','Xem danh sách và chi tiết kế hoạch nguyên liệu'),
('KEHOACH_QUANLY','Quản lý kế hoạch nguyên liệu','Tạo và chỉnh sửa kế hoạch nguyên liệu');

INSERT IGNORE INTO `vai_tro_quyen` (`MaVaiTro`,`MaQuyen`) VALUES
('ADMIN','KEHOACH_XEM'),('ADMIN','KEHOACH_QUANLY'),
('NVKHO','KEHOACH_XEM'),('NVKHO','KEHOACH_QUANLY');

-- Dữ liệu mẫu (không bắt buộc)
INSERT IGNORE INTO `ke_hoach_nguyen_lieu` (`MaKeHoach`,`NgayLap`,`MaNguoiLap`,`ThoiGianSuDung`,`TrangThai`,`LyDo`,`GhiChu`) VALUES
('KH261028A001', NOW(), 'TK_NVKHO01', DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'CHO_CHINH_SUA', 'Giảm số lượng hạt nhựa PET xuống còn 800 kg.', 'Kế hoạch sản xuất tuần tới'),
('KH261028A002', NOW(), 'TK_NVKHO01', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'CHO_PHE_DUYET', NULL, NULL);

INSERT IGNORE INTO `chi_tiet_ke_hoach` (`MaKeHoach`,`MaHang`,`SoLuongCan`,`GhiChu`) VALUES
('KH261028A001','NL001',1000.00,NULL),
('KH261028A002','NL001',600.00,'Đợt 2');

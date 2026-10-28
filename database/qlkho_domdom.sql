-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 28, 2026 lúc 05:55 PM
-- Phiên bản máy phục vụ: 10.11.16-MariaDB
-- Phiên bản PHP: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `qlkho_domdom`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bien_ban_kiem_ke`
--

CREATE TABLE `bien_ban_kiem_ke` (
  `MaBienBan` varchar(24) NOT NULL,
  `MaKeHoachKiemKe` varchar(20) NOT NULL,
  `MaKho` varchar(20) NOT NULL,
  `NgayKiemKe` datetime NOT NULL DEFAULT current_timestamp(),
  `TrangThai` enum('NHAP','CHO_XU_LY','DA_XU_LY','HUY') NOT NULL DEFAULT 'NHAP',
  `NguoiLap` varchar(20) NOT NULL,
  `NgayCapNhat` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `bien_ban_kiem_ke`
--

INSERT INTO `bien_ban_kiem_ke` (`MaBienBan`, `MaKeHoachKiemKe`, `MaKho`, `NgayKiemKe`, `TrangThai`, `NguoiLap`, `NgayCapNhat`) VALUES
('BB26102823363019380226', 'KKDEMO001', 'KHO01', '2026-10-28 23:36:30', 'NHAP', 'TK001', '2026-10-28 23:36:30');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_tiet_chung_tu_nguon`
--

CREATE TABLE `chi_tiet_chung_tu_nguon` (
  `MaChiTiet` bigint(20) UNSIGNED NOT NULL,
  `MaChungTu` varchar(30) NOT NULL,
  `MaHang` varchar(30) NOT NULL,
  `SoLuong` decimal(12,2) NOT NULL,
  `SoLuongDaNhap` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chi_tiet_chung_tu_nguon`
--

INSERT INTO `chi_tiet_chung_tu_nguon` (`MaChiTiet`, `MaChungTu`, `MaHang`, `SoLuong`, `SoLuongDaNhap`) VALUES
(1, 'CTNL001', 'NL001', 500.00, 500.00),
(2, 'CTTP001', 'TP001', 200.00, 30.00),
(3, 'CTTP003', 'TP003', 500.00, 500.00),
(4, 'CTNL004', 'NL001', 300.00, 20.00),
(5, 'CTNL008', 'NL001', 300.00, 20.00),
(6, 'CTNL009', 'NL001', 200.00, 0.00),
(7, 'CTNL010', 'NL001', 150.00, 0.00),
(8, 'CTNL011', 'NL001', 100.00, 0.00),
(9, 'CTTP006', 'TP001', 250.00, 0.00),
(10, 'CTTP007', 'TP003', 100.00, 0.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_tiet_ke_hoach`
--

CREATE TABLE `chi_tiet_ke_hoach` (
  `MaChiTiet` int(11) NOT NULL,
  `MaKeHoach` varchar(30) NOT NULL,
  `MaHang` varchar(30) NOT NULL,
  `SoLuongCan` decimal(14,2) NOT NULL,
  `GhiChu` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chi_tiet_ke_hoach`
--

INSERT INTO `chi_tiet_ke_hoach` (`MaChiTiet`, `MaKeHoach`, `MaHang`, `SoLuongCan`, `GhiChu`) VALUES
(2, 'KH261028A002', 'NL001', 600.00, 'Đợt 2'),
(3, 'KH261028A001', 'NL001', 1000.00, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_tiet_kiem_ke`
--

CREATE TABLE `chi_tiet_kiem_ke` (
  `MaChiTietKiemKe` bigint(20) UNSIGNED NOT NULL,
  `MaBienBan` varchar(24) NOT NULL,
  `MaHang` varchar(30) NOT NULL,
  `SoLuongHeThong` decimal(14,2) NOT NULL,
  `SoLuongThucTe` decimal(14,2) DEFAULT NULL,
  `SoLuongChenhLech` decimal(15,2) GENERATED ALWAYS AS (case when `SoLuongThucTe` is null then NULL else `SoLuongThucTe` - `SoLuongHeThong` end) STORED,
  `TinhTrangThucTe` varchar(100) DEFAULT NULL,
  `GhiChu` varchar(500) DEFAULT NULL,
  `NguyenNhan` varchar(500) DEFAULT NULL,
  `BienPhapXuLy` varchar(500) DEFAULT NULL,
  `TrangThaiXuLy` enum('KHONG_CHENH_LECH','CHO_DUYET','DA_DUYET','TU_CHOI','DA_CAP_NHAT') NOT NULL DEFAULT 'KHONG_CHENH_LECH'
) ;

--
-- Đang đổ dữ liệu cho bảng `chi_tiet_kiem_ke`
--

INSERT INTO `chi_tiet_kiem_ke` (`MaChiTietKiemKe`, `MaBienBan`, `MaHang`, `SoLuongHeThong`, `SoLuongThucTe`, `TinhTrangThucTe`, `GhiChu`, `NguyenNhan`, `BienPhapXuLy`, `TrangThaiXuLy`) VALUES
(1, 'BB26102823363019380226', 'NL001', 520.00, NULL, NULL, NULL, NULL, NULL, 'KHONG_CHENH_LECH'),
(2, 'BB26102823363019380226', 'TP001', 20.00, NULL, NULL, NULL, NULL, NULL, 'KHONG_CHENH_LECH'),
(3, 'BB26102823363019380226', 'TP003', 500.00, NULL, NULL, NULL, NULL, NULL, 'KHONG_CHENH_LECH');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_tiet_phieu_nhap`
--

CREATE TABLE `chi_tiet_phieu_nhap` (
  `MaChiTietNhap` bigint(20) UNSIGNED NOT NULL,
  `MaPhieuNhap` varchar(30) NOT NULL,
  `MaHang` varchar(30) NOT NULL,
  `MaChiTietNguon` bigint(20) UNSIGNED NOT NULL,
  `SoLuong` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chi_tiet_phieu_nhap`
--

INSERT INTO `chi_tiet_phieu_nhap` (`MaChiTietNhap`, `MaPhieuNhap`, `MaHang`, `MaChiTietNguon`, `SoLuong`) VALUES
(1, 'PN20261027233933AF4D75', 'NL001', 1, 500.00),
(2, 'PN2026102723400410D36F', 'TP003', 3, 500.00),
(3, 'PN20261027235514F18E88', 'TP001', 2, 10.00),
(4, 'PN202610280004317DF479', 'TP001', 2, 10.00),
(5, 'PN202610280007258B5A3B', 'TP001', 2, 10.00),
(6, 'PN202610280020590BAA64', 'NL001', 5, 20.00),
(7, 'PN20261028004852D432CB', 'NL001', 4, 20.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chung_tu_nguon`
--

CREATE TABLE `chung_tu_nguon` (
  `MaChungTu` varchar(30) NOT NULL,
  `LoaiNhap` enum('NGUYEN_LIEU','THANH_PHAM') NOT NULL,
  `TrangThai` enum('CHO_DUYET','DA_DUYET','HOAN_THANH','HUY') NOT NULL DEFAULT 'CHO_DUYET',
  `KetQuaKiemDinh` enum('CHO_KIEM_DINH','DAT','KHONG_DAT') DEFAULT NULL,
  `NgayChungTu` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chung_tu_nguon`
--

INSERT INTO `chung_tu_nguon` (`MaChungTu`, `LoaiNhap`, `TrangThai`, `KetQuaKiemDinh`, `NgayChungTu`) VALUES
('CTNL001', 'NGUYEN_LIEU', 'DA_DUYET', 'DAT', '2026-10-20'),
('CTNL002', 'NGUYEN_LIEU', 'DA_DUYET', 'CHO_KIEM_DINH', '2026-10-27'),
('CTNL003', 'NGUYEN_LIEU', 'DA_DUYET', 'KHONG_DAT', '2026-10-27'),
('CTNL004', 'NGUYEN_LIEU', 'DA_DUYET', 'DAT', '2026-10-28'),
('CTNL008', 'NGUYEN_LIEU', 'DA_DUYET', 'DAT', '2026-10-28'),
('CTNL009', 'NGUYEN_LIEU', 'DA_DUYET', 'CHO_KIEM_DINH', '2026-10-28'),
('CTNL010', 'NGUYEN_LIEU', 'DA_DUYET', 'KHONG_DAT', '2026-10-28'),
('CTNL011', 'NGUYEN_LIEU', 'CHO_DUYET', 'DAT', '2026-10-28'),
('CTTP001', 'THANH_PHAM', 'HOAN_THANH', 'DAT', '2026-10-21'),
('CTTP003', 'THANH_PHAM', 'HOAN_THANH', NULL, '2026-10-27'),
('CTTP006', 'THANH_PHAM', 'HOAN_THANH', NULL, '2026-10-28'),
('CTTP007', 'THANH_PHAM', 'CHO_DUYET', NULL, '2026-10-28');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hang_hoa`
--

CREATE TABLE `hang_hoa` (
  `MaHang` varchar(30) NOT NULL,
  `TenHang` varchar(150) NOT NULL,
  `LoaiHang` enum('NGUYEN_LIEU','THANH_PHAM') NOT NULL,
  `DonViTinh` varchar(30) NOT NULL,
  `TrangThai` enum('ACTIVE','LOCKED') NOT NULL DEFAULT 'ACTIVE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `hang_hoa`
--

INSERT INTO `hang_hoa` (`MaHang`, `TenHang`, `LoaiHang`, `DonViTinh`, `TrangThai`) VALUES
('NL001', 'Hạt nhựa PET', 'NGUYEN_LIEU', 'Kg', 'ACTIVE'),
('TP001', 'Chai nước đóng chai 500ml', 'THANH_PHAM', 'Thùng', 'ACTIVE'),
('TP003', 'Chai nước đóng chai 500ml - Lô 3', 'THANH_PHAM', 'Thùng', 'ACTIVE');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ke_hoach_kiem_ke`
--

CREATE TABLE `ke_hoach_kiem_ke` (
  `MaKeHoachKiemKe` varchar(20) NOT NULL,
  `NgayKiemKe` date NOT NULL,
  `PhamViKiemKe` varchar(300) NOT NULL,
  `TrangThai` enum('NHAP','DA_DUYET','DANG_KIEM_KE','HOAN_TAT','HUY') NOT NULL DEFAULT 'NHAP',
  `NguoiLap` varchar(20) NOT NULL,
  `NgayTao` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `ke_hoach_kiem_ke`
--

INSERT INTO `ke_hoach_kiem_ke` (`MaKeHoachKiemKe`, `NgayKiemKe`, `PhamViKiemKe`, `TrangThai`, `NguoiLap`, `NgayTao`) VALUES
('KKDEMO001', '2026-10-28', 'Kiem ke kho thu nghiem', 'DANG_KIEM_KE', 'TK001', '2026-10-28 23:35:53');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ke_hoach_kiem_ke_kho`
--

CREATE TABLE `ke_hoach_kiem_ke_kho` (
  `MaKeHoachKiemKe` varchar(20) NOT NULL,
  `MaKho` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `ke_hoach_kiem_ke_kho`
--

INSERT INTO `ke_hoach_kiem_ke_kho` (`MaKeHoachKiemKe`, `MaKho`) VALUES
('KKDEMO001', 'KHO01');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ke_hoach_nguyen_lieu`
--

CREATE TABLE `ke_hoach_nguyen_lieu` (
  `MaKeHoach` varchar(30) NOT NULL,
  `NgayLap` datetime NOT NULL DEFAULT current_timestamp(),
  `MaNguoiLap` varchar(20) NOT NULL,
  `ThoiGianSuDung` date NOT NULL,
  `TrangThai` enum('NHAP','CHO_PHE_DUYET','CHO_CHINH_SUA','DA_PHE_DUYET','TU_CHOI') NOT NULL DEFAULT 'NHAP',
  `LyDo` varchar(500) DEFAULT NULL COMMENT 'Lý do từ chối / nội dung yêu cầu chỉnh sửa của Ban giám đốc',
  `GhiChu` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `ke_hoach_nguyen_lieu`
--

INSERT INTO `ke_hoach_nguyen_lieu` (`MaKeHoach`, `NgayLap`, `MaNguoiLap`, `ThoiGianSuDung`, `TrangThai`, `LyDo`, `GhiChu`) VALUES
('KH261028A001', '2026-10-28 20:38:28', 'TK_NVKHO01', '2026-11-11', 'CHO_PHE_DUYET', 'Giảm số lượng hạt nhựa PET xuống còn 800 kg.', 'Kế hoạch sản xuất tuần tới'),
('KH261028A002', '2026-10-28 20:38:28', 'TK_NVKHO01', '2026-11-27', 'CHO_PHE_DUYET', NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `kho`
--

CREATE TABLE `kho` (
  `MaKho` varchar(20) NOT NULL,
  `TenKho` varchar(100) NOT NULL,
  `SucChua` decimal(14,2) NOT NULL,
  `TrangThai` enum('ACTIVE','LOCKED') NOT NULL DEFAULT 'ACTIVE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `kho`
--

INSERT INTO `kho` (`MaKho`, `TenKho`, `SucChua`, `TrangThai`) VALUES
('KHO01', 'Kho nguyên liệu A', 10000.00, 'ACTIVE'),
('KHO02', 'Kho thành phẩm B', 5000.00, 'ACTIVE');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lich_su_thao_tac`
--

CREATE TABLE `lich_su_thao_tac` (
  `MaLichSu` bigint(20) UNSIGNED NOT NULL,
  `MaTaiKhoan` varchar(20) DEFAULT NULL,
  `ThoiGian` datetime DEFAULT current_timestamp(),
  `HanhDong` varchar(100) NOT NULL,
  `DoiTuong` varchar(100) DEFAULT NULL,
  `MaDoiTuong` varchar(30) DEFAULT NULL,
  `NoiDung` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `lich_su_thao_tac`
--

INSERT INTO `lich_su_thao_tac` (`MaLichSu`, `MaTaiKhoan`, `ThoiGian`, `HanhDong`, `DoiTuong`, `MaDoiTuong`, `NoiDung`) VALUES
(1, 'TK_NVKHO01', '2026-10-27 22:35:29', 'DANG_NHAP', 'tai_khoan', 'TK_NVKHO01', 'Đăng nhập thành công'),
(2, 'TK_NVKHO01', '2026-10-27 23:39:33', 'LAP_PHIEU_NHAP', 'phieu_nhap_kho', 'PN20261027233933AF4D75', 'Lập phiếu nhập từ chứng từ CTNL001'),
(3, 'TK_NVKHO01', '2026-10-27 23:40:04', 'LAP_PHIEU_NHAP', 'phieu_nhap_kho', 'PN2026102723400410D36F', 'Lập phiếu nhập từ chứng từ CTTP003'),
(4, 'TK_NVKHO01', '2026-10-27 23:55:14', 'LAP_PHIEU_NHAP', 'phieu_nhap_kho', 'PN20261027235514F18E88', 'Lập phiếu nhập từ chứng từ CTTP001'),
(5, 'TK_NVKHO01', '2026-10-28 00:04:31', 'LAP_PHIEU_NHAP', 'phieu_nhap_kho', 'PN202610280004317DF479', 'Lập phiếu nhập từ chứng từ CTTP001'),
(6, 'TK_NVKHO01', '2026-10-28 00:07:25', 'LAP_PHIEU_NHAP', 'phieu_nhap_kho', 'PN202610280007258B5A3B', 'Lập phiếu nhập từ chứng từ CTTP001'),
(7, 'TK_NVKHO01', '2026-10-28 00:20:59', 'LAP_PHIEU_NHAP', 'phieu_nhap_kho', 'PN202610280020590BAA64', 'Lập phiếu nhập từ chứng từ CTNL008'),
(8, 'TK_NVKHO01', '2026-10-28 00:48:52', 'LAP_PHIEU_NHAP', 'phieu_nhap_kho', 'PN20261028004852D432CB', 'Lập phiếu nhập từ chứng từ CTNL004'),
(9, 'TK001', '2026-10-28 20:49:40', 'DANG_NHAP', 'tai_khoan', 'TK001', 'Đăng nhập thành công'),
(10, 'TK001', '2026-10-28 23:29:56', 'DANG_NHAP', 'tai_khoan', 'TK001', 'Đăng nhập thành công'),
(11, 'TK001', '2026-10-28 23:31:43', 'CAP_NHAT_KE_HOACH', 'ke_hoach_nguyen_lieu', 'KH261028A001', 'Chuyển trạng thái CHO_PHE_DUYET'),
(12, 'TK001', '2026-10-28 23:36:30', 'LUU_NHAP_KIEM_KE', 'bien_ban_kiem_ke', 'BB26102823363019380226', 'Kế hoạch KKDEMO001; kho KHO01');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lo_hang`
--

CREATE TABLE `lo_hang` (
  `MaLo` varchar(30) NOT NULL,
  `MaHang` varchar(30) NOT NULL,
  `MaPhieuNhap` varchar(30) NOT NULL,
  `SoLuongNhap` decimal(12,2) NOT NULL,
  `TrangThai` enum('DA_NHAP_KHO') NOT NULL DEFAULT 'DA_NHAP_KHO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `lo_hang`
--

INSERT INTO `lo_hang` (`MaLo`, `MaHang`, `MaPhieuNhap`, `SoLuongNhap`, `TrangThai`) VALUES
('LO20261027233933E43CEC', 'NL001', 'PN20261027233933AF4D75', 500.00, 'DA_NHAP_KHO'),
('LO20261027234004C8454F', 'TP003', 'PN2026102723400410D36F', 500.00, 'DA_NHAP_KHO'),
('LO2026102723551434AB90', 'TP001', 'PN20261027235514F18E88', 10.00, 'DA_NHAP_KHO'),
('LO20261028000431986947', 'TP001', 'PN202610280004317DF479', 10.00, 'DA_NHAP_KHO'),
('LO2026102800072590159F', 'TP001', 'PN202610280007258B5A3B', 10.00, 'DA_NHAP_KHO'),
('LO202610280020597FA170', 'NL001', 'PN202610280020590BAA64', 20.00, 'DA_NHAP_KHO'),
('LO2026102800485283CA1D', 'NL001', 'PN20261028004852D432CB', 20.00, 'DA_NHAP_KHO');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieu_hang_loi`
--

CREATE TABLE `phieu_hang_loi` (
  `MaPhieu` varchar(24) NOT NULL,
  `NgayLap` date NOT NULL,
  `LoaiHang` enum('NGUYEN_LIEU','THANH_PHAM') NOT NULL,
  `MaHang` varchar(30) NOT NULL,
  `TenHang` varchar(150) NOT NULL,
  `SoLuong` decimal(12,2) NOT NULL,
  `DonViTinh` varchar(30) NOT NULL,
  `LyDo` varchar(250) NOT NULL,
  `HuongXuLy` enum('TIEU_HUY','TRA_NCC','TAI_CHE') NOT NULL,
  `NhaCungCap` varchar(150) DEFAULT NULL,
  `TrangThai` enum('CHO_DUYET','DANG_XU_LY','DA_XU_LY','TU_CHOI') NOT NULL DEFAULT 'CHO_DUYET',
  `GhiChu` text DEFAULT NULL,
  `HinhAnh` varchar(255) DEFAULT NULL,
  `MaNguoiLap` varchar(20) NOT NULL,
  `NgayTao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `phieu_hang_loi`
--

INSERT INTO `phieu_hang_loi` (`MaPhieu`, `NgayLap`, `LoaiHang`, `MaHang`, `TenHang`, `SoLuong`, `DonViTinh`, `LyDo`, `HuongXuLy`, `NhaCungCap`, `TrangThai`, `GhiChu`, `HinhAnh`, `MaNguoiLap`, `NgayTao`) VALUES
('HL001', '2026-09-05', 'NGUYEN_LIEU', 'NL002', 'Chai PET 500ml', 500.00, 'Cái', 'Bị móp', 'TRA_NCC', 'Công ty Nhựa ABC', 'DANG_XU_LY', 'Chai bị móp, không đạt chất lượng.', NULL, 'TK001', '2026-10-27 22:01:04'),
('HL002', '2026-09-08', 'NGUYEN_LIEU', 'NL003', 'Nắp chai', 1000.00, 'Cái', 'Nứt, vỡ', 'TAI_CHE', NULL, 'DA_XU_LY', 'Đã chuyển sang khu tái chế.', NULL, 'TK001', '2026-10-27 22:01:04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieu_nhap_kho`
--

CREATE TABLE `phieu_nhap_kho` (
  `MaPhieuNhap` varchar(30) NOT NULL,
  `NgayNhap` datetime NOT NULL DEFAULT current_timestamp(),
  `LoaiNhap` enum('NGUYEN_LIEU','THANH_PHAM') NOT NULL,
  `MaChungTu` varchar(30) NOT NULL,
  `MaKho` varchar(20) NOT NULL,
  `MaNguoiLap` varchar(20) NOT NULL,
  `TrangThai` enum('DA_NHAP') NOT NULL DEFAULT 'DA_NHAP'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `phieu_nhap_kho`
--

INSERT INTO `phieu_nhap_kho` (`MaPhieuNhap`, `NgayNhap`, `LoaiNhap`, `MaChungTu`, `MaKho`, `MaNguoiLap`, `TrangThai`) VALUES
('PN20261027233933AF4D75', '2026-10-27 23:39:33', 'NGUYEN_LIEU', 'CTNL001', 'KHO01', 'TK_NVKHO01', 'DA_NHAP'),
('PN2026102723400410D36F', '2026-10-27 23:40:04', 'THANH_PHAM', 'CTTP003', 'KHO01', 'TK_NVKHO01', 'DA_NHAP'),
('PN20261027235514F18E88', '2026-10-27 23:55:14', 'THANH_PHAM', 'CTTP001', 'KHO01', 'TK_NVKHO01', 'DA_NHAP'),
('PN202610280004317DF479', '2026-10-28 00:04:31', 'THANH_PHAM', 'CTTP001', 'KHO02', 'TK_NVKHO01', 'DA_NHAP'),
('PN202610280007258B5A3B', '2026-10-28 00:07:25', 'THANH_PHAM', 'CTTP001', 'KHO01', 'TK_NVKHO01', 'DA_NHAP'),
('PN202610280020590BAA64', '2026-10-28 00:20:59', 'NGUYEN_LIEU', 'CTNL008', 'KHO01', 'TK_NVKHO01', 'DA_NHAP'),
('PN20261028004852D432CB', '2026-10-28 00:48:52', 'NGUYEN_LIEU', 'CTNL004', 'KHO02', 'TK_NVKHO01', 'DA_NHAP');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `quyen`
--

CREATE TABLE `quyen` (
  `MaQuyen` varchar(30) NOT NULL,
  `TenQuyen` varchar(100) NOT NULL,
  `MoTa` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `quyen`
--

INSERT INTO `quyen` (`MaQuyen`, `TenQuyen`, `MoTa`) VALUES
('HANGLOI_QUANLY', 'Quản lý hàng lỗi', 'Tạo sửa xóa phiếu và xử lý'),
('HANGLOI_XEM', 'Xem hàng lỗi', 'Xem danh sách phiếu'),
('KEHOACH_QUANLY', 'Quản lý kế hoạch nguyên liệu', 'Tạo và chỉnh sửa kế hoạch nguyên liệu'),
('KEHOACH_XEM', 'Xem kế hoạch nguyên liệu', 'Xem danh sách và chi tiết kế hoạch nguyên liệu'),
('KIEMKE_LAP', 'Lập biên bản kiểm kê', 'Kiểm kê, lưu nháp và xác nhận'),
('NHAPKHO_QUANLY', 'Quản lý nhập kho', 'Lập và quản lý phiếu nhập kho'),
('TAIKHOAN_QUANLY', 'Quản lý tài khoản', 'Thêm sửa khóa tài khoản và gán vai trò'),
('TAIKHOAN_XEM', 'Xem tài khoản', 'Xem danh sách tài khoản');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tai_khoan`
--

CREATE TABLE `tai_khoan` (
  `MaTaiKhoan` varchar(20) NOT NULL,
  `TenDangNhap` varchar(50) NOT NULL,
  `MatKhauHash` varchar(255) NOT NULL,
  `HoTen` varchar(120) NOT NULL,
  `Email` varchar(150) DEFAULT NULL,
  `SoDienThoai` varchar(30) DEFAULT NULL,
  `MaVaiTro` varchar(20) NOT NULL,
  `TrangThai` enum('ACTIVE','LOCKED') NOT NULL DEFAULT 'ACTIVE',
  `NgayTao` datetime DEFAULT current_timestamp(),
  `LanDangNhapCuoi` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tai_khoan`
--

INSERT INTO `tai_khoan` (`MaTaiKhoan`, `TenDangNhap`, `MatKhauHash`, `HoTen`, `Email`, `SoDienThoai`, `MaVaiTro`, `TrangThai`, `NgayTao`, `LanDangNhapCuoi`) VALUES
('TK_NVKHO01', 'nhanvienkho', '$2y$12$g8DhGy8/EoqrluNGr4T3h.09DRP1OMFWkHpSlnJ7crVRVrpxHSPrC', 'Nhân viên kho', 'nhanvienkho@example.com', NULL, 'NVKHO', 'ACTIVE', '2026-10-27 22:30:26', '2026-10-27 22:35:29'),
('TK001', 'admin', '$2y$12$JD.BvTqEYHKMdoO24ZwsBuiaxVplvBJbhtLz/m4skitxasDt126gG', 'Quản trị hệ thống', 'admin@example.com', NULL, 'ADMIN', 'ACTIVE', '2026-10-27 22:01:04', '2026-10-28 23:29:56');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ton_kho`
--

CREATE TABLE `ton_kho` (
  `MaKho` varchar(20) NOT NULL,
  `MaHang` varchar(30) NOT NULL,
  `SoLuongTon` decimal(14,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `ton_kho`
--

INSERT INTO `ton_kho` (`MaKho`, `MaHang`, `SoLuongTon`) VALUES
('KHO01', 'NL001', 520.00),
('KHO01', 'TP001', 20.00),
('KHO01', 'TP003', 500.00),
('KHO02', 'NL001', 20.00),
('KHO02', 'TP001', 10.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `vai_tro`
--

CREATE TABLE `vai_tro` (
  `MaVaiTro` varchar(20) NOT NULL,
  `TenVaiTro` varchar(100) NOT NULL,
  `MoTa` varchar(300) DEFAULT NULL,
  `TrangThai` enum('ACTIVE','LOCKED') NOT NULL DEFAULT 'ACTIVE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `vai_tro`
--

INSERT INTO `vai_tro` (`MaVaiTro`, `TenVaiTro`, `MoTa`, `TrangThai`) VALUES
('ADMIN', 'Admin', 'Quản trị tài khoản và quyền', 'ACTIVE'),
('HOIDONG', 'Hội đồng kiểm kê', 'Lập biên bản kiểm kê', 'ACTIVE'),
('KIEMDINH', 'Bộ phận kiểm định', 'Xem thông tin hàng lỗi', 'ACTIVE'),
('NVKHO', 'Nhân viên kho', 'Quản lý phiếu hàng lỗi/trả về', 'ACTIVE');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `vai_tro_quyen`
--

CREATE TABLE `vai_tro_quyen` (
  `MaVaiTro` varchar(20) NOT NULL,
  `MaQuyen` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `vai_tro_quyen`
--

INSERT INTO `vai_tro_quyen` (`MaVaiTro`, `MaQuyen`) VALUES
('ADMIN', 'HANGLOI_QUANLY'),
('ADMIN', 'HANGLOI_XEM'),
('ADMIN', 'KEHOACH_QUANLY'),
('ADMIN', 'KEHOACH_XEM'),
('ADMIN', 'KIEMKE_LAP'),
('ADMIN', 'NHAPKHO_QUANLY'),
('ADMIN', 'TAIKHOAN_QUANLY'),
('ADMIN', 'TAIKHOAN_XEM'),
('HOIDONG', 'KIEMKE_LAP'),
('KIEMDINH', 'HANGLOI_XEM'),
('NVKHO', 'HANGLOI_QUANLY'),
('NVKHO', 'HANGLOI_XEM'),
('NVKHO', 'KEHOACH_QUANLY'),
('NVKHO', 'KEHOACH_XEM'),
('NVKHO', 'NHAPKHO_QUANLY');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `bien_ban_kiem_ke`
--
ALTER TABLE `bien_ban_kiem_ke`
  ADD PRIMARY KEY (`MaBienBan`),
  ADD UNIQUE KEY `uq_uc22_kehoach_kho` (`MaKeHoachKiemKe`,`MaKho`),
  ADD KEY `fk_uc22_bb_nguoilap` (`NguoiLap`);

--
-- Chỉ mục cho bảng `chi_tiet_chung_tu_nguon`
--
ALTER TABLE `chi_tiet_chung_tu_nguon`
  ADD PRIMARY KEY (`MaChiTiet`),
  ADD KEY `MaChungTu` (`MaChungTu`),
  ADD KEY `MaHang` (`MaHang`);

--
-- Chỉ mục cho bảng `chi_tiet_ke_hoach`
--
ALTER TABLE `chi_tiet_ke_hoach`
  ADD PRIMARY KEY (`MaChiTiet`),
  ADD UNIQUE KEY `uq_kehoach_hang` (`MaKeHoach`,`MaHang`),
  ADD KEY `MaHang` (`MaHang`);

--
-- Chỉ mục cho bảng `chi_tiet_kiem_ke`
--
ALTER TABLE `chi_tiet_kiem_ke`
  ADD PRIMARY KEY (`MaChiTietKiemKe`),
  ADD UNIQUE KEY `uq_uc22_bb_hang` (`MaBienBan`,`MaHang`),
  ADD KEY `fk_uc22_ct_hang` (`MaHang`);

--
-- Chỉ mục cho bảng `chi_tiet_phieu_nhap`
--
ALTER TABLE `chi_tiet_phieu_nhap`
  ADD PRIMARY KEY (`MaChiTietNhap`),
  ADD KEY `MaPhieuNhap` (`MaPhieuNhap`),
  ADD KEY `MaHang` (`MaHang`),
  ADD KEY `MaChiTietNguon` (`MaChiTietNguon`);

--
-- Chỉ mục cho bảng `chung_tu_nguon`
--
ALTER TABLE `chung_tu_nguon`
  ADD PRIMARY KEY (`MaChungTu`);

--
-- Chỉ mục cho bảng `hang_hoa`
--
ALTER TABLE `hang_hoa`
  ADD PRIMARY KEY (`MaHang`);

--
-- Chỉ mục cho bảng `ke_hoach_kiem_ke`
--
ALTER TABLE `ke_hoach_kiem_ke`
  ADD PRIMARY KEY (`MaKeHoachKiemKe`),
  ADD KEY `fk_uc22_kh_nguoilap` (`NguoiLap`);

--
-- Chỉ mục cho bảng `ke_hoach_kiem_ke_kho`
--
ALTER TABLE `ke_hoach_kiem_ke_kho`
  ADD PRIMARY KEY (`MaKeHoachKiemKe`,`MaKho`),
  ADD KEY `fk_uc22_scope_kho` (`MaKho`);

--
-- Chỉ mục cho bảng `ke_hoach_nguyen_lieu`
--
ALTER TABLE `ke_hoach_nguyen_lieu`
  ADD PRIMARY KEY (`MaKeHoach`),
  ADD KEY `MaNguoiLap` (`MaNguoiLap`),
  ADD KEY `TrangThai` (`TrangThai`);

--
-- Chỉ mục cho bảng `kho`
--
ALTER TABLE `kho`
  ADD PRIMARY KEY (`MaKho`);

--
-- Chỉ mục cho bảng `lich_su_thao_tac`
--
ALTER TABLE `lich_su_thao_tac`
  ADD PRIMARY KEY (`MaLichSu`),
  ADD KEY `MaTaiKhoan` (`MaTaiKhoan`);

--
-- Chỉ mục cho bảng `lo_hang`
--
ALTER TABLE `lo_hang`
  ADD PRIMARY KEY (`MaLo`),
  ADD KEY `MaHang` (`MaHang`),
  ADD KEY `MaPhieuNhap` (`MaPhieuNhap`);

--
-- Chỉ mục cho bảng `phieu_hang_loi`
--
ALTER TABLE `phieu_hang_loi`
  ADD PRIMARY KEY (`MaPhieu`),
  ADD KEY `MaNguoiLap` (`MaNguoiLap`);

--
-- Chỉ mục cho bảng `phieu_nhap_kho`
--
ALTER TABLE `phieu_nhap_kho`
  ADD PRIMARY KEY (`MaPhieuNhap`),
  ADD KEY `MaChungTu` (`MaChungTu`),
  ADD KEY `MaKho` (`MaKho`),
  ADD KEY `MaNguoiLap` (`MaNguoiLap`);

--
-- Chỉ mục cho bảng `quyen`
--
ALTER TABLE `quyen`
  ADD PRIMARY KEY (`MaQuyen`),
  ADD UNIQUE KEY `TenQuyen` (`TenQuyen`);

--
-- Chỉ mục cho bảng `tai_khoan`
--
ALTER TABLE `tai_khoan`
  ADD PRIMARY KEY (`MaTaiKhoan`),
  ADD UNIQUE KEY `TenDangNhap` (`TenDangNhap`),
  ADD KEY `MaVaiTro` (`MaVaiTro`);

--
-- Chỉ mục cho bảng `ton_kho`
--
ALTER TABLE `ton_kho`
  ADD PRIMARY KEY (`MaKho`,`MaHang`),
  ADD KEY `MaHang` (`MaHang`);

--
-- Chỉ mục cho bảng `vai_tro`
--
ALTER TABLE `vai_tro`
  ADD PRIMARY KEY (`MaVaiTro`),
  ADD UNIQUE KEY `TenVaiTro` (`TenVaiTro`);

--
-- Chỉ mục cho bảng `vai_tro_quyen`
--
ALTER TABLE `vai_tro_quyen`
  ADD PRIMARY KEY (`MaVaiTro`,`MaQuyen`),
  ADD KEY `MaQuyen` (`MaQuyen`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `chi_tiet_chung_tu_nguon`
--
ALTER TABLE `chi_tiet_chung_tu_nguon`
  MODIFY `MaChiTiet` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `chi_tiet_ke_hoach`
--
ALTER TABLE `chi_tiet_ke_hoach`
  MODIFY `MaChiTiet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `chi_tiet_kiem_ke`
--
ALTER TABLE `chi_tiet_kiem_ke`
  MODIFY `MaChiTietKiemKe` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `chi_tiet_phieu_nhap`
--
ALTER TABLE `chi_tiet_phieu_nhap`
  MODIFY `MaChiTietNhap` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `lich_su_thao_tac`
--
ALTER TABLE `lich_su_thao_tac`
  MODIFY `MaLichSu` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Ràng buộc đối với các bảng kết xuất
--

--
-- Ràng buộc cho bảng `bien_ban_kiem_ke`
--
ALTER TABLE `bien_ban_kiem_ke`
  ADD CONSTRAINT `fk_uc22_bb_nguoilap` FOREIGN KEY (`NguoiLap`) REFERENCES `tai_khoan` (`MaTaiKhoan`),
  ADD CONSTRAINT `fk_uc22_bb_scope` FOREIGN KEY (`MaKeHoachKiemKe`,`MaKho`) REFERENCES `ke_hoach_kiem_ke_kho` (`MaKeHoachKiemKe`, `MaKho`);

--
-- Ràng buộc cho bảng `chi_tiet_chung_tu_nguon`
--
ALTER TABLE `chi_tiet_chung_tu_nguon`
  ADD CONSTRAINT `chi_tiet_chung_tu_nguon_ibfk_1` FOREIGN KEY (`MaChungTu`) REFERENCES `chung_tu_nguon` (`MaChungTu`),
  ADD CONSTRAINT `chi_tiet_chung_tu_nguon_ibfk_2` FOREIGN KEY (`MaHang`) REFERENCES `hang_hoa` (`MaHang`);

--
-- Ràng buộc cho bảng `chi_tiet_ke_hoach`
--
ALTER TABLE `chi_tiet_ke_hoach`
  ADD CONSTRAINT `fk_ctkh_kehoach` FOREIGN KEY (`MaKeHoach`) REFERENCES `ke_hoach_nguyen_lieu` (`MaKeHoach`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `chi_tiet_kiem_ke`
--
ALTER TABLE `chi_tiet_kiem_ke`
  ADD CONSTRAINT `fk_uc22_ct_bb` FOREIGN KEY (`MaBienBan`) REFERENCES `bien_ban_kiem_ke` (`MaBienBan`),
  ADD CONSTRAINT `fk_uc22_ct_hang` FOREIGN KEY (`MaHang`) REFERENCES `hang_hoa` (`MaHang`);

--
-- Ràng buộc cho bảng `chi_tiet_phieu_nhap`
--
ALTER TABLE `chi_tiet_phieu_nhap`
  ADD CONSTRAINT `chi_tiet_phieu_nhap_ibfk_1` FOREIGN KEY (`MaPhieuNhap`) REFERENCES `phieu_nhap_kho` (`MaPhieuNhap`),
  ADD CONSTRAINT `chi_tiet_phieu_nhap_ibfk_2` FOREIGN KEY (`MaHang`) REFERENCES `hang_hoa` (`MaHang`),
  ADD CONSTRAINT `chi_tiet_phieu_nhap_ibfk_3` FOREIGN KEY (`MaChiTietNguon`) REFERENCES `chi_tiet_chung_tu_nguon` (`MaChiTiet`);

--
-- Ràng buộc cho bảng `ke_hoach_kiem_ke`
--
ALTER TABLE `ke_hoach_kiem_ke`
  ADD CONSTRAINT `fk_uc22_kh_nguoilap` FOREIGN KEY (`NguoiLap`) REFERENCES `tai_khoan` (`MaTaiKhoan`);

--
-- Ràng buộc cho bảng `ke_hoach_kiem_ke_kho`
--
ALTER TABLE `ke_hoach_kiem_ke_kho`
  ADD CONSTRAINT `fk_uc22_scope_kh` FOREIGN KEY (`MaKeHoachKiemKe`) REFERENCES `ke_hoach_kiem_ke` (`MaKeHoachKiemKe`),
  ADD CONSTRAINT `fk_uc22_scope_kho` FOREIGN KEY (`MaKho`) REFERENCES `kho` (`MaKho`);

--
-- Ràng buộc cho bảng `lich_su_thao_tac`
--
ALTER TABLE `lich_su_thao_tac`
  ADD CONSTRAINT `lich_su_thao_tac_ibfk_1` FOREIGN KEY (`MaTaiKhoan`) REFERENCES `tai_khoan` (`MaTaiKhoan`) ON DELETE SET NULL;

--
-- Ràng buộc cho bảng `lo_hang`
--
ALTER TABLE `lo_hang`
  ADD CONSTRAINT `lo_hang_ibfk_1` FOREIGN KEY (`MaHang`) REFERENCES `hang_hoa` (`MaHang`),
  ADD CONSTRAINT `lo_hang_ibfk_2` FOREIGN KEY (`MaPhieuNhap`) REFERENCES `phieu_nhap_kho` (`MaPhieuNhap`);

--
-- Ràng buộc cho bảng `phieu_hang_loi`
--
ALTER TABLE `phieu_hang_loi`
  ADD CONSTRAINT `phieu_hang_loi_ibfk_1` FOREIGN KEY (`MaNguoiLap`) REFERENCES `tai_khoan` (`MaTaiKhoan`);

--
-- Ràng buộc cho bảng `phieu_nhap_kho`
--
ALTER TABLE `phieu_nhap_kho`
  ADD CONSTRAINT `phieu_nhap_kho_ibfk_1` FOREIGN KEY (`MaChungTu`) REFERENCES `chung_tu_nguon` (`MaChungTu`),
  ADD CONSTRAINT `phieu_nhap_kho_ibfk_2` FOREIGN KEY (`MaKho`) REFERENCES `kho` (`MaKho`),
  ADD CONSTRAINT `phieu_nhap_kho_ibfk_3` FOREIGN KEY (`MaNguoiLap`) REFERENCES `tai_khoan` (`MaTaiKhoan`);

--
-- Ràng buộc cho bảng `tai_khoan`
--
ALTER TABLE `tai_khoan`
  ADD CONSTRAINT `tai_khoan_ibfk_1` FOREIGN KEY (`MaVaiTro`) REFERENCES `vai_tro` (`MaVaiTro`);

--
-- Ràng buộc cho bảng `ton_kho`
--
ALTER TABLE `ton_kho`
  ADD CONSTRAINT `ton_kho_ibfk_1` FOREIGN KEY (`MaKho`) REFERENCES `kho` (`MaKho`),
  ADD CONSTRAINT `ton_kho_ibfk_2` FOREIGN KEY (`MaHang`) REFERENCES `hang_hoa` (`MaHang`);

--
-- Ràng buộc cho bảng `vai_tro_quyen`
--
ALTER TABLE `vai_tro_quyen`
  ADD CONSTRAINT `vai_tro_quyen_ibfk_1` FOREIGN KEY (`MaVaiTro`) REFERENCES `vai_tro` (`MaVaiTro`),
  ADD CONSTRAINT `vai_tro_quyen_ibfk_2` FOREIGN KEY (`MaQuyen`) REFERENCES `quyen` (`MaQuyen`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

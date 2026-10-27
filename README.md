# Hệ thống Quản lý kho hàng – PHP MVC

Bản khởi đầu cho phần nghiệp vụ do **Lê Tấn Kiệt** phụ trách, dựa trên đặc tả trong tài liệu Word của nhóm Đom Đóm:

- Đăng nhập và đăng xuất.
- Quản lý tài khoản: thêm, cập nhật, gán vai trò, khóa tài khoản; lưu nhật ký thao tác.
- Quản lý hàng lỗi / hàng trả về: tìm kiếm, lọc, tạo/sửa/xóa phiếu, cập nhật trạng thái và hình thức xử lý **tiêu hủy / trả nhà cung cấp / tái chế**.
- Phân quyền theo vai trò và quyền được gán trong cơ sở dữ liệu.

## 1. Yêu cầu

- XAMPP (Apache + MySQL/MariaDB) và PHP 8+.
- Trình duyệt web.

## 2. Cài đặt trên Windows/XAMPP

1. Giải nén thư mục `QLKhoDomDom_MVC` vào `C:\xampp\htdocs\`.
2. Mở XAMPP Control Panel, bật **Apache** và **MySQL**.
3. Truy cập `http://localhost/phpmyadmin`.
4. Chọn **Import** và nhập file `database/qlkho_domdom.sql`.
5. Kiểm tra cấu hình DB tại `app/config/config.php` (mặc định XAMPP: user `root`, mật khẩu rỗng).
6. Mở `http://localhost/QLKhoDomDom_MVC/public/`.

Nếu đổi tên thư mục dự án, hãy sửa `BASE_URL` trong `app/config/config.php` cho khớp đường dẫn.

## 3. Tài khoản demo

- Tên đăng nhập: `admin`
- Mật khẩu: `password`

Đây là tài khoản thử nghiệm trên máy local. Hãy đổi mật khẩu trước khi triển khai hoặc chia sẻ hệ thống ra môi trường thật.

## 4. Cấu trúc MVC

```text
QLKhoDomDom_MVC/
├── app/
│   ├── config/       # Cấu hình ứng dụng và kết nối
│   ├── controllers/  # Nhận request, kiểm tra quyền, điều phối nghiệp vụ
│   ├── core/         # Database, Auth, BaseController
│   ├── models/       # UserModel, AccountModel, DefectModel
│   └── views/        # Giao diện đăng nhập, tổng quan, tài khoản, hàng lỗi
├── database/         # SQL tạo database và dữ liệu demo
└── public/           # Front controller, CSS, tài nguyên public
```

## 5. Vai trò demo

- **Admin**: quản lý tài khoản/phân quyền và quản lý phiếu hàng lỗi/trả về.
- **Nhân viên kho**: quản lý phiếu hàng lỗi/trả về.
- **Bộ phận kiểm định**: xem danh sách hàng lỗi/trả về.

Các vai trò và quyền được lưu trong các bảng `vai_tro`, `quyen`, `vai_tro_quyen`. Có thể mở rộng vai trò theo các actor khác trong đặc tả sau khi nhóm tích hợp thêm các phân hệ.

## 6. Lưu ý

- Truy vấn SQL sử dụng PDO prepared statements; mật khẩu tài khoản được băm bằng `password_hash`.
- Tài khoản bị khóa sẽ không đăng nhập được. Nút khóa tài khoản dùng trạng thái `LOCKED` thay vì xóa vật lý để giữ lịch sử nghiệp vụ.
- Hệ thống ghi nhận đăng nhập/đăng xuất và các thao tác chính vào `lich_su_thao_tac`.
- Đây là bản nền MVC cho nhóm tiếp tục phát triển; cần bổ sung CSRF protection, giới hạn đăng nhập, kiểm thử quyền và rà soát bảo mật trước khi triển khai thực tế.

## 7. GitHub

Chưa đẩy code lên GitHub vì chưa nhận được URL repository dành riêng cho dự án quản lý kho hàng. Không sử dụng repository khác.

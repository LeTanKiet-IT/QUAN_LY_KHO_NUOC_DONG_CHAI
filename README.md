# Hệ thống Quản lý kho hàng – PHP MVC

Bản khởi đầu cho phần nghiệp vụ do **Lê Tấn Kiệt** phụ trách, dựa trên đặc tả trong tài liệu Word của nhóm Đom Đóm:

- Đăng nhập và đăng xuất.
- Quản lý tài khoản: thêm, cập nhật, gán vai trò, khóa tài khoản; lưu nhật ký thao tác.
- Quản lý hàng lỗi / hàng trả về: tìm kiếm, lọc, tạo/sửa/xóa phiếu, cập nhật trạng thái và hình thức xử lý **tiêu hủy / trả nhà cung cấp / tái chế**.
- Phân quyền theo vai trò và quyền được gán trong cơ sở dữ liệu.
## Cấu trúc MVC

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



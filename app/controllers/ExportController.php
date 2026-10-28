
<?php

class ExportController extends BaseController
{
    public function index(): void
    {
        // Kiểm tra đăng nhập
        Auth::loginRequired();

        // Lấy thông tin người dùng
        $user = Auth::user();

        // Chỉ Admin và nhân viên kho được truy cập
        if (!in_array(
            $user['MaVaiTro'] ?? '',
            ['ADMIN', 'NVKHO'],
            true
        )) {
            http_response_code(403);
            exit('Bạn không có quyền truy cập.');
        }

        // Hiển thị giao diện lập phiếu xuất kho
        $title = 'Lập phiếu xuất kho';

        $this->render(
            'exports/index',
            compact('title')
        );
    }
}

<?php
class AuthController extends BaseController
{
    public function form(): void
    {
        if (Auth::user()) { $this->go('/'); }
        $layout = 'auth'; $title = 'Đăng nhập'; require __DIR__.'/../views/auth/login.php';
    }

    public function login(): void
    {
        $username = trim($_POST['username'] ?? ''); $password = $_POST['password'] ?? '';
        if ($username === '' || $password === '') { $this->msg('error','Vui lòng nhập tên đăng nhập và mật khẩu.'); $this->go('/login'); }
        try {
            $model = new UserModel(); $row = $model->findForLogin($username);
            if (!$row || !password_verify($password,$row['MatKhauHash']) || $row['TrangThai'] !== 'ACTIVE' || $row['TrangThai'] === 'LOCKED') { $this->msg('error','Tên đăng nhập hoặc mật khẩu không chính xác, hoặc tài khoản đã khóa.'); $this->go('/login'); }
            session_regenerate_id(true);
            $_SESSION['user'] = ['MaTaiKhoan'=>$row['MaTaiKhoan'],'TenDangNhap'=>$row['TenDangNhap'],'HoTen'=>$row['HoTen'],'MaVaiTro'=>$row['MaVaiTro'],'TenVaiTro'=>$row['TenVaiTro'],'permissions'=>$model->permissions($row['MaVaiTro'])];
            $model->recordLogin($row['MaTaiKhoan']);
            Auth::log($row['MaTaiKhoan'],'DANG_NHAP','tai_khoan',$row['MaTaiKhoan'],'Đăng nhập thành công');
            $this->go('/');
        } catch (Throwable $e) { error_log($e->getMessage()); $this->msg('error','Không kết nối được cơ sở dữ liệu. Hãy kiểm tra cấu hình và import SQL.'); $this->go('/login'); }
    }

    public function logout(): void
    {
        Auth::loginRequired(); $user = Auth::user();
        Auth::log($user['MaTaiKhoan'],'DANG_XUAT','tai_khoan',$user['MaTaiKhoan'],'Người dùng xác nhận đăng xuất');
        $_SESSION = [];
        if (ini_get('session.use_cookies')) { $params = session_get_cookie_params(); setcookie(session_name(),'',time()-42000,$params['path'],$params['domain'],$params['secure'],$params['httponly']); }
        session_destroy(); header('Location: '.BASE_URL.'/login'); exit;
    }
}

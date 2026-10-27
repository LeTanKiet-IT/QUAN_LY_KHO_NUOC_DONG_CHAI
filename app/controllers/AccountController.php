<?php
class AccountController extends BaseController
{
    public function index(): void
    {
        Auth::allow('TAIKHOAN_XEM');
        $model = new AccountModel();
        $q = trim($_GET['q'] ?? ''); $role = $_GET['role'] ?? ''; $status = $_GET['status'] ?? '';
        $accounts = $model->search($q, $role, $status); $roles = $model->activeRoles();
        $title = 'Quản lý tài khoản & phân quyền';
        $this->render('accounts/index', compact('title','accounts','roles','q','role','status'));
    }

    public function save(): void
    {
        Auth::allow('TAIKHOAN_QUANLY');
        $id = trim($_POST['id'] ?? '');
        $data = ['username'=>trim($_POST['username'] ?? ''),'name'=>trim($_POST['name'] ?? ''),'password'=>$_POST['password'] ?? '', 'role'=>trim($_POST['role'] ?? ''),'status'=>$_POST['status'] ?? 'ACTIVE','email'=>trim($_POST['email'] ?? ''),'phone'=>trim($_POST['phone'] ?? '')];
        if ($data['username']==='' || $data['name']==='' || $data['role']==='' || (!$id && $data['password']==='') || ($data['password']!=='' && strlen($data['password'])<8) || !in_array($data['status'],['ACTIVE','LOCKED'],true)) {
            $this->msg('error','Thông tin chưa hợp lệ. Tên đăng nhập, họ tên, vai trò là bắt buộc; mật khẩu mới tối thiểu 8 ký tự.'); $this->go('/accounts');
        }
        if ($id === Auth::user()['MaTaiKhoan'] && ($data['status'] !== 'ACTIVE' || $data['role'] !== 'ADMIN')) {
            $this->msg('error','Không thể tự khóa hoặc tự hạ quyền tài khoản Admin đang đăng nhập.'); $this->go('/accounts');
        }
        try {
            $model = new AccountModel();
            if (!$model->roleExists($data['role'])) { $this->msg('error','Vai trò không tồn tại hoặc đang bị khóa.'); $this->go('/accounts'); }
            $savedId = $model->save($data, $id ?: null);
            Auth::log(Auth::user()['MaTaiKhoan'], $id ? 'CAP_NHAT_TAI_KHOAN' : 'TAO_TAI_KHOAN', 'tai_khoan', $savedId, 'Tài khoản '.$data['username'].'; vai trò '.$data['role']);
            $this->msg('success','Đã lưu tài khoản và vai trò.');
        } catch (Throwable $e) { error_log($e->getMessage()); $this->msg('error','Không lưu được. Hãy kiểm tra tên đăng nhập trùng hoặc dữ liệu chưa hợp lệ.'); }
        $this->go('/accounts');
    }

    public function delete(): void
    {
        Auth::allow('TAIKHOAN_QUANLY'); $id = trim($_POST['id'] ?? '');
        if ($id === '' || $id === Auth::user()['MaTaiKhoan']) { $this->msg('error','Không thể khóa tài khoản đang đăng nhập.'); $this->go('/accounts'); }
        try {
            $model = new AccountModel();
            $count = $model->lock($id);
            if ($count) { Auth::log(Auth::user()['MaTaiKhoan'],'KHOA_TAI_KHOAN','tai_khoan',$id,'Khóa tài khoản thay vì xóa vật lý để giữ lịch sử'); $this->msg('success','Đã khóa tài khoản.'); }
            else { $this->msg('error','Không tìm thấy tài khoản cần khóa.'); }
        } catch (Throwable $e) { error_log($e->getMessage()); $this->msg('error','Không thể khóa tài khoản.'); }
        $this->go('/accounts');
    }
}

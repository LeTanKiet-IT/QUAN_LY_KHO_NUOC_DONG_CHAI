<?php
class DefectController extends BaseController
{
    public function index(): void
    {
        Auth::allow('HANGLOI_XEM'); $model = new DefectModel();
        $q = trim($_GET['q'] ?? ''); $type = $_GET['type'] ?? ''; $status = $_GET['status'] ?? '';
        $items = $model->search($q, $type, $status); $editing = null;
        if (!empty($_GET['edit'])) { $editing = $model->find(trim($_GET['edit'])); }
        $title = 'Quản lý hàng lỗi / trả về';
        $this->render('defects/index', compact('title','items','editing','q','type','status'));
    }

    public function save(): void
    {
        Auth::allow('HANGLOI_QUANLY');
        $id = trim($_POST['id'] ?? '');
        $data = ['date'=>$_POST['date'] ?? '', 'type'=>$_POST['type'] ?? '', 'code'=>trim($_POST['code'] ?? ''), 'product'=>trim($_POST['product'] ?? ''), 'qty'=>$_POST['qty'] ?? '', 'unit'=>trim($_POST['unit'] ?? ''), 'reason'=>trim($_POST['reason'] ?? ''), 'action'=>$_POST['action'] ?? '', 'supplier'=>trim($_POST['supplier'] ?? ''), 'status'=>$_POST['status'] ?? 'CHO_DUYET', 'note'=>trim($_POST['note'] ?? '')];
        $validDate = $data['date'] !== '' && DateTime::createFromFormat('Y-m-d', $data['date']) !== false;
        $valid = $validDate && in_array($data['type'],['NGUYEN_LIEU','THANH_PHAM'],true) && $data['code']!=='' && $data['product']!=='' && is_numeric($data['qty']) && (float)$data['qty']>0 && $data['unit']!=='' && $data['reason']!=='' && in_array($data['action'],['TIEU_HUY','TRA_NCC','TAI_CHE'],true) && in_array($data['status'],['CHO_DUYET','DANG_XU_LY','DA_XU_LY','TU_CHOI'],true) && ($data['action']!=='TRA_NCC' || $data['supplier']!=='');
        if (!$valid) { $this->msg('error','Dữ liệu chưa hợp lệ. Số lượng phải lớn hơn 0; chọn Trả nhà cung cấp thì phải nhập nhà cung cấp.'); $this->go('/defects'); }
        try {
            $model = new DefectModel();
            if ($id !== '' && !$model->find($id)) { $this->msg('error','Dữ liệu phiếu không tồn tại.'); $this->go('/defects'); }
            $savedId = $model->save($data, $id ?: null, Auth::user()['MaTaiKhoan']);
            Auth::log(Auth::user()['MaTaiKhoan'], $id ? 'CAP_NHAT_PHIEU_HANG_LOI' : 'TAO_PHIEU_HANG_LOI', 'phieu_hang_loi', $savedId, 'Hướng xử lý '.$data['action'].'; trạng thái '.$data['status']);
            $this->msg('success','Đã lưu phiếu hàng lỗi / trả về.');
        } catch (Throwable $e) { error_log($e->getMessage()); $this->msg('error','Không lưu được phiếu. Vui lòng thử lại.'); }
        $this->go('/defects');
    }

    public function delete(): void
    {
        Auth::allow('HANGLOI_QUANLY'); $id = trim($_POST['id'] ?? '');
        try { $count = (new DefectModel())->delete($id); if ($count) { Auth::log(Auth::user()['MaTaiKhoan'],'XOA_PHIEU_HANG_LOI','phieu_hang_loi',$id,'Xóa phiếu'); $this->msg('success','Đã xóa phiếu.'); } else { $this->msg('error','Dữ liệu phiếu không tồn tại.'); } }
        catch (Throwable $e) { error_log($e->getMessage()); $this->msg('error','Không thể xóa phiếu.'); }
        $this->go('/defects');
    }

    public function status(): void
    {
        Auth::allow('HANGLOI_QUANLY'); $id = trim($_POST['id'] ?? ''); $status = $_POST['status'] ?? '';
        if (!in_array($status,['CHO_DUYET','DANG_XU_LY','DA_XU_LY','TU_CHOI'],true)) { $this->msg('error','Trạng thái không hợp lệ.'); $this->go('/defects'); }
        try { $count = (new DefectModel())->updateStatus($id,$status); if ($count) { Auth::log(Auth::user()['MaTaiKhoan'],'CAP_NHAT_TRANG_THAI','phieu_hang_loi',$id,$status); $this->msg('success','Đã cập nhật trạng thái.'); } else { $this->msg('error','Không tìm thấy phiếu hoặc trạng thái không thay đổi.'); } }
        catch (Throwable $e) { error_log($e->getMessage()); $this->msg('error','Không thể cập nhật trạng thái.'); }
        $this->go('/defects');
    }
}

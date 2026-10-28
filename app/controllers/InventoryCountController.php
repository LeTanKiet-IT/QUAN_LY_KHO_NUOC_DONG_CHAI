<?php
/** UC22 - Hoi dong kiem ke */
class InventoryCountController extends BaseController
{
    private function authorize(): array
    {
        Auth::allow('KIEMKE_LAP');
        return Auth::user() ?? [];
    }

    private function csrfToken(): string
    {
        if (empty($_SESSION['uc22_csrf'])) {
            $_SESSION['uc22_csrf']=bin2hex(random_bytes(32));
        }
        return $_SESSION['uc22_csrf'];
    }

    private function verifyCsrf(): void
    {
        if (!isset($_POST['csrf']) || !is_string($_POST['csrf']) ||
            !hash_equals($this->csrfToken(),$_POST['csrf'])) {
            http_response_code(403);
            exit('403 - Phiên gửi biểu mẫu không hợp lệ. Hãy tải lại trang.');
        }
    }

    public function index(): void
    {
        $this->authorize();
        try {
            $model=new InventoryCountModel();
            $plans=$model->plans();
            $selectedPlan=(string)($_GET['plan'] ?? '');
            $warehouses=$selectedPlan ? $model->warehousesForPlan($selectedPlan) : [];
            $records=$model->listRecords();
            $csrf=$this->csrfToken();
            $title='Biên bản kiểm kê kho';
            $this->render('inventory_counts/list',compact('plans','selectedPlan','warehouses','records','csrf','title'));
        } catch(Throwable $e) {
            error_log('[UC22] '.$e->getMessage());
            http_response_code(500);
            exit('Không tải được dữ liệu kiểm kê. Kiểm tra migration UC22.');
        }
    }

    public function create(): void
    {
        $user=$this->authorize();
        $planId=trim((string)($_GET['plan']??''));
        $warehouseId=trim((string)($_GET['warehouse']??''));
        if ($planId==='' || $warehouseId==='') {
            $this->go('/inventory-counts');
        }
        try {
            $model=new InventoryCountModel();
            if (!$model->validScope($planId,$warehouseId)) {
                http_response_code(400);
                exit('Kế hoạch/kho không hợp lệ hoặc chưa được duyệt.');
            }
            $existing=$model->recordByScope($planId,$warehouseId);
            if ($existing && $existing['TrangThai']!=='NHAP') {
                $this->go('/inventory-counts/detail?id='.urlencode($existing['MaBienBan']));
            }
            $isAdmin=($user['TenVaiTro']??'')==='Admin';
            if ($existing && !$isAdmin && $existing['NguoiLap']!==($user['MaTaiKhoan']??'')) {
                http_response_code(403);
                exit('Biên bản nháp do tài khoản khác lập.');
            }
            $warehouses=$model->warehousesForPlan($planId);
            $warehouseName=$warehouseId;
            foreach($warehouses as $w) {
                if($w['MaKho']===$warehouseId) $warehouseName=$w['TenKho'];
            }
            $rows=$model->stockRows($warehouseId);
            $draftRows=[];
            $oldForm=$_SESSION['uc22_oldform']??null;
            unset($_SESSION['uc22_oldform']);
            if ($existing) {
                foreach($model->detailRows($existing['MaBienBan']) as $row) {
                    $draftRows[$row['MaHang']]=$row;
                }
            }
            if (is_array($oldForm) && ($oldForm['plan']??null)===$planId &&
                ($oldForm['warehouse']??null)===$warehouseId) {
                foreach ($rows as $row) {
                    $code=(string)$row['MaHang'];
                    $draftRows[$code]['SoLuongThucTe']=$oldForm['counts'][$code]??'';
                    $draftRows[$code]['TinhTrangThucTe']=$oldForm['conditions'][$code]??'';
                    $draftRows[$code]['GhiChu']=$oldForm['notes'][$code]??'';
                }
            }
            $csrf=$this->csrfToken();
            $title='Lập biên bản kiểm kê';
            $this->render('inventory_counts/form',compact('planId','warehouseId','warehouseName','rows','draftRows','existing','csrf','title'));
        } catch(Throwable $e) {
            error_log('[UC22] '.$e->getMessage());
            http_response_code(500);
            exit('Không thể mở màn hình kiểm kê.');
        }
    }

    public function save(): void
    {
        $user=$this->authorize();
        $this->verifyCsrf();
        $planId=trim((string)($_POST['plan']??''));
        $warehouseId=trim((string)($_POST['warehouse']??''));
        $action=(string)($_POST['action']??'');
        $counts=$_POST['counts']??[];
        $conditions=$_POST['conditions']??[];
        $notes=$_POST['notes']??[];
        $snapshots=$_POST['snapshots']??[];
        if (!is_array($counts) || !is_array($conditions) || !is_array($notes) || !is_array($snapshots)) {
            http_response_code(400);
            exit('Biểu mẫu kiểm kê không đúng định dạng.');
        }
        try {
            $model=new InventoryCountModel();
            [$id,$status]=$model->save($planId,$warehouseId,
                (string)($user['MaTaiKhoan']??''),($user['TenVaiTro']??'')==='Admin',
                $counts,$conditions,$notes,$snapshots,$action);
            Auth::log($user['MaTaiKhoan']??null,
                $action==='confirm'?'LAP_BIEN_BAN_KIEM_KE':'LUU_NHAP_KIEM_KE',
                'bien_ban_kiem_ke',$id,'Kế hoạch '.$planId.'; kho '.$warehouseId);
            $this->msg('success',$action==='confirm'?'Lập biên bản kiểm kê thành công.':'Đã lưu tạm kết quả kiểm kê.');
            $this->go('/inventory-counts/detail?id='.urlencode($id));
        } catch(Throwable $e) {
            error_log('[UC22] '.$e->getMessage());
            $_SESSION['uc22_oldform']=[
                'plan'=>$planId,'warehouse'=>$warehouseId,
                'counts'=>$counts,'conditions'=>$conditions,'notes'=>$notes
            ];
            $this->msg('error','Không thể lưu biên bản: '.$e->getMessage());
            $this->go('/inventory-counts/create?plan='.urlencode($planId).'&warehouse='.urlencode($warehouseId));
        }
    }

    public function detail(): void
    {
        $this->authorize();
        $id=trim((string)($_GET['id']??''));
        $model=new InventoryCountModel();
        $record=$model->record($id);
        if (!$record) {
            http_response_code(404);
            exit('Không tìm thấy biên bản.');
        }
        $rows=$model->detailRows($id);
        $csrf=$this->csrfToken();
        $title='Chi tiết biên bản kiểm kê';
        $this->render('inventory_counts/detail',compact('record','rows','csrf','title'));
    }

    public function cancel(): void
    {
        $user=$this->authorize();
        $this->verifyCsrf();
        $id=trim((string)($_POST['id']??''));
        try {
            $model=new InventoryCountModel();
            $model->cancelDraft($id,(string)($user['MaTaiKhoan']??''),
                ($user['TenVaiTro']??'')==='Admin');
            Auth::log($user['MaTaiKhoan']??null,'HUY_BIEN_BAN_NHAP','bien_ban_kiem_ke',$id);
            $this->msg('success','Đã hủy biên bản nháp.');
        } catch(Throwable $e) {
            error_log('[UC22] '.$e->getMessage());
            $this->msg('error','Không thể hủy bản nháp.');
        }
        $this->go('/inventory-counts');
    }
}

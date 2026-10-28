<?php
class PlanController extends BaseController
{
    private const PER_PAGE = 10;

    public function index(): void
    {
        Auth::allow('KEHOACH_XEM');
        $q = trim($_GET['q'] ?? '');
        $status = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $res = (new PlanModel())->search($q, $status, $page, self::PER_PAGE);
        $items = $res['rows'];
        $pages = max(1, (int)ceil($res['total'] / self::PER_PAGE));
        $total = $res['total'];
        $title = 'Kế hoạch nguyên liệu';
        $this->render('plans/index', compact('title', 'items', 'q', 'status', 'page', 'pages', 'total'));
    }

    public function create(): void
    {
        Auth::allow('KEHOACH_QUANLY');
        $this->form(null);
    }

    public function edit(): void
    {
        Auth::allow('KEHOACH_QUANLY');
        $this->form(trim($_GET['id'] ?? ''));
    }

    public function detail(): void
    {
        Auth::allow('KEHOACH_XEM');
        $model = new PlanModel();
        $plan = $model->find(trim($_GET['id'] ?? ''));
        if (!$plan) { $this->msg('error', 'Kế hoạch không tồn tại.'); $this->go('/plans'); }
        $lines = $model->items($plan['MaKeHoach']);
        $title = 'Chi tiết kế hoạch ' . $plan['MaKeHoach'];
        $this->render('plans/detail', compact('title', 'plan', 'lines'));
    }

    public function save(): void
    {
        Auth::allow('KEHOACH_QUANLY');
        $model = new PlanModel();
        $id = trim($_POST['id'] ?? '');
        $isEdit = $id !== '';

        if ($isEdit) {
            $plan = $model->find($id);
            if (!$plan) { $this->msg('error', 'Kế hoạch không tồn tại.'); $this->go('/plans'); }
            if (!in_array($plan['TrangThai'], PlanModel::EDITABLE, true)) {
                $this->msg('error', 'Kế hoạch ở trạng thái hiện tại không được chỉnh sửa.'); $this->go('/plans');
            }
        }

        $date = $_POST['date'] ?? '';
        $note = trim($_POST['note'] ?? '');
        $codes = $_POST['code'] ?? []; $qtys = $_POST['qty'] ?? []; $notes = $_POST['line_note'] ?? [];
        $back = $isEdit ? '/plans/edit?id=' . urlencode($id) : '/plans/create';
        $err = null;

        $d = DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) { $err = 'Thời gian sử dụng không hợp lệ.'; }
        elseif ($date < date('Y-m-d')) { $err = 'Thời gian sử dụng không được nhỏ hơn ngày hôm nay.'; }
        elseif (mb_strlen($note) > 500) { $err = 'Ghi chú tối đa 500 ký tự.'; }

        $valid = array_flip($model->validMaterialCodes());
        $lines = []; $seen = [];
        if (!$err && (!is_array($codes) || count($codes) === 0)) { $err = 'Vui lòng thêm ít nhất một nguyên liệu.'; }
        if (!$err) {
            foreach ($codes as $i => $code) {
                $code = trim((string)$code); $qty = $qtys[$i] ?? '';
                if (!isset($valid[$code])) { $err = 'Có nguyên liệu không hợp lệ.'; break; }
                if (isset($seen[$code])) { $err = 'Nguyên liệu ' . $code . ' bị trùng trong kế hoạch.'; break; }
                if (!is_numeric($qty) || (float)$qty <= 0) { $err = 'Số lượng của ' . $code . ' phải lớn hơn 0.'; break; }
                $seen[$code] = true;
                $lines[] = ['code' => $code, 'qty' => round((float)$qty, 2), 'note' => mb_substr(trim((string)($notes[$i] ?? '')), 0, 300)];
            }
        }
        if ($err) { $this->msg('error', $err); $this->go($back); }

        try {
            $user = Auth::user();
            $savedId = $model->save(['date' => $date, 'note' => $note], $lines, $isEdit ? $id : null, $user['MaTaiKhoan']);
            Auth::log($user['MaTaiKhoan'], $isEdit ? 'CAP_NHAT_KE_HOACH' : 'TAO_KE_HOACH', 'ke_hoach_nguyen_lieu', $savedId, 'Chuyển trạng thái CHO_PHE_DUYET');
            $this->msg('success', 'Đã lưu kế hoạch ' . $savedId . ' và gửi chờ phê duyệt.');
            $this->go('/plans/detail?id=' . urlencode($savedId));
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $this->msg('error', 'Không lưu được kế hoạch. Vui lòng thử lại.');
            $this->go($back);
        }
    }

    private function form(?string $id): void
    {
        $model = new PlanModel();
        $plan = null; $lines = [];
        if ($id !== null) {
            $plan = $model->find($id);
            if (!$plan) { $this->msg('error', 'Kế hoạch không tồn tại.'); $this->go('/plans'); }
            if (!in_array($plan['TrangThai'], PlanModel::EDITABLE, true)) {
                $this->msg('error', 'Chỉ sửa được kế hoạch ở trạng thái Nháp hoặc Chờ chỉnh sửa.'); $this->go('/plans');
            }
            $lines = $model->items($id);
        }
        $materials = $model->materials();
        $title = $plan ? 'Chỉnh sửa kế hoạch ' . $plan['MaKeHoach'] : 'Lập kế hoạch nguyên liệu';
        $this->render('plans/form', compact('title', 'plan', 'lines', 'materials'));
    }
}

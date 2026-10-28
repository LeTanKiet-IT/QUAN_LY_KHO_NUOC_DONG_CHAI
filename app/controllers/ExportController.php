<?php
/** FR17 - Lập phiếu xuất kho thực tế (không dùng dữ liệu demo). */
class ExportController extends BaseController
{
    public function index(): void
    {
        Auth::allow('XUATKHO_QUANLY');
        if (empty($_SESSION['csrf_export'])) { $_SESSION['csrf_export'] = bin2hex(random_bytes(32)); }
        try {
            $model = new ExportModel();
            $user = Auth::user();
            $isAdmin = ($user['MaVaiTro'] ?? '') === 'ADMIN';
            $requests = $model->requests($user['MaTaiKhoan'], $isAdmin);
            $requestId = trim((string)($_GET['request'] ?? ''));
            $selected = $requestId === '' ? null : $model->request($requestId, false, $user['MaTaiKhoan'], $isAdmin);
            if ($requestId !== '' && !$selected) {
                $this->msg('error', 'Không tìm thấy yêu cầu xuất kho.');
                $this->go('/exports');
            }
            $lots = $selected ? $model->lots($selected['MaKho'], $selected['MaHang']) : [];
            $stock = $selected ? $model->stock($selected['MaKho'], $selected['MaHang']) : '0.00';
            $history = $model->history($user['MaTaiKhoan'], $isAdmin);
            $title = 'Lập phiếu xuất kho';
            $this->render('exports/index', compact('title', 'requests', 'selected', 'lots', 'stock', 'history'));
        } catch (Throwable $e) {
            error_log('FR17 index: ' . $e->getMessage());
            http_response_code(500);
            exit('Không thể mở Lập phiếu xuất kho. Kiểm tra bản sao lưu và migration FR17.');
        }
    }

    public function save(): void
    {
        Auth::allow('XUATKHO_QUANLY');
        $expected = $_SESSION['csrf_export'] ?? '';
        if (!is_string($expected) || $expected === '' ||
            !hash_equals($expected, (string)($_POST['csrf_token'] ?? ''))) {
            http_response_code(403);
            exit('Phiên biểu mẫu không hợp lệ. Tải lại trang và thử lại.');
        }
        $requestId = trim((string)($_POST['request_id'] ?? ''));
        $lotId = trim((string)($_POST['lot_id'] ?? ''));
        $quantity = trim((string)($_POST['quantity'] ?? ''));
        $recipient = trim((string)($_POST['recipient'] ?? ''));
        if ($requestId === '') { $this->msg('error', 'Bạn chưa chọn yêu cầu xuất kho.'); $this->go('/exports'); }
        try {
            $user = Auth::user();
            $id = (new ExportModel())->issue(
                $requestId, $lotId, $quantity, $recipient, $user['MaTaiKhoan'],
                $expected, ($user['MaVaiTro'] ?? '') === 'ADMIN'
            );
            // Rotate token to make accidental resubmission from the same form fail.
            $_SESSION['csrf_export'] = bin2hex(random_bytes(32));
            $this->msg('success', 'Lập phiếu xuất kho ' . $id . ' thành công và đã cập nhật tồn kho.');
            $this->go('/exports/detail?id=' . rawurlencode($id));
        } catch (InvalidArgumentException | RuntimeException $e) {
            $this->msg('error', $e->getMessage());
        } catch (Throwable $e) {
            error_log('FR17 save: ' . $e->getMessage());
            $this->msg('error', 'Không thể lưu phiếu. Dữ liệu chưa thay đổi.');
        }
        $this->go('/exports?request=' . rawurlencode($requestId));
    }

    public function detail(): void
    {
        Auth::allow('XUATKHO_QUANLY');
        $id = trim((string)($_GET['id'] ?? ''));
        if ($id === '') { http_response_code(400); exit('Thiếu mã phiếu xuất kho.'); }
        try {
            $model = new ExportModel();
            $user = Auth::user();
            $export = $model->detail($id, $user['MaTaiKhoan'], ($user['MaVaiTro'] ?? '') === 'ADMIN');
            if (!$export) { http_response_code(404); exit('Không tìm thấy phiếu xuất kho.'); }
            $lines = $model->detailLines($id);
            $title = 'Chi tiết phiếu xuất ' . $id;
            $this->render('exports/detail', compact('title', 'export', 'lines'));
        } catch (Throwable $e) {
            error_log('FR17 detail: ' . $e->getMessage());
            http_response_code(500);
            exit('Không thể tải chi tiết phiếu xuất.');
        }
    }
}

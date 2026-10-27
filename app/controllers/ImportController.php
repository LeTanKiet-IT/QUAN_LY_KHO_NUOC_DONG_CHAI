<?php

class ImportController extends BaseController
{
    /** Danh sách các phiếu nhập kho đã tạo. */
   
    public function index(): void
    {
        Auth::allow('NHAPKHO_QUANLY');

        // Doc va kiem tra cac bo loc tu URL.
        $q = trim($_GET['q'] ?? '');
        $type = $_GET['type'] ?? '';
        $status = $_GET['status'] ?? '';
        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');

        if (!in_array($type, ['', 'NGUYEN_LIEU', 'THANH_PHAM'], true)) {
            $type = '';
        }

        if (!in_array($status, ['', 'DA_NHAP'], true)) {
            $status = '';
        }

        // Chi chap nhan ngay theo dinh dang YYYY-MM-DD.
        foreach (['dateFrom', 'dateTo'] as $dateField) {
            if ($$dateField !== '') {
                $date = DateTime::createFromFormat('!Y-m-d', $$dateField);
                if (!$date || $date->format('Y-m-d') !== $$dateField) {
                    $$dateField = '';
                }
            }
        }

        // Neu nguoi dung chon khoang ngay nguoc, bo loc ngay de tranh ket qua kho hieu.
        if ($dateFrom !== '' && $dateTo !== '' && $dateFrom > $dateTo) {
            $dateFrom = '';
            $dateTo = '';
        }

        $filters = [
            'q' => $q,
            'type' => $type,
            'status' => $status,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        try {
            $model = new ImportModel();
            $imports = $model->getFilteredImportHistory($filters);
            $title = 'Danh sách phiếu nhập kho';

            $this->render('imports/list', compact(
                'title', 'imports', 'q', 'type', 'status', 'dateFrom', 'dateTo'
            ));
        } catch (Throwable $e) {
            error_log($e->getMessage());
            http_response_code(500);
            exit('Không thể tải danh sách phiếu nhập kho.');
        }
    }


    /** Form lập phiếu nhập kho. */
    public function create(): void
    {
        Auth::allow('NHAPKHO_QUANLY');

        $model = new ImportModel();
        $type = $_GET['type'] ?? 'NGUYEN_LIEU';

        if (!in_array($type, ['NGUYEN_LIEU', 'THANH_PHAM'], true)) {
            $type = 'NGUYEN_LIEU';
        }

        try {
            $warehouses = $model->getActiveWarehouses();
            $sources = $model->getEligibleSources($type);
            $sourceId = trim($_GET['source'] ?? '');
            $details = [];

            if ($sourceId !== '') {
                foreach ($sources as $source) {
                    if ($source['MaChungTu'] === $sourceId) {
                        $details = $model->getSourceDetails($sourceId);
                        break;
                    }
                }
            }

            $title = 'Lập phiếu nhập kho';
            $this->render('imports/index', compact(
                'title', 'type', 'warehouses', 'sources', 'sourceId', 'details'
            ));
        } catch (Throwable $e) {
            error_log($e->getMessage());
            http_response_code(500);
            exit('Không thể tải dữ liệu lập phiếu nhập kho.');
        }
    }

    /** Xem chi tiết một phiếu nhập kho. */
    public function detail(): void
    {
        Auth::allow('NHAPKHO_QUANLY');

        $importId = trim($_GET['id'] ?? '');
        if ($importId === '') {
            http_response_code(400);
            exit('Thiếu mã phiếu nhập kho.');
        }

        try {
            $model = new ImportModel();
            $import = $model->getImportById($importId);

            if (!$import) {
                http_response_code(404);
                exit('Không tìm thấy phiếu nhập kho.');
            }

            $details = $model->getImportDetails($importId);
            $title = 'Chi tiết phiếu nhập kho';

            $this->render('imports/detail', compact(
                'title',
                'import',
                'details'
            ));
        } catch (Throwable $e) {
            error_log($e->getMessage());
            http_response_code(500);
            exit('Không thể tải chi tiết phiếu nhập kho.');
        }
    }

    /** Lưu phiếu nhập kho. */
    public function save(): void
    {
        Auth::allow('NHAPKHO_QUANLY');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Phương thức không được hỗ trợ.');
        }

        $sourceId = trim($_POST['source'] ?? '');
        $warehouseId = trim($_POST['warehouse'] ?? '');
        $quantities = $_POST['quantities'] ?? [];
        $type = $_POST['type'] ?? 'NGUYEN_LIEU';

        if (!is_array($quantities)) {
            http_response_code(400);
            exit('Dữ liệu số lượng không hợp lệ.');
        }

        if (!in_array($type, ['NGUYEN_LIEU', 'THANH_PHAM'], true)) {
            $type = 'NGUYEN_LIEU';
        }

        $user = Auth::user();
        $userId = $user['MaTaiKhoan'] ?? '';

        if ($userId === '') {
            http_response_code(401);
            exit('Không xác định được tài khoản đăng nhập.');
        }

        try {
            $model = new ImportModel();
            $importId = $model->saveImport(
                $sourceId, $warehouseId, $userId, $quantities
            );

            header('Location: ' . BASE_URL . '/imports?success=' . urlencode($importId));
            exit;
        } catch (Throwable $e) {
            error_log($e->getMessage());
            header(
                'Location: ' . BASE_URL . '/imports/create?type=' . urlencode($type)
                . '&source=' . urlencode($sourceId)
                . '&error=' . urlencode($e->getMessage())
            );
            exit;
        }
    }
}

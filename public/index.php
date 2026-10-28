<?php

// ==================================================
// 1. KHỞI TẠO SESSION VÀ NẠP CẤU HÌNH
// ==================================================

session_start();

require_once __DIR__ . '/../app/config/config.php';


// ==================================================
// 2. NẠP CÁC FILE CORE, MODEL VÀ CONTROLLER
// ==================================================

foreach (glob(__DIR__ . '/../app/core/*.php') as $f) {
    require_once $f;
}

foreach (glob(__DIR__ . '/../app/models/*.php') as $f) {
    require_once $f;
}

foreach (glob(__DIR__ . '/../app/controllers/*.php') as $f) {
    require_once $f;
}


// ==================================================
// 3. XÁC ĐỊNH ĐƯỜNG DẪN VÀ HTTP METHOD
// ==================================================

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

$base = rtrim(BASE_URL, '/');

// Loại bỏ BASE_URL khỏi đường dẫn nếu có
if (str_starts_with($path, $base)) {
    $path = substr($path, strlen($base));
}

// Chuẩn hóa đường dẫn
$path = '/' . trim($path, '/');

// Tạo khóa định tuyến, ví dụ: GET /login
$key = $_SERVER['REQUEST_METHOD'] . ' ' . $path;


// ==================================================
// 4. KHAI BÁO ROUTE
// ==================================================

$routes = [

    // Trang chủ
    'GET /' => [
        \DashboardController::class,
        'index'
    ],

    // Đăng nhập
    'GET /login' => [
        \AuthController::class,
        'form'
    ],

    'POST /login' => [
        \AuthController::class,
        'login'
    ],

    // Đăng xuất
    'POST /logout' => [
        \AuthController::class,
        'logout'
    ],

    // Quản lý tài khoản
    'GET /accounts' => [
        \AccountController::class,
        'index'
    ],

    'POST /accounts/save' => [
        \AccountController::class,
        'save'
    ],

    'POST /accounts/delete' => [
        \AccountController::class,
        'delete'
    ],

    // Quản lý hàng lỗi / trả về
    'GET /defects' => [
        \DefectController::class,
        'index'
    ],

    'POST /defects/save' => [
        \DefectController::class,
        'save'
    ],

    'POST /defects/delete' => [
        \DefectController::class,
        'delete'
    ],

    'POST /defects/status' => [
        \DefectController::class,
        'status'
    ],
    
    // Quản lý nhập kho
    'GET /imports' => [
        ImportController::class,
        'index'
    ],

    'GET /imports/create' => [
        ImportController::class,
        'create'
    ],

    'POST /imports/save' => [
        ImportController::class,
        'save'
    ],
    'GET /imports/detail' => [ImportController::class, 'detail'],

    // FR17 - Lap phieu xuat kho - My Oanh
    'GET /exports' => [
        ExportController::class,
        'index'
    ],

];




// ==================================================
// 5. KIỂM TRA ROUTE VÀ ĐIỀU PHỐI REQUEST
// ==================================================

if (!isset($routes[$key])) {
    http_response_code(404);
    exit('404 - Không tìm thấy trang');
}

// Lấy controller và phương thức cần thực thi
[$c, $m] = $routes[$key];

// Khởi tạo controller và gọi phương thức
(new $c())->$m();


<div class="page-heading">
    <div>
        <h1>Quản lý hàng lỗi / trả về</h1>
        <p>
            Theo dõi, ghi nhận lý do và hình thức xử lý
            hàng hóa không đạt chất lượng.
        </p>
    </div>

    <a class="btn btn-primary" href="#defect-form">
        ＋ Tạo phiếu trả về
    </a>
</div>

<div class="split">

    <!-- DANH SÁCH PHIẾU HÀNG LỖI -->
    <section class="panel">

        <!-- TÌM KIẾM VÀ LỌC -->
        <form class="filters" method="get">

            <div class="field search">
                <label for="search">Tìm kiếm</label>

                <input
                    class="input"
                    id="search"
                    type="text"
                    name="q"
                    value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Mã phiếu, mã hàng, tên hàng, nhà cung cấp..."
                >
            </div>

            <div class="field">
                <label for="type">Loại hàng</label>

                <select class="select" id="type" name="type">
                    <option value="">Tất cả</option>

                    <option
                        value="NGUYEN_LIEU"
                        <?= $type === 'NGUYEN_LIEU' ? 'selected' : '' ?>
                    >
                        Nguyên liệu
                    </option>

                    <option
                        value="THANH_PHAM"
                        <?= $type === 'THANH_PHAM' ? 'selected' : '' ?>
                    >
                        Thành phẩm
                    </option>
                </select>
            </div>

            <div class="field">
                <label for="status">Trạng thái</label>

                <select class="select" id="status" name="status">
                    <option value="">Tất cả</option>

                    <?php
                    $statusOptions = [
                        'CHO_DUYET'  => 'Chờ duyệt',
                        'DANG_XU_LY' => 'Đang xử lý',
                        'DA_XU_LY'   => 'Đã xử lý',
                        'TU_CHOI'    => 'Từ chối'
                    ];
                    ?>

                    <?php foreach ($statusOptions as $key => $value): ?>
                        <option
                            value="<?= $key ?>"
                            <?= $status === $key ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button
                type="submit"
                class="btn btn-primary"
                style="align-self: end;"
            >
                Tìm kiếm
            </button>

        </form>

        <!-- BẢNG DANH SÁCH -->
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Mã phiếu</th>
                        <th>Ngày lập</th>
                        <th>Loại hàng</th>
                        <th>Mã hàng / Tên hàng</th>
                        <th>Số lượng</th>
                        <th>Lý do</th>
                        <th>Hướng xử lý</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (empty($items)): ?>
                        <tr>
                            <td
                                colspan="9"
                                style="text-align: center; padding: 35px;"
                            >
                                Chưa có phiếu phù hợp.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($items as $it): ?>
                        <?php
                        $handlingOptions = [
                            'TIEU_HUY' => 'Tiêu hủy',
                            'TRA_NCC'  => 'Trả NCC',
                            'TAI_CHE'  => 'Tái chế'
                        ];

                        $statusLabels = [
                            'CHO_DUYET'  => 'Chờ duyệt',
                            'DANG_XU_LY' => 'Đang xử lý',
                            'DA_XU_LY'   => 'Đã xử lý',
                            'TU_CHOI'    => 'Từ chối'
                        ];

                        $statusColors = [
                            'CHO_DUYET'  => 'blue',
                            'DANG_XU_LY' => 'amber',
                            'DA_XU_LY'   => 'green',
                            'TU_CHOI'    => 'red'
                        ];

                        $currentStatus = $it['TrangThai'];
                        ?>

                        <tr>
                            <td>
                                <b>
                                    <?= htmlspecialchars(
                                        $it['MaPhieu'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </b>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $it['NgayLap'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <td>
                                <?= $it['LoaiHang'] === 'NGUYEN_LIEU'
                                    ? 'Nguyên liệu'
                                    : 'Thành phẩm' ?>
                            </td>

                            <td>
                                <b>
                                    <?= htmlspecialchars(
                                        $it['MaHang'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </b>
                                <br>

                                <?= htmlspecialchars(
                                    $it['TenHang'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <td>
                                <?= number_format(
                                    (float) $it['SoLuong'],
                                    2
                                ) ?>

                                <?= htmlspecialchars(
                                    $it['DonViTinh'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $it['LyDo'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $handlingOptions[$it['HuongXuLy']]
                                        ?? $it['HuongXuLy'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <td>
                                <span class="badge <?= $statusColors[$currentStatus] ?? 'gray' ?>">
                                    <?= htmlspecialchars(
                                        $statusLabels[$currentStatus]
                                            ?? $currentStatus,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </span>

                                <!-- CẬP NHẬT TRẠNG THÁI -->
                                <form
                                    method="post"
                                    action="<?= BASE_URL ?>/defects/status"
                                    style="margin-top: 7px;"
                                    onsubmit="return confirm('Bạn có muốn cập nhật trạng thái phiếu?')"
                                >
                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= htmlspecialchars(
                                            $it['MaPhieu'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                    >

                                    <select
                                        class="select"
                                        name="status"
                                        aria-label="Cập nhật trạng thái"
                                        required
                                        style="padding: 5px; font-size: 10px;"
                                    >
                                        <option value="">
                                            Chọn trạng thái
                                        </option>

                                        <?php foreach ($statusOptions as $key => $value): ?>
                                            <option
                                                value="<?= $key ?>"
                                                <?= $currentStatus === $key ? 'disabled' : '' ?>
                                            >
                                                <?= htmlspecialchars(
                                                    $value,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <button
                                        type="submit"
                                        class="btn btn-light btn-sm"
                                        style="margin-top: 5px;"
                                    >
                                        Cập nhật
                                    </button>
                                </form>
                            </td>

                            <td>
                                <div class="actions">

                                    <a
                                        class="icon-btn"
                                        href="<?= BASE_URL ?>/defects?edit=<?= urlencode($it['MaPhieu']) ?>#defect-form"
                                        title="Sửa phiếu"
                                        aria-label="Sửa phiếu"
                                    >
                                        ✎
                                    </a>

                                    <form
                                        method="post"
                                        action="<?= BASE_URL ?>/defects/delete"
                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa phiếu này?')"
                                    >
                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= htmlspecialchars(
                                                $it['MaPhieu'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="icon-btn"
                                            title="Xóa phiếu"
                                            aria-label="Xóa phiếu"
                                        >
                                            ⌫
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>

    </section>

    <!-- FORM TẠO / CẬP NHẬT PHIẾU -->
    <section class="panel" id="defect-form">

        <div class="panel-title">
            <h2>
                <?= $editing
                    ? 'Cập nhật phiếu'
                    : 'Tạo phiếu hàng lỗi / trả về' ?>
            </h2>
        </div>

        <form
            method="post"
            action="<?= BASE_URL ?>/defects/save"
        >

            <input
                type="hidden"
                name="id"
                value="<?= htmlspecialchars(
                    $editing['MaPhieu'] ?? '',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >

            <div class="form-grid">

                <div class="field">
                    <label for="date">Ngày lập *</label>

                    <input
                        class="input"
                        id="date"
                        type="date"
                        name="date"
                        value="<?= htmlspecialchars(
                            $editing['NgayLap'] ?? date('Y-m-d'),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        required
                    >
                </div>

                <div class="field">
                    <label for="product-type">Loại hàng *</label>

                    <select
                        class="select"
                        id="product-type"
                        name="type"
                        required
                    >
                        <option
                            value="NGUYEN_LIEU"
                            <?= ($editing['LoaiHang'] ?? '') === 'NGUYEN_LIEU'
                                ? 'selected'
                                : '' ?>
                        >
                            Nguyên liệu
                        </option>

                        <option
                            value="THANH_PHAM"
                            <?= ($editing['LoaiHang'] ?? '') === 'THANH_PHAM'
                                ? 'selected'
                                : '' ?>
                        >
                            Thành phẩm
                        </option>
                    </select>
                </div>

                <div class="field">
                    <label for="code">Mã hàng *</label>

                    <input
                        class="input"
                        id="code"
                        name="code"
                        value="<?= htmlspecialchars(
                            $editing['MaHang'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        placeholder="VD: NL002"
                        required
                    >
                </div>

                <div class="field">
                    <label for="product">Tên hàng *</label>

                    <input
                        class="input"
                        id="product"
                        name="product"
                        value="<?= htmlspecialchars(
                            $editing['TenHang'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        placeholder="Tên hàng hóa"
                        required
                    >
                </div>

                <div class="field">
                    <label for="qty">Số lượng *</label>

                    <input
                        class="input"
                        id="qty"
                        type="number"
                        min="0.01"
                        step="0.01"
                        name="qty"
                        value="<?= htmlspecialchars(
                            (string) ($editing['SoLuong'] ?? ''),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        required
                    >
                </div>

                <div class="field">
                    <label for="unit">Đơn vị tính *</label>

                    <input
                        class="input"
                        id="unit"
                        name="unit"
                        value="<?= htmlspecialchars(
                            $editing['DonViTinh'] ?? 'Cái',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        required
                    >
                </div>

                <div class="field span2">
                    <label for="reason">Lý do *</label>

                    <select
                        class="select"
                        id="reason"
                        name="reason"
                        required
                    >
                        <?php
                        $reasons = [
                            'Bị móp',
                            'Nứt, vỡ',
                            'Rò rỉ nắp',
                            'Sai màu',
                            'Hạn dùng ngắn',
                            'Không đạt chất lượng',
                            'Khác'
                        ];
                        ?>

                        <?php foreach ($reasons as $reason): ?>
                            <option
                                value="<?= htmlspecialchars(
                                    $reason,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                <?= ($editing['LyDo'] ?? '') === $reason
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= htmlspecialchars(
                                    $reason,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field span2">
                    <label for="action">Hướng xử lý *</label>

                    <select
                        class="select"
                        id="action"
                        name="action"
                        required
                    >
                        <option
                            value="TIEU_HUY"
                            <?= ($editing['HuongXuLy'] ?? '') === 'TIEU_HUY'
                                ? 'selected'
                                : '' ?>
                        >
                            Tiêu hủy
                        </option>

                        <option
                            value="TRA_NCC"
                            <?= ($editing['HuongXuLy'] ?? '') === 'TRA_NCC'
                                ? 'selected'
                                : '' ?>
                        >
                            Trả nhà cung cấp
                        </option>

                        <option
                            value="TAI_CHE"
                            <?= ($editing['HuongXuLy'] ?? '') === 'TAI_CHE'
                                ? 'selected'
                                : '' ?>
                        >
                            Tái chế
                        </option>
                    </select>
                </div>

                <div class="field span2">
                    <label for="supplier">Nhà cung cấp</label>

                    <input
                        class="input"
                        id="supplier"
                        name="supplier"
                        value="<?= htmlspecialchars(
                            $editing['NhaCungCap'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        placeholder="Bắt buộc nếu chọn trả nhà cung cấp"
                    >
                </div>

                <div class="field span2">
                    <label for="form-status">Trạng thái *</label>

                    <select
                        class="select"
                        id="form-status"
                        name="status"
                        required
                    >
                        <?php foreach ($statusOptions as $key => $value): ?>
                            <option
                                value="<?= $key ?>"
                                <?= ($editing['TrangThai'] ?? 'CHO_DUYET') === $key
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= htmlspecialchars(
                                    $value,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field span2">
                    <label for="note">Ghi chú</label>

                    <textarea
                        class="textarea"
                        id="note"
                        name="note"
                        rows="3"
                        placeholder="Thông tin bổ sung..."
                    ><?= htmlspecialchars(
                        $editing['GhiChu'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?></textarea>
                </div>

            </div>

            <div class="form-actions">

                <a
                    class="btn btn-light"
                    href="<?= BASE_URL ?>/defects"
                >
                    Hủy
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <?= $editing ? 'Cập nhật phiếu' : 'Lưu phiếu' ?>
                </button>

            </div>

        </form>

    </section>

</div>


<div class="page-heading">
    <div>
        <h1>Lập phiếu nhập kho</h1>
        <p>Chọn chứng từ nguồn, kho tiếp nhận và số lượng thực nhận.</p>
    </div>
</div>

<div style="margin-bottom:18px">
    <a class="btn btn-secondary" href="<?= BASE_URL ?>/imports">
        &larr; Quay lại danh sách phiếu nhập kho
    </a>
</div>

<?php if (!empty($_GET['error'])): ?>
    <div class="import-alert import-alert-error" role="alert">
        <div class="import-alert-icon">
            <i class="bi bi-exclamation-triangle"></i>
        </div>

        <div class="import-alert-content">
            <div class="import-alert-title">
                Không thể nhập kho
            </div>

            <div class="import-alert-message">
                <?= htmlspecialchars(
                    $_GET['error'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>
        </div>

        <button type="button"
                class="import-alert-close"
                onclick="this.parentElement.remove()"
                aria-label="Đóng thông báo">
            &times;
        </button>
    </div>
<?php endif; ?>

    <!-- 1. Chọn loại nhập -->
    <form method="get" action="<?= BASE_URL ?>/imports/create">
            <div class="field">
            <label for="type">Loại nhập kho</label>

            <select
                class="select"
                id="type"
                name="type"
                onchange="this.form.submit()"
            >
                <option
                    value="NGUYEN_LIEU"
                    <?= $type === 'NGUYEN_LIEU' ? 'selected' : '' ?>
                >
                    Nhập nguyên liệu
                </option>

                <option
                    value="THANH_PHAM"
                    <?= $type === 'THANH_PHAM' ? 'selected' : '' ?>
                >
                    Nhập thành phẩm
                </option>
            </select>
        </div>
    </form>

    <!-- 2. Chọn chứng từ nguồn -->
    <form method="get" action="<?= BASE_URL ?>/imports/create">
        <input
            type="hidden"
            name="type"
            value="<?= htmlspecialchars(
                $type,
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >

        <div class="field">
            <label for="source">Chứng từ nguồn</label>

            <select
                class="select"
                id="source"
                name="source"
                required
            >
                <option value="">-- Chọn chứng từ --</option>

                <?php foreach ($sources as $source): ?>
                    <option
                        value="<?= htmlspecialchars(
                            $source['MaChungTu'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        <?= $sourceId === $source['MaChungTu']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= htmlspecialchars(
                            $source['MaChungTu'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                        -
                        <?= htmlspecialchars(
                            $source['NgayChungTu'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button class="btn btn-primary" type="submit">
            Xem hàng hóa
        </button>
    </form>

    <hr>

    <!-- 3. Kho tiếp nhận -->
    <h2>Kho tiếp nhận</h2>

    <?php if (empty($warehouses)): ?>

        <p>Chưa có kho đang hoạt động.</p>

    <?php else: ?>

        <!-- 4. Form xác nhận nhập kho -->
        <form
            method="post"
            action="<?= BASE_URL ?>/imports/save"
        >
            <input type="hidden" name="type" value="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>">
            <input
                type="hidden"
                name="source"
                value="<?= htmlspecialchars(
                    $sourceId,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >

            <div class="field">
                <label for="warehouse">Chọn kho</label>

                <select
                    class="select"
                    id="warehouse"
                    name="warehouse"
                    required
                >
                    <?php foreach ($warehouses as $warehouse): ?>
                        <option
                            value="<?= htmlspecialchars(
                                $warehouse['MaKho'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >
                            <?= htmlspecialchars($warehouse['TenKho'], ENT_QUOTES, 'UTF-8') ?>
                            - Sức chứa còn lại:
                            <?= number_format((float)$warehouse['SucChuaConLai'], 2) ?>
                            /
                            <?= number_format((float)$warehouse['SucChua'], 2) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <h2>Chi tiết hàng hóa</h2>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Mã hàng</th>
                            <th>Tên hàng</th>
                            <th>Đơn vị</th>
                            <th>Số lượng chứng từ</th>
                            <th>Đã nhập</th>
                            <th>Còn được nhập</th>
                            <th>Số lượng thực nhận</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($details)): ?>

                            <tr>
                                <td
                                    colspan="7"
                                    style="text-align:center"
                                >
                                    Chọn chứng từ rồi nhấn
                                    “Xem hàng hóa”.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($details as $item): ?>
                                <?php
                                    $maHang = (string)$item['MaHang'];
                                    $maChiTiet = (string)$item['MaChiTiet'];
                                    $soLuongConLai = (float)$item['SoLuongConLai'];
                                ?>

                                <tr>
                                    <td>
                                        <?= htmlspecialchars(
                                            $maHang,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $item['TenHang'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $item['DonViTinh'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= number_format(
                                            (float)$item['SoLuong'],
                                            2
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= number_format(
                                            (float)$item['SoLuongDaNhap'],
                                            2
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= number_format(
                                            $soLuongConLai,
                                            2
                                        ) ?>
                                    </td>

                                    <td>
                                        <input
                                            type="number"
                                            name="quantities[<?= htmlspecialchars($maChiTiet, ENT_QUOTES, 'UTF-8') ?>]"
                                            class="form-control quantity-input"
                                            min="0.01"
                                            max="<?= $soLuongConLai ?>"
                                            step="0.01"
                                            value=""
                                            data-max="<?= $soLuongConLai ?>"
                                            required
                                        >
                                        <div class="quantity-error text-danger small mt-1" hidden></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($details)): ?>
                <div style="margin-top:20px">
                    <button
                        class="btn btn-primary"
                        type="submit"
                        onclick="return confirm(
                            'Bạn có chắc chắn muốn xác nhận nhập kho?'
                        );"
                    >
                        Xác nhận nhập kho
                    </button>
                </div>
            <?php endif; ?>

        </form>

    <?php endif; ?>

    <p class="muted" style="margin-top:20px">
        Số lượng nhập sẽ được kiểm tra lại ở phía máy chủ
        trước khi lưu.
    </p>

</div>
<style>

/* Thông báo kết quả nhập kho */
.import-alert {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 16px 18px;
    margin-bottom: 22px;
    border: 1px solid transparent;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(15, 23, 42, 0.04);
    animation: importAlertIn 0.25s ease-out;
}

.import-alert-success {
    color: #166534;
    background: #f0fdf4;
    border-color: #bbf7d0;
}

.import-alert-error {
    color: #991b1b;
    background: #fef2f2;
    border-color: #fecaca;
}

.import-alert-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    font-size: 22px;
}

.import-alert-success .import-alert-icon {
    background: #dcfce7;
    color: #15803d;
}

.import-alert-error .import-alert-icon {
    background: #fee2e2;
    color: #b91c1c;
}

.import-alert-content {
    flex: 1;
    min-width: 0;
}

.import-alert-title {
    margin-bottom: 4px;
    font-size: 15px;
    font-weight: 700;
}

.import-alert-message {
    font-size: 14px;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

.import-alert-message strong {
    font-weight: 700;
}

.import-alert-close {
    flex-shrink: 0;
    padding: 0 4px;
    border: none;
    background: transparent;
    color: inherit;
    font-size: 25px;
    line-height: 1;
    opacity: 0.6;
    cursor: pointer;
}

.import-alert-close:hover {
    opacity: 1;
}

@keyframes importAlertIn {
    from {
        opacity: 0;
        transform: translateY(-6px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

</style>

<script>
document.querySelectorAll('.quantity-input').forEach(function (input) {
    const error = input.parentElement.querySelector('.quantity-error');

    function validateQuantity() {
        const value = input.value.trim();
        const max = Number(input.dataset.max);

        error.hidden = true;
        error.textContent = '';
        input.setCustomValidity('');

        if (value === '') {
            return;
        }

        const quantity = Number(value);

        if (!Number.isFinite(quantity) || quantity <= 0) {
            error.textContent = 'Số lượng thực nhận phải lớn hơn 0.';
            error.hidden = false;
            input.setCustomValidity('Số lượng thực nhận phải lớn hơn 0.');
        } else if (quantity > max) {
            error.textContent =
                'Số lượng thực nhận không được vượt quá ' + max + '.';
            error.hidden = false;
            input.setCustomValidity(
                'Số lượng thực nhận không được vượt quá ' + max + '.'
            );
        }
    }

    input.addEventListener('input', validateQuantity);
    input.addEventListener('invalid', validateQuantity);
});
</script>

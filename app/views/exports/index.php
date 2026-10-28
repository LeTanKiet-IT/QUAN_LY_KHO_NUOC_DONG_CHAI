
<div class="export-page">
    <h1>LẬP PHIẾU XUẤT KHO</h1>
    <p>Quản lý xuất kho - Nhóm Đom Đóm</p>

    <div class="export-card">
        <h2>Thông tin yêu cầu xuất kho</h2>

        <label>Mã yêu cầu xuất kho</label>
        <select id="yeuCau">
            <option value="">-- Chọn yêu cầu xuất --</option>
            <option value="YCX-DEMO-01">
                YCX-DEMO-01 (Dữ liệu demo)
            </option>
        </select>

        <label>Mã thành phẩm</label>
        <input value="TP001" readonly>

        <label>Tên thành phẩm</label>
        <input value="Chai nước đóng chai 500ml" readonly>

        <label>Kho xuất</label>
        <input value="Kho thành phẩm B" readonly>

        <label>Chứng từ liên quan</label>
        <input value="CT-DEMO-01" readonly>

        <label>Số lượng yêu cầu</label>
        <input type="number" value="25" readonly>

        <h2>Thông tin phiếu xuất kho</h2>

        <label>Lô hàng xuất (ưu tiên FEFO)</label>
        <select id="loHang">
            <option value="">-- Chọn lô hàng --</option>
            <option value="LO-DEMO-01">
                LO-DEMO-01 - HSD 15/01/2027
            </option>
            <option value="LO-DEMO-02">
                LO-DEMO-02 - HSD 15/03/2027
            </option>
        </select>

        <label>Người nhận hàng</label>
        <input id="nguoiNhan"
               placeholder="Nhập tên người nhận">

        <label>Số lượng thực xuất</label>
        <input id="soLuong"
               type="number"
               min="1"
               placeholder="Nhập số lượng">

        <div class="export-actions">
            <button type="button" onclick="kiemTraPhieu()">
                Kiểm tra phiếu
            </button>
        </div>

        <p id="thongBao"></p>
        <p class="export-demo">
            Dữ liệu minh họa - Chưa lưu vào cơ sở dữ liệu.
        </p>
    </div>
</div>

<style>
.export-page {
    max-width: 950px;
    margin: 0 auto;
}

.export-page h1 {
    color: #183b68;
}

.export-card {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px #00000012;
}

.export-card h2 {
    color: #2878d0;
    border-bottom: 1px solid #ddd;
    padding-bottom: 12px;
    margin-top: 25px;
}

.export-card label {
    display: block;
    margin-top: 15px;
    font-weight: 600;
}

.export-card input,
.export-card select {
    width: 100%;
    padding: 12px;
    margin-top: 6px;
    border: 1px solid #ddd;
    border-radius: 7px;
    background: white;
}

.export-card button {
    background: #2878d0;
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 7px;
    cursor: pointer;
}

.export-actions {
    margin-top: 25px;
}

.export-demo {
    color: #777;
    font-size: 12px;
}
</style>

<script>
function kiemTraPhieu() {
    const yeuCau = document.getElementById("yeuCau").value;
    const lo = document.getElementById("loHang").value;
    const nguoi = document.getElementById("nguoiNhan").value.trim();
    const sl = Number(document.getElementById("soLuong").value);
    const thongBao = document.getElementById("thongBao");

    if (!yeuCau || !lo || !nguoi || !Number.isInteger(sl) || sl <= 0) {
        thongBao.textContent = "Vui lòng nhập đầy đủ thông tin hợp lệ!";
        thongBao.style.color = "red";
        return;
    }

    const tonLo = lo === "LO-DEMO-01" ? 20 : 50;

    if (sl > tonLo || sl > 25) {
        thongBao.textContent = "Số lượng xuất vượt mức cho phép!";
        thongBao.style.color = "red";
        return;
    }

    thongBao.textContent = "Thông tin hợp lệ (kiểm tra demo, chưa lưu phiếu).";
    thongBao.style.color = "green";
}
</script>

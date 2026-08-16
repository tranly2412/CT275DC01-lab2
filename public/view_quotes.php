<?php
/* Đoạn mã xử lý PHP. */

define('TITLE', 'Xem tất cả các Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$has_access = ensure_admin_access();
$error_message = null;
$quotes = [];

if ($has_access) {

    $query = '
        SELECT id, quote, source, favorite, date_entered
        FROM quotes
        ORDER BY date_entered DESC
    ';

    try {
        $pdo = get_database_connection();
        $statement = $pdo->prepare($query);
        $statement->execute();
        $quotes = $statement->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error_message =
            'Lỗi khi truy xuất dữ liệu từ cơ sở dữ liệu: '
            . $e->getMessage();
    }
} else {

    $error_message = 'Bạn không có quyền truy cập trang này';
}
$success_message = null;

if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success_message = 'Đã xóa trích dẫn thành công.';
}

?>

<?php render_page_header(); ?>
<div class="container py-5">
    <!-- =========================
            TIÊU ĐỀ
    ========================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-chat-quote-fill text-primary"></i>
                Tất cả Trích dẫn
            </h2>
            <p class="text-muted mb-0">
                Quản lý các trích dẫn trong hệ thống
            </p>
        </div>

        <a
            href="add_quote.php"
            class="btn btn-primary">
            <i class="bi bi-plus-lg"></i>
            Thêm trích dẫn
        </a>
    </div>

    <!-- =========================
            THÔNG BÁO LỖI
    ========================== -->
    <?php if (!empty($error_message)): ?>
        <?php include __DIR__ . '/../partials/show_error.php'; ?>
    <?php endif; ?>
    <?php if ($has_access && empty($error_message)): ?>
        <!-- =========================
            THÔNG BÁO XOÁ THÀNH CÔNG
    ========================== -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= html_escape($success_message) ?>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Đóng">
                </button>
            </div>
        <?php endif; ?>
        <!-- =========================
                DANH SÁCH TRÍCH DẪN
        ========================== -->

        <?php if (!empty($quotes)): ?>
            <div class="row g-4">
                <?php foreach ($quotes as $quote): ?>
                    <!-- =========================
                            CARD TRÍCH DẪN
                    ========================== -->

                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <!-- ID + yêu thích -->
                                <div
                                    class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge bg-light text-secondary">
                                        #<?= html_escape($quote['id']) ?>
                                    </span>

                                    <?php if ($quote['favorite']): ?>
                                        <span
                                            class="badge bg-warning text-dark">
                                            <i class="bi bi-star-fill"></i>
                                            Yêu thích
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Nội dung quote -->
                                <div class="mb-4">
                                    <i
                                        class="bi bi-quote text-primary fs-2"></i>
                                    <p class="fs-5 mb-0">
                                        <?= html_escape($quote['quote']) ?>
                                    </p>
                                </div>
                                <!-- Nguồn -->

                                <div class="border-top pt-3">
                                    <div class="d-flex align-items-center">
                                        <div
                                            class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                            <i
                                                class="bi bi-person-fill text-primary"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted">
                                                Nguồn
                                            </small>
                                            <div class="fw-semibold">
                                                <?= html_escape($quote['source']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- =========================
                                    NÚT SỬA / XÓA
                            ========================== -->
                            <div
                                class="card-footer bg-white border-0 px-4 pb-4">
                                <div class="d-flex gap-2">
                                    <!-- Sửa -->
                                    <a
                                        href="edit_quote.php?id=<?= urlencode($quote['id']) ?>"
                                        class="btn btn-outline-primary btn-sm flex-fill">
                                        <i class="bi bi-pencil-square"></i>
                                        Sửa
                                    </a>

                                    <!-- Xóa -->
                                    <button
                                        type="button"
                                        class="btn btn-outline-danger btn-sm flex-fill"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteModal<?= $quote['id'] ?>">
                                        <i class="bi bi-trash"></i>
                                        Xóa
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- =================================================
                            MODAL XÁC NHẬN XÓA
                    ================================================== -->
                    <div
                        class="modal fade"
                        id="deleteModal<?= $quote['id'] ?>"
                        tabindex="-1"
                        aria-labelledby="deleteModalLabel<?= $quote['id'] ?>"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <!-- Modal Header -->
                                <div class="modal-header">
                                    <h5
                                        class="modal-title fw-bold"
                                        id="deleteModalLabel<?= $quote['id'] ?>">
                                        <i
                                            class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                                        Xác nhận xóa
                                    </h5>
                                    <button
                                        type="button"
                                        class="btn-close"
                                        data-bs-dismiss="modal"
                                        aria-label="Đóng"></button>
                                </div>
                                <!-- Modal Body -->
                                <div class="modal-body">
                                    <p class="mb-3">
                                        Bạn có chắc chắn muốn xóa
                                        trích dẫn này không?
                                    </p>
                                    <!-- Nội dung quote -->
                                    <div class="bg-light rounded p-3">
                                        <p class="mb-2 fst-italic">
                                            “<?= html_escape($quote['quote']) ?>”
                                        </p>
                                        <small class="text-muted">
                                            <i class="bi bi-person-fill"></i>
                                            <?= html_escape($quote['source']) ?>
                                        </small>
                                    </div>
                                    <!-- Cảnh báo -->
                                    <div
                                        class="alert alert-warning mt-3 mb-0 small">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Hành động này không thể hoàn tác.
                                    </div>
                                </div>
                                <!-- Modal Footer -->
                                <div class="modal-footer">
                                    <!-- Hủy -->
                                    <button
                                        type="button"
                                        class="btn btn-secondary"
                                        data-bs-dismiss="modal">
                                        Hủy
                                    </button>
                                    <!-- Form xóa -->
                                    <form
                                        action="delete_quote.php"
                                        method="post"
                                        class="d-inline">
                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= html_escape($quote['id']) ?>">
                                        <button
                                            type="submit"
                                            class="btn btn-danger">
                                            <i class="bi bi-trash3 me-1"></i>
                                            Xác nhận xóa
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>

            <!-- =========================
                    KHÔNG CÓ DỮ LIỆU
            ========================== -->
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i
                        class="bi bi-chat-square-text text-muted fs-1"></i>
                    <h5 class="mt-3">
                        Chưa có trích dẫn
                    </h5>
                    <p class="text-muted">
                        Hãy thêm trích dẫn đầu tiên vào hệ thống.
                    </p>
                    <a
                        href="add_quote.php"
                        class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i>
                        Thêm trích dẫn
                    </a>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php render_page_footer(); ?>
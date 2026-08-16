<?php
/* Đoạn mã xử lý PHP. */

define('TITLE', 'Thêm một Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$has_access = ensure_admin_access();

$success_message = null;
$error_message = null;
$reason = null;

$form_data = [
    'quote' => trim($_POST['quote'] ?? ''),
    'source' => trim($_POST['source'] ?? ''),
    'favorite' => !empty($_POST['favorite'])
];

/*
 * Kiểm tra quyền truy cập.
 */
if ($has_access && $_SERVER['REQUEST_METHOD'] === 'POST') {
    /*
     * Kiểm tra dữ liệu nhập vào.
     */
    if ($form_data['quote'] !== '' && $form_data['source'] !== '') {
        $query = '
            INSERT INTO quotes (quote, source, favorite)
            VALUES (?, ?, ?)
        ';
        try {
            $pdo = get_database_connection();
            $statement = $pdo->prepare($query);
            $statement->bindValue(
                1,
                $form_data['quote'],
                PDO::PARAM_STR
            );
            $statement->bindValue(
                2,
                $form_data['source'],
                PDO::PARAM_STR
            );
            $statement->bindValue(
                3,
                $form_data['favorite'],
                PDO::PARAM_BOOL
            );
            $statement->execute();
            if ($statement->rowCount() === 1) {
                $success_message = 'Trích dẫn của bạn đã được lưu trữ.';
                /*
                 * Xóa dữ liệu form sau khi lưu thành công.
                 */
                $form_data = [
                    'quote' => '',
                    'source' => '',
                    'favorite' => false
                ];
            } else {
                $error_message = 'Không thể lưu trữ trích dẫn.';
            }
        } catch (PDOException $e) {
            $error_message = 'Không thể lưu trữ trích dẫn.';
            $reason = $e->getMessage();
        }
    } else {

        $error_message = 'Hãy gõ vào cả Trích dẫn và Nguồn của nó!';
    }
} elseif (!$has_access) {

    $error_message = 'Bạn không có quyền truy cập trang này';
}


// html
?>
<?php render_page_header(); ?>
<div class="container py-4">
    <!-- Tiêu đề -->
    <div class="mb-4">
        <h2 class="fw-bold text-primary">
            <i class="bi bi-plus-circle-fill me-2"></i>
            Thêm một Trích dẫn
        </h2>
        <p class="text-muted mb-0">
            Thêm một trích dẫn mới vào hệ thống.
        </p>
    </div>
    <!-- Thông báo thành công -->
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success border-0 shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= html_escape($success_message) ?>
        </div>
    <?php endif; ?>

    <!-- Thông báo lỗi -->
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger border-0 shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= html_escape($error_message) ?>
        </div>
    <?php endif; ?>

    <?php if ($has_access): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0">
                    <i class="bi bi-chat-quote me-2"></i>
                    Thông tin trích dẫn
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="add_quote.php" method="post">
                    <!-- Trích dẫn -->
                    <div class="mb-4">
                        <label for="quote" class="form-label fw-semibold">
                            Trích dẫn
                        </label>
                        <textarea
                            id="quote"
                            name="quote"
                            rows="5"
                            class="form-control"
                            placeholder="Nhập nội dung trích dẫn..."
                            required
                        ><?= html_escape($form_data['quote']) ?></textarea>
                    </div>
                    <!-- Nguồn -->
                    <div class="mb-4">
                        <label for="source" class="form-label fw-semibold">
                            Nguồn
                        </label>
                        <input
                            type="text"
                            id="source"
                            name="source"
                            class="form-control"
                            placeholder="Nhập tên tác giả hoặc nguồn..."
                            value="<?= html_escape($form_data['source']) ?>"
                            required
                        >
                    </div>
                    <!-- Yêu thích -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input
                                type="checkbox"
                                id="favorite"
                                name="favorite"
                                value="yes"
                                class="form-check-input"
                                <?= $form_data['favorite'] ? 'checked' : '' ?>
                            >
                            <label
                                    class="form-check-label"
                                    for="favorite">
                                    <i class="bi bi-star-fill text-warning"></i>
                                    Đây là trích dẫn yêu thích
                                </label>
                        </div>
                    </div>
                    <!-- Nút -->
                    <div class="d-flex gap-2">
                        <button
                            type="submit"
                            name="submit"
                            class="btn btn-primary"
                        >
                            <i class="bi bi-plus-lg me-1"></i>
                            Thêm Trích dẫn này!
                        </button>
                        <a
                            href="view_quotes.php"
                            class="btn btn-outline-secondary"
                        >
                            <i class="bi bi-arrow-left me-1"></i>
                            Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php render_page_footer(); ?>
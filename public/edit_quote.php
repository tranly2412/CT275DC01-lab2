<?php
/* Đoạn mã xử lý PHP. */

define('TITLE', 'Sửa một Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$has_access = ensure_admin_access();
$error_message = null;
$success_message = null;

$quote = [
    'id' => '',
    'quote' => '',
    'source' => '',
    'favorite' => false
];

if (!$has_access) {
    $error_message = 'Bạn không có quyền truy cập trang này';
} else {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        $error_message = 'Trích dẫn không hợp lệ.';
    } else {
        try {
            $pdo = get_database_connection();
            /*
             * Lấy thông tin trích dẫn
             */
            $query = '
                SELECT id, quote, source, favorite
                FROM quotes
                WHERE id = ?
            ';
            $statement = $pdo->prepare($query);
            $statement->execute([$id]);
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                $error_message = 'Không tìm thấy trích dẫn cần sửa.';
            } else {
                $quote = $result;

                /*
                 * Xử lý khi nhấn nút Lưu thay đổi
                 */
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $quote['quote'] = trim($_POST['quote'] ?? '');
                    $quote['source'] = trim($_POST['source'] ?? '');
                    $quote['favorite'] = isset($_POST['favorite']);
                    if (
                        $quote['quote'] === '' ||
                        $quote['source'] === ''
                    ) {
                        $error_message =
                            'Hãy nhập đầy đủ Trích dẫn và Nguồn của nó!';
                    } else {
                        $update_query = '
                            UPDATE quotes
                            SET quote = ?, source = ?, favorite = ?
                            WHERE id = ?
                        ';
                        $update = $pdo->prepare($update_query);
                        $update->bindValue(
                            1,
                            $quote['quote'],
                            PDO::PARAM_STR
                        );
                        $update->bindValue(
                            2,
                            $quote['source'],
                            PDO::PARAM_STR
                        );
                        $update->bindValue(
                            3,
                            $quote['favorite'],
                            PDO::PARAM_BOOL
                        );
                        $update->bindValue(
                            4,
                            $id,
                            PDO::PARAM_INT
                        );
                        $update->execute();
                        $success_message =
                            'Trích dẫn đã được cập nhật thành công.';
                    }
                }
            }
        } catch (PDOException $e) {

            $error_message =
                'Không thể cập nhật trích dẫn: ' . $e->getMessage();
        }
    }
}
?>

<?php render_page_header(); ?>

<div class="container py-4">

    <!-- Tiêu đề -->
    <div class="mb-4">
        <h2 class="fw-bold text-primary mb-1">
            <i class="bi bi-pencil-square"></i>
            Sửa một Trích dẫn
        </h2>
        <p class="text-muted mb-0">
            Cập nhật thông tin của trích dẫn
        </p>
    </div>

    <!-- Thông báo lỗi -->
    <?php if (!empty($error_message)): ?>
        <?php include __DIR__ . '/../partials/show_error.php'; ?>
    <?php endif; ?>

    <!-- Thông báo thành công -->
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= html_escape($success_message) ?>
        </div>
    <?php endif; ?>

    <?php if ($has_access && empty($error_message)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0">
                    <i class="bi bi-chat-quote me-2"></i>
                    Thông tin trích dẫn
                </h5>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form
                        action="edit_quote.php?id=<?= urlencode($quote['id']) ?>"
                        method="post">
                        <!-- Trích dẫn -->
                        <div class="mb-3">
                            <label
                                for="quote"
                                class="form-label fw-semibold">
                                Trích dẫn
                            </label>
                            <textarea
                                class="form-control"
                                id="quote"
                                name="quote"
                                rows="5"
                                placeholder="Nhập nội dung trích dẫn..."
                                required><?= html_escape($quote['quote']) ?></textarea>
                        </div>
                        <!-- Nguồn -->
                        <div class="mb-3">
                            <label
                                for="source"
                                class="form-label fw-semibold">
                                Nguồn
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="source"
                                name="source"
                                value="<?= html_escape($quote['source']) ?>"
                                placeholder="Nhập nguồn của trích dẫn..."
                                required>
                        </div>
                        <!-- Yêu thích -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="favorite"
                                    name="favorite"
                                    value="yes"
                                    <?= $quote['favorite'] ? 'checked' : '' ?>>
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
                                class="btn btn-primary">
                                <i class="bi bi-check-lg"></i>
                                Lưu thay đổi
                            </button>
                            <a
                                href="view_quotes.php"
                                class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i>
                                Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php render_page_footer(); ?>
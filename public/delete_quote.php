<?php
/* Đoạn mã xử lý PHP. */

define('TITLE', 'Xóa một Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$has_access = ensure_admin_access();
$error_message = null;

if (!$has_access) {
    $error_message = 'Bạn không có quyền truy cập trang này';
} elseif ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $error_message = 'Yêu cầu không hợp lệ.';
} else {
    $id = filter_input(
        INPUT_POST,
        'id',
        FILTER_VALIDATE_INT
    );
    if (!$id) {
        $error_message = 'Trích dẫn không hợp lệ.';
    } else {
        try {
            $pdo = get_database_connection();
            $query = '
                DELETE FROM quotes
                WHERE id = ?
            ';
            $statement = $pdo->prepare($query);
            $statement->execute([$id]);
            if ($statement->rowCount() === 1) {

                /*
                 * Xóa thành công, thông báo → quay về danh sách
                 */
                header('Location: view_quotes.php?success=1');
                exit;
            } else {
                $error_message =
                    'Không tìm thấy trích dẫn cần xóa.';
            }
        } catch (PDOException $e) {
            $error_message =
                'Không thể xóa trích dẫn: '
                . $e->getMessage();
        }
    }
}
?>

<?php render_page_header(); ?>
<div class="container py-4">
    <div class="mb-4">
        <h2 class="fw-bold text-danger">
            <i class="bi bi-trash3-fill"></i>
            Xóa Trích dẫn
        </h2>
    </div>
    <?php if (!empty($error_message)): ?>
        <?php include __DIR__ . '/../partials/show_error.php'; ?>
        <a
            href="view_quotes.php"
            class="btn btn-outline-secondary"
        >
            <i class="bi bi-arrow-left"></i>
            Quay lại
        </a>
    <?php endif; ?>
</div>

<?php render_page_footer(); ?>
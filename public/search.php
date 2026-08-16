<?php

define('TITLE', 'Tìm kiếm Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$error_message = null;
$quotes = [];
$sources = [];

$keyword = trim($_GET['keyword'] ?? '');
$source = trim($_GET['source'] ?? '');

try {
    $pdo = get_database_connection();
    // Lấy danh sách nguồn/tác giả cho combobox
    $source_query = '
        SELECT DISTINCT source
        FROM quotes
        ORDER BY source
    ';
    $source_statement = $pdo->prepare($source_query);
    $source_statement->execute();
    $sources = $source_statement->fetchAll(PDO::FETCH_COLUMN);
    // Tìm kiếm khi người dùng submit form
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['keyword'])) {
        $query = '
            SELECT id, quote, source, favorite, date_entered
            FROM quotes
            WHERE quote ILIKE ?
        ';
        $params = ['%' . $keyword . '%'];
        // Nếu đã chọn nguồn thì tìm thêm theo source
        if ($source !== '') {
            $query .= ' AND source = ?';
            $params[] = $source;
        }
        $query .= ' ORDER BY date_entered DESC';
        $statement = $pdo->prepare($query);
        $statement->execute($params);
        $quotes = $statement->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error_message = 'Lỗi khi truy xuất dữ liệu từ cơ sở dữ liệu.';
}
?>

<?php render_page_header(); ?>
<div class="container py-4">
    <!-- Tiêu đề -->
    <div class="mb-4">
        <h2 class="fw-bold text-primary mb-1">
            <i class="bi bi-search"></i>
            Tìm kiếm Trích dẫn
        </h2>
        <p class="text-muted mb-0">
            Tìm kiếm trích dẫn theo từ khóa và nguồn/tác giả
        </p>
    </div>

    <!-- Thông báo lỗi -->
    <?php if (!empty($error_message)): ?>
        <?php include __DIR__ . '/../partials/show_error.php'; ?>
    <?php endif; ?>

    <!-- Form tìm kiếm -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form action="search.php" method="get">
                <div class="row g-3">
                    <!-- Từ khóa -->
                    <div class="col-md-6">
                        <label for="keyword" class="form-label fw-semibold">
                            Từ khóa
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="keyword"
                            name="keyword"
                            value="<?= html_escape($keyword) ?>"
                            placeholder="Nhập từ khóa cần tìm..."
                        >
                    </div>

                    <!-- Nguồn -->
                    <div class="col-md-4">
                        <label for="source" class="form-label fw-semibold">
                            Nguồn / Tác giả
                        </label>
                        <select
                            class="form-select"
                            id="source"
                            name="source"
                        >
                            <option value="">-- Tất cả nguồn --</option>
                            <?php foreach ($sources as $item): ?>
                                <option
                                    value="<?= html_escape($item) ?>"
                                    <?= $source === $item ? 'selected' : '' ?>
                                >
                                    <?= html_escape($item) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Nút tìm -->
                    <div class="col-md-2 d-flex align-items-end">
                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            <i class="bi bi-search me-1"></i>
                            Tìm kiếm
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Kết quả -->
    <?php if (isset($_GET['keyword'])): ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">
                Kết quả tìm kiếm
            </h5>
            <span class="badge bg-primary">
                <?= count($quotes) ?> kết quả
            </span>
        </div>
        <?php if (count($quotes) > 0): ?>
            <div class="row g-4">
                <?php foreach ($quotes as $quote): ?>
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-3">
                                    <i class="bi bi-quote fs-2 text-primary"></i>
                                    <?php if ($quote['favorite']): ?>
                                        <i class="bi bi-star-fill text-warning fs-5"></i>
                                    <?php endif; ?>
                                </div>
                                <p class="fs-5 mb-3">
                                    <?= html_escape($quote['quote']) ?>
                                </p>
                                <div class="text-muted">
                                    <i class="bi bi-person-fill me-1"></i>
                                    <?= html_escape($quote['source']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-circle me-2"></i>
                Không tìm thấy trích dẫn phù hợp.
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>

<?php render_page_footer(); ?>
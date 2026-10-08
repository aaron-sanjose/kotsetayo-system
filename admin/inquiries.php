<?php
/**
 * CarBuy - Inquiry Management
 */
require_once __DIR__ . '/../includes/admin-header.php';

$filter = clean($_GET['status'] ?? '');

// Handle status change.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (csrf_check($_POST['csrf_token'] ?? '')) {
        $inq_id = (int)($_POST['id'] ?? 0);
        $new_status = clean($_POST['status'] ?? '');
        if ($inq_id > 0 && in_array($new_status, ['Pending', 'Contacted', 'Completed', 'Cancelled'], true)) {
            query("UPDATE inquiries SET status = ? WHERE id = ?", [$new_status, $inq_id]);
            set_flash('success', 'Inquiry status updated to ' . $new_status . '.');
        } else {
            set_flash('error', 'Invalid inquiry or status.');
        }
    } else {
        set_flash('error', 'Security token mismatch.');
    }
    header('Location: inquiries.php');
    exit;
}

$where = '';
$params = [];
if ($filter !== '') {
    $where = " WHERE i.status = ?";
    $params[] = $filter;
}

$inquiries = fetch_all(
    "SELECT i.*, c.brand, c.model FROM inquiries i
     LEFT JOIN cars c ON c.id = i.car_id$where
     ORDER BY i.created_at DESC",
    $params
);

$token = csrf_token();
$statuses = ['Pending', 'Contacted', 'Completed', 'Cancelled'];
?>

<div class="panel">
    <div class="panel-head">
        <h2>Inquiries (<?php echo count($inquiries); ?>)</h2>
        <form method="get" action="inquiries.php" class="inline-filter">
            <select name="status" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <?php foreach ($statuses as $st): ?>
                <option value="<?php echo e($st); ?>" <?php echo $filter === $st ? 'selected' : ''; ?>><?php echo e($st); ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($filter !== ''): ?><a href="inquiries.php" class="btn btn-outline btn-sm">Clear</a><?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Customer</th><th>Vehicle</th><th>Email</th><th>Contact</th>
                    <th>Message</th><th>Date</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($inquiries)): ?>
                    <tr><td colspan="8" class="text-muted">No inquiries found.</td></tr>
                <?php else: foreach ($inquiries as $inq): ?>
                <tr>
                    <td><strong><?php echo e($inq['customer_name']); ?></strong></td>
                    <td><?php echo e($inq['brand'] . ' ' . $inq['model']); ?></td>
                    <td><?php echo e($inq['email']); ?></td>
                    <td><?php echo e($inq['contact']); ?></td>
                    <td class="cell-message" title="<?php echo e($inq['message']); ?>"><?php echo e(mb_substr($inq['message'] ?? '', 0, 50)); ?><?php echo mb_strlen($inq['message'] ?? '') > 50 ? '…' : ''; ?></td>
                    <td><?php echo e(date('M j, Y', strtotime($inq['created_at']))); ?></td>
                    <td>
                        <form method="post" action="inquiries.php" class="inline-form">
                            <input type="hidden" name="csrf_token" value="<?php echo e($token); ?>">
                            <input type="hidden" name="id" value="<?php echo (int)$inq['id']; ?>">
                            <select name="status" onchange="this.form.submit()" class="status-select">
                                <?php foreach ($statuses as $st): ?>
                                <option value="<?php echo e($st); ?>" <?php echo $inq['status'] === $st ? 'selected' : ''; ?>><?php echo e($st); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                    <td>
                        <button type="button" class="btn btn-outline btn-sm" data-inquiry-view="<?php echo (int)$inq['id']; ?>">View</button>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Inquiry detail modal -->
<div class="modal" id="inquiryModal" aria-hidden="true">
    <div class="modal-box modal-wide">
        <h3>Inquiry Details</h3>
        <div id="inquiryModalBody"></div>
        <div class="modal-actions">
            <button type="button" class="btn btn-outline" id="inquiryClose">Close</button>
        </div>
    </div>
</div>

<script>
// Pass inquiry data to JS for the detail modal.
window.__inquiries = <?php
    $json = [];
    foreach ($inquiries as $inq) {
        $json[] = [
            'customer' => $inq['customer_name'],
            'vehicle'  => trim($inq['brand'] . ' ' . $inq['model']),
            'email'    => $inq['email'],
            'contact'  => $inq['contact'],
            'message'  => $inq['message'],
            'status'   => $inq['status'],
            'date'     => date('F j, Y g:i A', strtotime($inq['created_at'])),
        ];
    }
    echo json_encode($json);
?>;
</script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
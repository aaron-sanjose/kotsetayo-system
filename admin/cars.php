  <?php
/**
 * KotseTayo - Admin Vehicle Management
 */
require_once __DIR__ . '/../includes/admin-header.php';

// Handle actions: change status or delete.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = clean($_POST['action'] ?? '');
    $car_id = (int)($_POST['id'] ?? 0);

    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        set_flash('error', 'Security token mismatch. Please try again.');
    } elseif ($car_id > 0) {
        if ($action === 'set_status') {
            $new_status = clean($_POST['status'] ?? '');
            if (in_array($new_status, ['Available', 'Reserved', 'Sold'], true)) {
                query("UPDATE cars SET status = ? WHERE id = ?", [$new_status, $car_id]);
                set_flash('success', 'Vehicle status updated to ' . $new_status . '.');
            }
        } elseif ($action === 'delete') {
            // Delete images from disk first, then the record (images cascade).
            $imgs = fetch_all("SELECT image_path FROM car_images WHERE car_id = ?", [$car_id]);
            query("DELETE FROM cars WHERE id = ?", [$car_id]);
            foreach ($imgs as $img) {
                delete_uploaded_file($img['image_path']);
            }
            set_flash('success', 'Vehicle deleted successfully.');
        }
    }
    header('Location: cars.php');
    exit;
}

$cars = fetch_all(
    "SELECT c.*, (SELECT ci.image_path FROM car_images ci WHERE ci.car_id = c.id ORDER BY ci.sort_order, ci.id LIMIT 1) AS image_path
     FROM cars c ORDER BY c.created_at DESC"
);

$token = csrf_token();
?>

<div class="panel">
    <div class="panel-head">
        <h2>All Vehicles (<?php echo count($cars); ?>)</h2>
        <a href="add-car.php" class="btn btn-primary">+ Add Vehicle</a>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Brand / Model</th>
                    <th>Year</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Date Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
<?php if (empty($cars)): ?>
                    <tr><td colspan="7" class="text-muted">No vehicles found. Click "Add Vehicle" to start.</td></tr>
                <?php else: foreach ($cars as $car): ?>
                <tr>
                    <td><img class="table-thumb" src="<?php echo e(car_image($car)); ?>" alt=""></td>
                    <td><strong><?php echo e($car['brand']); ?> <?php echo e($car['model']); ?></strong></td>
                    <td><?php echo e($car['year']); ?></td>
                    <td><?php echo format_money($car['price']); ?></td>
                    <td><span class="badge <?php echo e(status_class($car['status'])); ?>"><?php echo e($car['status']); ?></span></td>
                    <td><?php echo e(date('M j, Y', strtotime($car['created_at']))); ?></td>
                    <td>
                        <div class="table-actions">
                            <a class="btn btn-outline btn-sm" href="../car-details.php?id=<?php echo (int)$car['id']; ?>" target="_blank">View</a>
                            <a class="btn btn-outline btn-sm" href="edit-car.php?id=<?php echo (int)$car['id']; ?>">Edit</a>

                            <?php if ($car['status'] !== 'Available'): ?>
                            <form method="post" action="cars.php" class="inline-form">
                                <input type="hidden" name="csrf_token" value="<?php echo e($token); ?>">
                                <input type="hidden" name="action" value="set_status">
                                <input type="hidden" name="id" value="<?php echo (int)$car['id']; ?>">
                                <input type="hidden" name="status" value="Available">
                                <button type="submit" class="btn btn-sm btn-avail">Available</button>
                            </form>
                            <?php endif; ?>

                            <?php if ($car['status'] !== 'Reserved'): ?>
                            <form method="post" action="cars.php" class="inline-form">
                                <input type="hidden" name="csrf_token" value="<?php echo e($token); ?>">
                                <input type="hidden" name="action" value="set_status">
                                <input type="hidden" name="id" value="<?php echo (int)$car['id']; ?>">
                                <input type="hidden" name="status" value="Reserved">
                                <button type="submit" class="btn btn-sm btn-resv">Reserve</button>
                            </form>
                            <?php endif; ?>

                            <?php if ($car['status'] !== 'Sold'): ?>
                            <form method="post" action="cars.php" class="inline-form">
                                <input type="hidden" name="csrf_token" value="<?php echo e($token); ?>">
                                <input type="hidden" name="action" value="set_status">
                                <input type="hidden" name="id" value="<?php echo (int)$car['id']; ?>">
                                <input type="hidden" name="status" value="Sold">
                                <button type="submit" class="btn btn-sm btn-sold">Sold</button>
                            </form>
                            <?php endif; ?>

                            <button type="button" class="btn btn-sm btn-danger" data-delete-id="<?php echo (int)$car['id']; ?>" data-delete-name="<?php echo e($car['brand'] . ' ' . $car['model']); ?>">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Delete confirmation modal -->
<div class="modal" id="deleteModal" aria-hidden="true">
    <div class="modal-box">
        <h3>Confirm Deletion</h3>
        <p>Are you sure you want to delete <strong id="deleteName"></strong>? This action cannot be undone.</p>
        <form method="post" action="cars.php">
            <input type="hidden" name="csrf_token" value="<?php echo e($token); ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="deleteId" value="">
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" id="deleteCancel">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
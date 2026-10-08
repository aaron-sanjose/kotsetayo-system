<?php
/**
 * CarBuy - Edit Vehicle
 */
require_once __DIR__ . '/../includes/admin-header.php';

$id = (int)($_GET['id'] ?? 0);
$car = fetch_one("SELECT * FROM cars WHERE id = ?", [$id]);

if (!$car) {
    set_flash('error', 'Vehicle not found.');
    header('Location: cars.php');
    exit;
}

$car_images = fetch_all("SELECT * FROM car_images WHERE car_id = ? ORDER BY id", [$id]);

$errors = [];
$old = [
    'brand' => $car['brand'], 'model' => $car['model'], 'year' => $car['year'],
    'price' => $car['price'], 'mileage' => $car['mileage'],
    'transmission' => $car['transmission'], 'fuel_type' => $car['fuel_type'],
    'color' => $car['color'], 'engine' => $car['engine'],
    'description' => $car['description'], 'status' => $car['status'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token mismatch. Please try again.';
    } else {
        foreach ($old as $k => $v) {
            if ($k === 'price' || $k === 'mileage') continue;
            $old[$k] = clean($_POST[$k] ?? $car[$k]);
        }
        $old['price']   = clean($_POST['price'] ?? $car['price']);
        $old['mileage'] = clean($_POST['mileage'] ?? $car['mileage']);

        // Validation
        if ($old['brand'] === '') $errors[] = 'Brand is required.';
        if ($old['model'] === '') $errors[] = 'Model is required.';
        if (!is_numeric($old['year']) || (int)$old['year'] < 1990 || (int)$old['year'] > date('Y') + 1) $errors[] = 'Enter a valid year.';
        if (!is_numeric($old['price']) || (float)$old['price'] < 0) $errors[] = 'Enter a valid numeric price.';
        if ($old['mileage'] === '' || !is_numeric($old['mileage']) || (int)$old['mileage'] < 0) $errors[] = 'Enter a valid mileage.';
        if (!in_array($old['transmission'], ['Automatic', 'Manual'], true)) $errors[] = 'Invalid transmission.';
        if (!in_array($old['status'], ['Available', 'Reserved', 'Sold'], true)) $errors[] = 'Invalid status.';

        // Handle new image uploads.
        $uploaded = [];
        if (isset($_FILES['images'])) {
            $count = count($_FILES['images']['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
                $file = [
                    'name' => $_FILES['images']['name'][$i],
                    'type' => $_FILES['images']['type'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error' => $_FILES['images']['error'][$i],
                    'size' => $_FILES['images']['size'][$i],
                ];
                $res = upload_car_image($file);
                if (strpos($res, 'ERR:') === 0) {
                    $errors[] = 'Image "' . e($_FILES['images']['name'][$i]) . '": ' . substr($res, 4);
                } else {
                    $uploaded[] = $res;
                }
            }
        }

        // Handle image deletions (a set of IDs marked for removal).
        $delete_ids = isset($_POST['delete_image']) ? (array)$_POST['delete_image'] : [];

        if (empty($errors)) {
            query(
                "UPDATE cars SET brand = ?, model = ?, year = ?, price = ?, mileage = ?,
                 transmission = ?, fuel_type = ?, color = ?, engine = ?, description = ?, status = ?
                 WHERE id = ?",
                [$old['brand'], $old['model'], (int)$old['year'], (float)$old['price'],
                 (int)$old['mileage'], $old['transmission'], $old['fuel_type'], $old['color'],
                 $old['engine'], $old['description'], $old['status'], $id]
            );

            // Insert new images.
            foreach ($uploaded as $path) {
                query("INSERT INTO car_images (car_id, image_path) VALUES (?, ?)", [$id, $path]);
            }

            // Delete selected images from DB and disk.
            foreach ($delete_ids as $imgId) {
                $img = fetch_one("SELECT * FROM car_images WHERE id = ? AND car_id = ?", [(int)$imgId, $id]);
                if ($img) {
                    query("DELETE FROM car_images WHERE id = ?", [(int)$imgId]);
                    delete_uploaded_file($img['image_path']);
                }
            }

            set_flash('success', 'Vehicle updated successfully.');
            header('Location: cars.php');
            exit;
        }
    }
}

$token = csrf_token();
?>

<div class="panel">
    <div class="panel-head"><h2>Edit Vehicle</h2><a class="btn btn-outline btn-sm" href="cars.php">&larr; Back</a></div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-error"><ul><?php foreach ($errors as $er): ?><li><?php echo e($er); ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="post" action="edit-car.php?id=<?php echo (int)$id; ?>" class="form admin-form" enctype="multipart/form-data" id="editCarForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo e($token); ?>">

        <div class="form-group">
            <label>Current Images</label>
            <div class="image-manager" id="imageManager">
                <?php foreach ($car_images as $img): ?>
                <div class="image-manager-item">
                    <img src="<?php echo e(car_image(['image_path' => $img['image_path']])); ?>" alt="">
                    <label class="image-remove">
                        <input type="checkbox" name="delete_image[]" value="<?php echo (int)$img['id']; ?>">
                        Remove
                    </label>
                </div>
                <?php endforeach; ?>
                <?php if (empty($car_images)): ?><span class="text-muted">No images.</span><?php endif; ?>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="brand">Brand *</label>
                <input type="text" id="brand" name="brand" value="<?php echo e($old['brand']); ?>" required>
            </div>
            <div class="form-group">
                <label for="model">Model *</label>
                <input type="text" id="model" name="model" value="<?php echo e($old['model']); ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="year">Year *</label>
                <input type="number" id="year" name="year" min="1990" max="<?php echo date('Y') + 1; ?>" value="<?php echo e($old['year']); ?>" required>
            </div>
            <div class="form-group">
                <label for="price">Price (₱) *</label>
                <input type="number" id="price" name="price" min="0" step="0.01" value="<?php echo e($old['price']); ?>" required>
            </div>
            <div class="form-group">
                <label for="mileage">Mileage (km) *</label>
                <input type="number" id="mileage" name="mileage" min="0" value="<?php echo e($old['mileage']); ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="transmission">Transmission *</label>
                <select name="transmission" id="transmission">
                    <option value="Automatic" <?php echo $old['transmission'] === 'Automatic' ? 'selected' : ''; ?>>Automatic</option>
                    <option value="Manual" <?php echo $old['transmission'] === 'Manual' ? 'selected' : ''; ?>>Manual</option>
                </select>
            </div>
            <div class="form-group">
                <label for="fuel_type">Fuel Type *</label>
                <select name="fuel_type" id="fuel_type">
                    <option value="Gasoline" <?php echo $old['fuel_type'] === 'Gasoline' ? 'selected' : ''; ?>>Gasoline</option>
                    <option value="Diesel" <?php echo $old['fuel_type'] === 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
                    <option value="Hybrid" <?php echo $old['fuel_type'] === 'Hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                    <option value="Electric" <?php echo $old['fuel_type'] === 'Electric' ? 'selected' : ''; ?>>Electric</option>
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status *</label>
                <select name="status" id="status">
                    <option value="Available" <?php echo $old['status'] === 'Available' ? 'selected' : ''; ?>>Available</option>
                    <option value="Reserved" <?php echo $old['status'] === 'Reserved' ? 'selected' : ''; ?>>Reserved</option>
                    <option value="Sold" <?php echo $old['status'] === 'Sold' ? 'selected' : ''; ?>>Sold</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="color">Color</label>
                <input type="text" id="color" name="color" value="<?php echo e($old['color']); ?>">
            </div>
            <div class="form-group">
                <label for="engine">Engine</label>
                <input type="text" id="engine" name="engine" value="<?php echo e($old['engine']); ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5"><?php echo e($old['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="images">Add More Images (JPG, JPEG, PNG, WEBP - max 5MB each, multiple allowed)</label>
            <input type="file" id="images" name="images[]" accept=".jpg,.jpeg,.png,.webp" multiple>
            <div class="image-preview" id="imagePreview"></div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">Save Changes</button>
            <a href="cars.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
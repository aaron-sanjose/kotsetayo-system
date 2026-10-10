<?php
/**
 * KotseTayo - Edit Vehicle
 */
require_once __DIR__ . '/../includes/admin-header.php';

$id = (int)($_GET['id'] ?? 0);
$car = fetch_one("SELECT * FROM cars WHERE id = ?", [$id]);

if (!$car) {
    set_flash('error', 'Vehicle not found.');
    header('Location: cars.php');
    exit;
}

$car_images = fetch_all("SELECT * FROM car_images WHERE car_id = ? ORDER BY sort_order, id", [$id]);
$car_images_by_id = [];
foreach ($car_images as $image) {
    $car_images_by_id[(int)$image['id']] = $image;
}

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
                    $uploaded[$i] = $res;
                }
            }
        }

        // Handle image deletions (a set of IDs marked for removal).
        $delete_ids = [];
        foreach ((array)($_POST['delete_image'] ?? []) as $imgId) {
            if (ctype_digit((string)$imgId) && isset($car_images_by_id[(int)$imgId])) {
                $delete_ids[(int)$imgId] = true;
            }
        }

        if (empty($errors)) {
            query(
                "UPDATE cars SET brand = ?, model = ?, year = ?, price = ?, mileage = ?,
                 transmission = ?, fuel_type = ?, color = ?, engine = ?, description = ?, status = ?
                 WHERE id = ?",
                [$old['brand'], $old['model'], (int)$old['year'], (float)$old['price'],
                 (int)$old['mileage'], $old['transmission'], $old['fuel_type'], $old['color'],
                 $old['engine'], $old['description'], $old['status'], $id]
            );

            // Delete selected images from DB and disk.
            foreach (array_keys($delete_ids) as $imgId) {
                query("DELETE FROM car_images WHERE id = ? AND car_id = ?", [$imgId, $id]);
                delete_uploaded_file($car_images_by_id[$imgId]['image_path']);
            }

            // Keep the submitted order, ignoring unknown or duplicate image keys.
            $ordered_images = [];
            $seen_images = [];
            foreach ((array)($_POST['image_order'] ?? []) as $imageKey) {
                if (!is_string($imageKey)) continue;

                if (strpos($imageKey, 'existing:') === 0) {
                    $imgId = substr($imageKey, 9);
                    if (!ctype_digit($imgId)) continue;
                    $imgId = (int)$imgId;
                    if (!isset($car_images_by_id[$imgId]) || isset($delete_ids[$imgId]) || isset($seen_images['existing:' . $imgId])) continue;
                    $ordered_images[] = ['type' => 'existing', 'id' => $imgId];
                    $seen_images['existing:' . $imgId] = true;
                } elseif (strpos($imageKey, 'new:') === 0) {
                    $uploadIndex = substr($imageKey, 4);
                    if (!ctype_digit($uploadIndex)) continue;
                    $uploadIndex = (int)$uploadIndex;
                    if (!array_key_exists($uploadIndex, $uploaded) || isset($seen_images['new:' . $uploadIndex])) continue;
                    $ordered_images[] = ['type' => 'new', 'index' => $uploadIndex];
                    $seen_images['new:' . $uploadIndex] = true;
                }
            }

            // Preserve older clients or omitted entries by appending any unlisted images.
            foreach ($car_images as $image) {
                $imgId = (int)$image['id'];
                if (!isset($delete_ids[$imgId]) && !isset($seen_images['existing:' . $imgId])) {
                    $ordered_images[] = ['type' => 'existing', 'id' => $imgId];
                    $seen_images['existing:' . $imgId] = true;
                }
            }
            foreach ($uploaded as $uploadIndex => $path) {
                if (!isset($seen_images['new:' . $uploadIndex])) {
                    $ordered_images[] = ['type' => 'new', 'index' => $uploadIndex];
                }
            }

            foreach ($ordered_images as $sortOrder => $image) {
                if ($image['type'] === 'existing') {
                    query(
                        "UPDATE car_images SET sort_order = ? WHERE id = ? AND car_id = ?",
                        [$sortOrder, $image['id'], $id]
                    );
                } else {
                    query(
                        "INSERT INTO car_images (car_id, image_path, sort_order) VALUES (?, ?, ?)",
                        [$id, $uploaded[$image['index']], $sortOrder]
                    );
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
            <div class="image-manager-heading">
                <div>
                    <label>Vehicle Images</label>
                    <p class="image-manager-note">Drag by the grip to reorder, or use the arrows. The first image is the listing thumbnail.</p>
                </div>
                <span class="image-manager-count" id="imageOrderSummary" aria-live="polite"><?php echo count($car_images); ?> <?php echo count($car_images) === 1 ? 'image' : 'images'; ?></span>
            </div>
            <div class="image-manager" id="imageManager">
                <?php foreach ($car_images as $image_index => $img): ?>
                <div class="image-manager-item<?php echo $image_index === 0 ? ' is-thumbnail' : ''; ?>" data-image-key="existing:<?php echo (int)$img['id']; ?>">
                    <div class="image-card-preview">
                        <img src="<?php echo e(car_image(['image_path' => $img['image_path']])); ?>" alt="Vehicle image">
                        <span class="image-thumbnail-badge"<?php echo $image_index === 0 ? '' : ' hidden'; ?>>Thumbnail</span>
                    </div>
                    <div class="image-manager-meta">
                        <span class="image-sequence-number"><?php echo str_pad((string)($image_index + 1), 2, '0', STR_PAD_LEFT); ?></span>
                        <span><?php echo e(basename($img['image_path'])); ?></span>
                    </div>
                    <div class="image-order-controls">
                        <div class="image-position-buttons">
                            <button type="button" class="image-move-up" aria-label="Move image earlier" title="Move earlier"<?php echo $image_index === 0 ? ' disabled' : ''; ?>>&uarr;</button>
                            <button type="button" class="image-move-down" aria-label="Move image later" title="Move later"<?php echo $image_index === count($car_images) - 1 ? ' disabled' : ''; ?>>&darr;</button>
                            <span class="image-drag-handle" title="Drag to reorder" aria-label="Drag to reorder">&#9776; <span>Drag</span></span>
                        </div>
                        <button type="button" class="image-make-thumbnail"<?php echo $image_index === 0 ? ' disabled' : ''; ?>><?php echo $image_index === 0 ? 'Current thumbnail' : 'Make thumbnail'; ?></button>
                    </div>
                    <label class="image-remove">
                        <input type="checkbox" name="delete_image[]" value="<?php echo (int)$img['id']; ?>">
                        <span>Remove image</span>
                    </label>
                    <input type="hidden" name="image_order[]" value="existing:<?php echo (int)$img['id']; ?>">
                </div>
                <?php endforeach; ?>
                <?php if (empty($car_images)): ?><div class="image-manager-empty">No images yet. Add images below to choose a thumbnail.</div><?php endif; ?>
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
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">Save Changes</button>
            <a href="cars.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
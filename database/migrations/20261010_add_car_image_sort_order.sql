ALTER TABLE `car_images`
  ADD COLUMN `sort_order` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `image_path`,
  ADD KEY `idx_car_images_order` (`car_id`, `sort_order`);

UPDATE `car_images`
SET `sort_order` = `id`;

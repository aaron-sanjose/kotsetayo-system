<?php
/**
 * CarBuy - Admin Sidebar navigation.
 * Expects $admin_page (basename of current script) to be set by admin-header.
 */
function adm_active(string $page): string {
    global $admin_page;
    return $admin_page === $page ? ' class="active"' : '';
}
?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-brand">
        <span class="brand-mark">CB</span>
        <span class="brand-text">Car<span>Buy</span> Admin</span>
    </div>
    <nav class="admin-nav">
        <a href="dashboard.php"<?php echo adm_active('dashboard.php'); ?>>Dashboard</a>
        <span class="admin-nav-label">Vehicles</span>
        <a href="cars.php"<?php echo adm_active('cars.php'); ?>>Manage Vehicles</a>
        <a href="add-car.php"<?php echo adm_active('add-car.php'); ?>>Add Vehicle</a>
        <span class="admin-nav-label">Management</span>
        <a href="inquiries.php"<?php echo adm_active('inquiries.php'); ?>>Inquiries</a>
        <a href="customers.php"<?php echo adm_active('customers.php'); ?>>Customers</a>
        <a href="sales.php"<?php echo adm_active('sales.php'); ?>>Sales</a>
        <a href="reports.php"<?php echo adm_active('reports.php'); ?>>Reports</a>
        <span class="admin-nav-label">Account</span>
        <a href="profile.php"<?php echo adm_active('profile.php'); ?>>My Account</a>
        <a href="logout.php">Logout</a>
    </nav>
    <div class="admin-sidebar-footer">
        <a href="../index.php" class="btn btn-outline btn-sm btn-block">&#8592; Back to Website</a>
    </div>
</aside>
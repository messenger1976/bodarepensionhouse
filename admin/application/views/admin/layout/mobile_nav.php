<?php
$this->load->model('Admin_model');
$admin_id = $this->session->userdata('admin_id');
$current_uri = uri_string();

$has_calendar_menu = $admin_id && (
    $this->Admin_model->has_permission($admin_id, 'view_calendar') ||
    $this->Admin_model->has_permission($admin_id, 'view_bookings') ||
    $this->Admin_model->has_permission($admin_id, 'view_events')
);

$inquiry_badge_count = 0;
$show_inquiries = $admin_id && $this->Admin_model->has_permission($admin_id, 'view_inquiries');
if ($show_inquiries && $this->db->table_exists('inquiry')) {
    $this->db->where_in('status', array('new', 'guest_replied'));
    $inquiry_badge_count = (int) $this->db->count_all_results('inquiry');
}

$dash_active = ($current_uri === 'dashboard' || $current_uri === '' || $current_uri === 'login');
$bookings_active = strpos($current_uri, 'bookings') !== false;
$calendar_active = (strpos($current_uri, 'calendar') === 0 || $current_uri === 'calendar');
$inquiries_active = strpos($current_uri, 'inquiries') !== false;
?>
<nav class="mob-tabbar d-lg-none" aria-label="Mobile navigation">
    <a href="<?php echo base_url('dashboard'); ?>" class="mob-tab<?php echo $dash_active ? ' active' : ''; ?>">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
    </a>
    <?php if ($admin_id && $this->Admin_model->has_permission($admin_id, 'view_bookings')): ?>
    <a href="<?php echo base_url('bookings'); ?>" class="mob-tab<?php echo $bookings_active ? ' active' : ''; ?>">
        <i class="bi bi-calendar-check"></i>
        <span>Bookings</span>
    </a>
    <?php endif; ?>
    <?php if ($has_calendar_menu): ?>
    <a href="<?php echo base_url('calendar'); ?>" class="mob-tab<?php echo $calendar_active ? ' active' : ''; ?>">
        <i class="bi bi-calendar3"></i>
        <span>Calendar</span>
    </a>
    <?php endif; ?>
    <?php if ($show_inquiries): ?>
    <a href="<?php echo base_url('inquiries'); ?>" class="mob-tab<?php echo $inquiries_active ? ' active' : ''; ?>">
        <i class="bi bi-envelope"></i>
        <span>Inquiries</span>
        <?php if ($inquiry_badge_count > 0): ?>
        <span id="inquiry-tab-badge" class="tab-badge"><?php echo (int) $inquiry_badge_count; ?></span>
        <?php else: ?>
        <span id="inquiry-tab-badge" class="tab-badge" style="display:none;"></span>
        <?php endif; ?>
    </a>
    <?php endif; ?>
    <button type="button" class="mob-tab menu-toggle" aria-label="Open menu">
        <i class="bi bi-list"></i>
        <span>Menu</span>
    </button>
</nav>

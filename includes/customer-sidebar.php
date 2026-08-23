<?php
$customerSidebarActiveTab = isset($customerSidebarActiveTab) ? $customerSidebarActiveTab : 'bookings';
?>
<aside class="customer-sidebar" aria-label="Customer dashboard menu">
    <h2 class="customer-sidebar-title">Account Menu</h2>
    <nav class="customer-sidebar-nav">
        <a class="tab-link <?php echo $customerSidebarActiveTab === 'bookings' ? 'active' : ''; ?>" data-tab="bookings" href="customer-bookings.php">My Bookings</a>
        <a class="tab-link <?php echo $customerSidebarActiveTab === 'invoices' ? 'active' : ''; ?>" data-tab="invoices" href="customer-invoices.php">My Invoices</a>
        <a class="tab-link <?php echo $customerSidebarActiveTab === 'profile' ? 'active' : ''; ?>" data-tab="profile" href="customer-profile.php">My Profile</a>
        <a class="tab-link <?php echo $customerSidebarActiveTab === 'security' ? 'active' : ''; ?>" data-tab="security" href="customer-dashboard.php?tab=security">Account Security</a>
        <a class="tab-link <?php echo $customerSidebarActiveTab === 'inquiry' ? 'active' : ''; ?>" data-tab="inquiry" href="customer-inquiry.php">Send Inquiry</a>
    </nav>
</aside>

<?php
$header_icon = $success ? 'bi-check-circle' : 'bi-x-circle';
$header_title = 'Account Activation';
$this->load->view('admin/auth/_header', array('title' => $title, 'header_icon' => $header_icon, 'header_title' => $header_title));
?>
            <div class="text-center py-3">
                <?php if ($success): ?>
                    <i class="bi bi-check-circle-fill" style="font-size: 3rem; color: #198754;"></i>
                    <h5 class="mt-3">Account Activated</h5>
                <?php else: ?>
                    <i class="bi bi-x-circle-fill" style="font-size: 3rem; color: #dc3545;"></i>
                    <h5 class="mt-3">Activation Failed</h5>
                <?php endif; ?>
                <p class="text-muted mt-2"><?php echo html_escape($message); ?></p>
                <?php if ($success): ?>
                    <a href="<?php echo site_url('login'); ?>" class="btn btn-login mt-2" style="max-width: 220px;">
                        <i class="bi bi-box-arrow-in-right"></i> Go to Login
                    </a>
                <?php else: ?>
                    <a href="<?php echo site_url('register'); ?>" class="btn btn-login mt-2" style="max-width: 220px;">
                        <i class="bi bi-person-plus"></i> Register Again
                    </a>
                <?php endif; ?>
            </div>
<?php $this->load->view('admin/auth/_footer'); ?>

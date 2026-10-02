<?php
$ro = $can_manage ? '' : 'disabled';
$s = function ($key, $default = '') use ($settings) {
    return htmlspecialchars(isset($settings[$key]) ? (string) $settings[$key] : $default, ENT_QUOTES, 'UTF-8');
};
$image_url = function ($key) use ($settings) {
    $path = isset($settings[$key]) ? trim((string) $settings[$key]) : '';
    if ($path === '' || !is_file(FCPATH . $path)) {
        return '';
    }
    return base_url($path) . '?v=' . filemtime(FCPATH . $path);
};
$logo_url = $image_url('logo_path');
$og_url = $image_url('og_image_path');
$noindex = isset($settings['seo_noindex']) && $settings['seo_noindex'] === '1';
?>
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h5 class="mb-0"><i class="bi bi-sliders"></i> Site Settings</h5>
        <div>
            <?php if ($noindex): ?>
                <span class="badge bg-danger">Hidden from search engines</span>
            <?php else: ?>
                <span class="badge bg-success">Visible to search engines</span>
            <?php endif; ?>
            <a href="<?php echo htmlspecialchars($public_url, ENT_QUOTES, 'UTF-8'); ?>/" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary ms-2">
                <i class="bi bi-box-arrow-up-right"></i> View website
            </a>
        </div>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $this->session->flashdata('success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!$table_ready): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            The <code>site_settings</code> table does not exist yet. Run <code>admin/sql/create_site_settings_table.sql</code> on the database, then reload this page.
        </div>
    <?php endif; ?>

    <?php if (!$can_manage): ?>
        <div class="alert alert-info">You can view this page but not change it. Ask a Super Admin for the <strong>Manage Site Settings</strong> permission.</div>
    <?php endif; ?>

    <?php echo form_open_multipart('site_settings/update', array('autocomplete' => 'off')); ?>
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-general" type="button" role="tab"><i class="bi bi-info-circle"></i> General</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-seo" type="button" role="tab"><i class="bi bi-search"></i> SEO</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-branding" type="button" role="tab"><i class="bi bi-image"></i> Logo &amp; Images</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-contact" type="button" role="tab"><i class="bi bi-geo-alt"></i> Address &amp; Contact</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-social" type="button" role="tab"><i class="bi bi-share"></i> Social Media</button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- General -->
            <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bi bi-info-circle"></i> Website Identity</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="site_name" class="form-label">Website Name *</label>
                                <input type="text" class="form-control" id="site_name" name="site_name" maxlength="150" required
                                    value="<?php echo $s('site_name', 'BODARE Pension House'); ?>" <?php echo $ro; ?>>
                                <small class="text-muted">Shown in the site header, browser tabs and search results.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="site_short_name" class="form-label">Short Name</label>
                                <input type="text" class="form-control" id="site_short_name" name="site_short_name" maxlength="50"
                                    value="<?php echo $s('site_short_name', 'BODARE'); ?>" <?php echo $ro; ?>>
                                <small class="text-muted">Used where space is tight, such as the home-screen app title.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="legal_name" class="form-label">Company / Legal Name</label>
                                <input type="text" class="form-control" id="legal_name" name="legal_name" maxlength="200"
                                    value="<?php echo $s('legal_name'); ?>" <?php echo $ro; ?>>
                                <small class="text-muted">Shown in the footer copyright line.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tagline" class="form-label">Tagline</label>
                                <input type="text" class="form-control" id="tagline" name="tagline" maxlength="255"
                                    value="<?php echo $s('tagline'); ?>" <?php echo $ro; ?>>
                            </div>
                            <div class="col-12 mb-0">
                                <label for="about_text" class="form-label">About Us (footer)</label>
                                <textarea class="form-control" id="about_text" name="about_text" rows="3" maxlength="1000" <?php echo $ro; ?>><?php echo $s('about_text'); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO -->
            <div class="tab-pane fade" id="tab-seo" role="tabpanel">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="bi bi-search"></i> Search Engine Optimization</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="meta_title" class="form-label">Homepage Title</label>
                            <input type="text" class="form-control js-count" id="meta_title" name="meta_title" maxlength="120" data-limit="60"
                                value="<?php echo $s('meta_title'); ?>" placeholder="Leave blank to keep the built-in homepage title" <?php echo $ro; ?>>
                            <small class="text-muted"><span class="js-count-out" data-for="meta_title"></span> Aim for 50–60 characters.</small>
                        </div>
                        <div class="mb-3">
                            <label for="meta_description" class="form-label">Meta Description</label>
                            <textarea class="form-control js-count" id="meta_description" name="meta_description" rows="3" maxlength="320" data-limit="160"
                                placeholder="Leave blank to use the tagline" <?php echo $ro; ?>><?php echo $s('meta_description'); ?></textarea>
                            <small class="text-muted"><span class="js-count-out" data-for="meta_description"></span> Used on the homepage and on pages without their own description. Aim for 120–160 characters.</small>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="meta_keywords" class="form-label">Keywords</label>
                                <textarea class="form-control" id="meta_keywords" name="meta_keywords" rows="3" <?php echo $ro; ?>><?php echo $s('meta_keywords'); ?></textarea>
                                <small class="text-muted">Separate with commas.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="meta_tags" class="form-label">Tags</label>
                                <textarea class="form-control" id="meta_tags" name="meta_tags" rows="3" placeholder="e.g. hotel, Bohol, budget stay" <?php echo $ro; ?>><?php echo $s('meta_tags'); ?></textarea>
                                <small class="text-muted">Separate with commas. Added to the keywords meta tag.</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="seo_noindex" name="seo_noindex" value="1"
                                    <?php echo $noindex ? 'checked' : ''; ?> <?php echo $ro; ?>>
                                <label class="form-check-label" for="seo_noindex">
                                    Hide the website from search engines (noindex)
                                </label>
                            </div>
                            <small class="text-muted">Only turn this on for a staging copy. On the live site it removes the pages from Google.</small>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="google_site_verification" class="form-label">Google Search Console Verification</label>
                                <input type="text" class="form-control" id="google_site_verification" name="google_site_verification"
                                    value="<?php echo $s('google_site_verification'); ?>" placeholder="Code or full meta tag" <?php echo $ro; ?>>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="bing_site_verification" class="form-label">Bing Webmaster Verification</label>
                                <input type="text" class="form-control" id="bing_site_verification" name="bing_site_verification"
                                    value="<?php echo $s('bing_site_verification'); ?>" placeholder="Code or full meta tag" <?php echo $ro; ?>>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="google_analytics_id" class="form-label">Google Analytics ID</label>
                                <input type="text" class="form-control" id="google_analytics_id" name="google_analytics_id"
                                    value="<?php echo $s('google_analytics_id'); ?>" placeholder="G-XXXXXXXXXX" <?php echo $ro; ?>>
                            </div>
                        </div>
                        <small class="text-muted">Sitemap: <code><?php echo htmlspecialchars($public_url, ENT_QUOTES, 'UTF-8'); ?>/sitemap.xml</code></small>
                    </div>
                </div>
            </div>

            <!-- Branding -->
            <div class="tab-pane fade" id="tab-branding" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="bi bi-building"></i> Company Logo</h6>
                            </div>
                            <div class="card-body">
                                <div class="border rounded d-flex align-items-center justify-content-center mb-3 bg-light" style="height: 140px;">
                                    <?php if ($logo_url !== ''): ?>
                                        <img src="<?php echo htmlspecialchars($logo_url, ENT_QUOTES, 'UTF-8'); ?>" alt="Logo" style="max-height: 120px; max-width: 90%; object-fit: contain;">
                                    <?php else: ?>
                                        <span class="text-muted small">Using the built-in logo (img/logo.png)</span>
                                    <?php endif; ?>
                                </div>
                                <input type="file" class="form-control" name="logo" accept="image/png,image/jpeg,image/webp,image/gif" <?php echo $ro; ?>>
                                <small class="text-muted">PNG with a transparent background works best. JPG, PNG, WEBP or GIF, max 2MB.</small>
                                <?php if ($can_manage && $logo_url !== ''): ?>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="remove_logo" name="remove_logo" value="1">
                                    <label class="form-check-label small" for="remove_logo">Remove uploaded logo (go back to the built-in one)</label>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="bi bi-image"></i> Social Share Image</h6>
                            </div>
                            <div class="card-body">
                                <div class="border rounded d-flex align-items-center justify-content-center mb-3 bg-light" style="height: 140px;">
                                    <?php if ($og_url !== ''): ?>
                                        <img src="<?php echo htmlspecialchars($og_url, ENT_QUOTES, 'UTF-8'); ?>" alt="Share image" style="max-height: 120px; max-width: 90%; object-fit: contain;">
                                    <?php else: ?>
                                        <span class="text-muted small">Using the built-in image (img/og-default.jpg)</span>
                                    <?php endif; ?>
                                </div>
                                <input type="file" class="form-control" name="og_image" accept="image/png,image/jpeg,image/webp" <?php echo $ro; ?>>
                                <small class="text-muted">Preview shown when a page without its own image is shared on Facebook, Messenger and similar apps. 1200×630 recommended, max 5MB.</small>
                                <?php if ($can_manage && $og_url !== ''): ?>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="remove_og_image" name="remove_og_image" value="1">
                                    <label class="form-check-label small" for="remove_og_image">Remove uploaded image (go back to the built-in one)</label>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address & Contact -->
            <div class="tab-pane fade" id="tab-contact" role="tabpanel">
                <div class="card mb-4">
                    <div class="card-header bg-warning">
                        <h6 class="mb-0"><i class="bi bi-geo-alt"></i> Address</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="street_address" class="form-label">Street Address</label>
                                <input type="text" class="form-control" id="street_address" name="street_address" maxlength="255"
                                    value="<?php echo $s('street_address'); ?>" <?php echo $ro; ?>>
                                <small class="text-muted">Building and street. On the website, the text after the first comma goes on a second line.</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="address_locality" class="form-label">City / Municipality</label>
                                <input type="text" class="form-control" id="address_locality" name="address_locality" maxlength="100"
                                    value="<?php echo $s('address_locality'); ?>" <?php echo $ro; ?>>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="address_region" class="form-label">Province</label>
                                <input type="text" class="form-control" id="address_region" name="address_region" maxlength="100"
                                    value="<?php echo $s('address_region'); ?>" <?php echo $ro; ?>>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="postal_code" class="form-label">ZIP Code</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" maxlength="20"
                                    value="<?php echo $s('postal_code'); ?>" <?php echo $ro; ?>>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="address_country" class="form-label">Country</label>
                                <input type="text" class="form-control text-uppercase" id="address_country" name="address_country" maxlength="2"
                                    value="<?php echo $s('address_country', 'PH'); ?>" <?php echo $ro; ?>>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="geo_latitude" class="form-label">Latitude</label>
                                <input type="text" class="form-control" id="geo_latitude" name="geo_latitude"
                                    value="<?php echo $s('geo_latitude'); ?>" <?php echo $ro; ?>>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="geo_longitude" class="form-label">Longitude</label>
                                <input type="text" class="form-control" id="geo_longitude" name="geo_longitude"
                                    value="<?php echo $s('geo_longitude'); ?>" <?php echo $ro; ?>>
                            </div>
                            <div class="col-12 mb-3">
                                <small class="text-muted">The map on the Contact page is pinned to these coordinates. To get them, right-click the building in Google Maps and click the numbers at the top of the menu.</small>
                            </div>
                            <div class="col-md-6 mb-0">
                                <label for="map_url" class="form-label">Google Maps Link</label>
                                <input type="url" class="form-control" id="map_url" name="map_url"
                                    value="<?php echo $s('map_url'); ?>" placeholder="Leave blank to search the address" <?php echo $ro; ?>>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-warning">
                        <h6 class="mb-0"><i class="bi bi-telephone"></i> Contact Info</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contact_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="contact_email" name="contact_email"
                                    value="<?php echo $s('contact_email'); ?>" <?php echo $ro; ?>>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="business_hours" class="form-label">Business Hours</label>
                                <input type="text" class="form-control" id="business_hours" name="business_hours" maxlength="255"
                                    value="<?php echo $s('business_hours'); ?>" <?php echo $ro; ?>>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="contact_phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="contact_phone" name="contact_phone" maxlength="50"
                                    value="<?php echo $s('contact_phone'); ?>" placeholder="0950 533 7480" <?php echo $ro; ?>>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="contact_phone_e164" class="form-label">Phone (international format)</label>
                                <input type="text" class="form-control" id="contact_phone_e164" name="contact_phone_e164" maxlength="20"
                                    value="<?php echo $s('contact_phone_e164'); ?>" placeholder="+639505337480" <?php echo $ro; ?>>
                                <small class="text-muted">Used for tap-to-call links. Leave blank to work it out from the phone number.</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="contact_phone_alt" class="form-label">Alternate Phone / Landline</label>
                                <input type="text" class="form-control" id="contact_phone_alt" name="contact_phone_alt" maxlength="50"
                                    value="<?php echo $s('contact_phone_alt'); ?>" <?php echo $ro; ?>>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social -->
            <div class="tab-pane fade" id="tab-social" role="tabpanel">
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0"><i class="bi bi-share"></i> Social Media Links</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Filled-in links appear as icons in the website footer and are listed for search engines. Leave a field blank to hide it.</p>
                        <div class="row">
                            <?php
                            $socials = array(
                                'facebook_url' => array('Facebook Page', 'bi-facebook', 'https://www.facebook.com/yourpage'),
                                'messenger_url' => array('Messenger (chat button)', 'bi-messenger', 'https://m.me/yourpage'),
                                'instagram_url' => array('Instagram', 'bi-instagram', 'https://www.instagram.com/yourpage'),
                                'tiktok_url' => array('TikTok', 'bi-tiktok', 'https://www.tiktok.com/@yourpage'),
                                'youtube_url' => array('YouTube', 'bi-youtube', 'https://www.youtube.com/@yourchannel'),
                                'x_url' => array('X (Twitter)', 'bi-twitter-x', 'https://x.com/yourpage'),
                                'linkedin_url' => array('LinkedIn', 'bi-linkedin', 'https://www.linkedin.com/company/yourpage')
                            );
                            foreach ($socials as $key => $meta): ?>
                            <div class="col-md-6 mb-3">
                                <label for="<?php echo $key; ?>" class="form-label"><i class="bi <?php echo $meta[1]; ?>"></i> <?php echo $meta[0]; ?></label>
                                <input type="url" class="form-control" id="<?php echo $key; ?>" name="<?php echo $key; ?>"
                                    value="<?php echo $s($key); ?>" placeholder="<?php echo $meta[2]; ?>" <?php echo $ro; ?>>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($can_manage && $table_ready): ?>
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="<?php echo base_url('dashboard'); ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Save Site Settings
            </button>
        </div>
        <?php endif; ?>
    <?php echo form_close(); ?>
</div>

<script>
(function () {
    document.querySelectorAll('.js-count').forEach(function (el) {
        var out = document.querySelector('.js-count-out[data-for="' + el.id + '"]');
        var limit = parseInt(el.getAttribute('data-limit'), 10) || 0;
        if (!out) { return; }
        var update = function () {
            var n = el.value.length;
            out.textContent = n + ' characters.';
            out.className = 'js-count-out' + (limit && n > limit ? ' text-danger' : '');
        };
        el.addEventListener('input', update);
        update();
    });

    var form = document.querySelector('form[action$="site_settings/update"]');
    if (form) {
        form.addEventListener('invalid', function (e) {
            var pane = e.target.closest('.tab-pane');
            if (pane && !pane.classList.contains('active') && window.bootstrap) {
                var btn = document.querySelector('[data-bs-target="#' + pane.id + '"]');
                if (btn) { bootstrap.Tab.getOrCreateInstance(btn).show(); }
            }
        }, true);
    }
})();
</script>

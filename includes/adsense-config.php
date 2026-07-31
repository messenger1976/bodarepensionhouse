<?php
/**
 * Google AdSense configuration for BODARE Pension House.
 *
 * Setup:
 * 1. Add this site in AdSense (Sites) if not already listed
 * 2. Confirm publisher_id matches your ca-pub-… account
 * 3. Keep ads.txt at the web root in sync with the publisher ID
 * 4. (Optional) Create ad units and paste slot IDs below
 * 5. Set enabled to true when ready to serve ads
 *
 * Auto ads work with only the publisher ID. Manual slot IDs unlock
 * fixed placements on room / amenities / home pages.
 */
return [
    // Master switch
    'enabled' => true,

    // From AdSense → Account → Account information (format: ca-pub-################)
    'publisher_id' => 'ca-pub-1060012311865896',

    // Auto ads: Google places ads when enabled in the AdSense UI
    'auto_ads' => true,

    // Manual ad unit slot IDs (Ads → By ad unit → Get code → data-ad-slot)
    // Leave empty until you create units — slots will not render.
    'slots' => [
        'sidebar' => '',       // Below reserve widget on room-detail
        'in_content' => '',    // Mid-page on home / amenities / rooms
        'footer' => '',        // Above footer on content pages
    ],
];

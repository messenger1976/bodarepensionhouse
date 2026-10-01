-- Indexes for the admin Bookings list (status tabs + counts, "Booked on" date filter).
-- The stay / check-in / check-out filters already use idx_check_dates (check_in, check_out, status).
-- Safe to run once; re-running reports "Duplicate key name" and changes nothing.

ALTER TABLE `bookings`
  ADD KEY `idx_bookings_status_id` (`status`, `id`),
  ADD KEY `idx_bookings_created_at` (`created_at`),
  ADD KEY `idx_bookings_check_out` (`check_out`);

<?php
$pageSeo = [
    'title' => 'Rooms & Rates | BODARE Pension House Tagbilaran',
    'description' => 'Browse dormitory, standard, deluxe, ambassador, and executive rooms at BODARE Pension House in Tagbilaran City. Compare rates and book your stay online.',
    'canonical_path' => 'rooms.php',
    'og_image' => 'img/deluxea.jpg',
];
include __DIR__ . '/includes/site-head.php';
?>
<body>

    <?php
    $headerConfig = [
        'cart_style' => 'display: flex; color: white !important;',
        'cart_icon_style' => 'font-size: 1.5rem; color: white !important;'
    ];
    include __DIR__ . '/includes/site-header.php';
?>


    <section class="page-header">
        <div class="page-header-content">
            <h1>Our Rooms</h1>
            <p>Find the perfect space for your stay.</p>
        </div>
    </section>

    <main class="rooms-page-section">
        <div class="container">
            <div class="rooms-toolbar">
                <form class="rooms-filter-form" id="rooms-filter-form">
                    <div class="form-group">
                        <label for="rooms-start-date">Start Date</label>
                        <input type="date" id="rooms-start-date" name="checkin" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="rooms-end-date">End Date</label>
                        <input type="date" id="rooms-end-date" name="checkout" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="rooms-pax">Pax/Room</label>
                        <select id="rooms-pax" name="guests">
                            <?php for ($pax = 1; $pax <= 12; $pax++): ?>
                                <option value="<?php echo $pax; ?>"<?php echo $pax === 2 ? ' selected' : ''; ?>><?php echo $pax; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="rooms-filter-actions">
                        <button type="submit" class="cta-button form-button">Filter</button>
                        <button type="button" class="cta-button-secondary" id="rooms-clear-filter">Clear</button>
                    </div>
                </form>

                <div class="rooms-toolbar-bottom">
                    <p class="rooms-results-summary" id="rooms-results-summary">Showing all rooms. Select dates to check availability.</p>
                    <div class="rooms-view-toggle">
                        <span>Display Type:</span>
                        <div class="rooms-view-buttons" role="group" aria-label="Room display type">
                            <button type="button" class="rooms-view-btn is-active" data-view="row" aria-pressed="true">Details/Row</button>
                            <button type="button" class="rooms-view-btn" data-view="thumbnail" aria-pressed="false">Thumbnail</button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="rooms-container" class="rooms-list" data-view="row">
                <div class="text-center" style="padding: 40px;">
                    <p>Loading rooms...</p>
                </div>
            </div>

            <noscript>
                <div class="room-grid" style="margin-top: 2rem;">
                    <?php
                    require_once __DIR__ . '/includes/site-config.php';
                    foreach (bodare_room_codes() as $roomKey):
                        $roomMeta = bodare_live_room($roomKey);
                        if (!$roomMeta) {
                            continue;
                        }
                    ?>
                        <div class="room-card">
                            <a href="room-detail.php?room=<?php echo htmlspecialchars($roomKey, ENT_QUOTES, 'UTF-8'); ?>">
                                <img src="<?php echo htmlspecialchars($roomMeta['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($roomMeta['title'] . ' at BODARE Pension House', ENT_QUOTES, 'UTF-8'); ?>">
                                <h2><?php echo htmlspecialchars($roomMeta['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                            </a>
                            <p class="room-details"><?php echo htmlspecialchars($roomMeta['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="room-details"><strong>From ₱<?php echo number_format($roomMeta['price']); ?></strong> <?php echo htmlspecialchars($roomMeta['price_unit'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </noscript>

            <?php adsense_render_unit('in_content', 'adsense-in-content'); ?>
        </div>
    </main>

<?php
    include __DIR__ . '/includes/site-footer.php';
?>

    <script src="api-config.js"></script>
    <script src="script.js?v=<?php echo filemtime(__DIR__ . '/script.js'); ?>"></script>
    <script>
        (function () {
            const DISPLAY_KEY = 'roomsDisplayType';
            const roomsContainer = document.getElementById('rooms-container');
            const filterForm = document.getElementById('rooms-filter-form');
            const startInput = document.getElementById('rooms-start-date');
            const endInput = document.getElementById('rooms-end-date');
            const paxInput = document.getElementById('rooms-pax');
            const summaryEl = document.getElementById('rooms-results-summary');
            const clearBtn = document.getElementById('rooms-clear-filter');

            let allRooms = [];
            let availabilityById = null;
            let activeFilter = null;

            function escapeHtml(value) {
                return String(value == null ? '' : value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function formatDateLocal(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function parseDateLocal(value) {
                const parts = String(value).split('-');
                return new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
            }

            function formatPrettyDate(value) {
                return parseDateLocal(value).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            }

            function nightCount(checkIn, checkOut) {
                const nights = Math.ceil((parseDateLocal(checkOut) - parseDateLocal(checkIn)) / (1000 * 60 * 60 * 24));
                return Math.max(nights, 1);
            }

            function roomCodeOf(room) {
                return room.room_code || String(room.room_name || '').toLowerCase().replace(/\s+/g, '');
            }

            function bedTypeOf(room) {
                return room.bed_type || (/dorm/i.test(`${room.room_type || ''} ${room.room_name || ''}`)
                    ? 'Bunk beds'
                    : 'Normal Beds');
            }

            function amenityList(room) {
                const raw = room.amenities || 'Wifi, Television, Private Bathroom';
                return String(raw)
                    .split(/[,;\n]+/)
                    .map(item => item.trim())
                    .filter(Boolean);
            }

            function detailUrl(room, filter) {
                const params = new URLSearchParams({ room: roomCodeOf(room) });
                if (filter) {
                    params.set('checkin', filter.checkIn);
                    params.set('checkout', filter.checkOut);
                    params.set('guests', filter.guests);
                }
                return `room-detail.php?${params.toString()}`;
            }

            function currentView() {
                const stored = localStorage.getItem(DISPLAY_KEY);
                return stored === 'thumbnail' ? 'thumbnail' : 'row';
            }

            function setView(view) {
                const next = view === 'thumbnail' ? 'thumbnail' : 'row';
                localStorage.setItem(DISPLAY_KEY, next);
                roomsContainer.classList.toggle('is-thumbnail', next === 'thumbnail');
                roomsContainer.dataset.view = next;
                document.querySelectorAll('.rooms-view-btn').forEach(btn => {
                    const active = btn.dataset.view === next;
                    btn.classList.toggle('is-active', active);
                    btn.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
            }

            function setDateBounds() {
                const today = formatDateLocal(new Date());
                startInput.min = today;
                if (!endInput.min) {
                    const tomorrow = new Date();
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    endInput.min = formatDateLocal(tomorrow);
                }
            }

            function syncCheckoutMin() {
                if (!startInput.value) {
                    return;
                }
                const checkinDate = parseDateLocal(startInput.value);
                const nextDay = new Date(checkinDate);
                nextDay.setDate(nextDay.getDate() + 1);
                const nextDayString = formatDateLocal(nextDay);
                endInput.min = nextDayString;
                if (endInput.value && parseDateLocal(endInput.value) <= checkinDate) {
                    endInput.value = nextDayString;
                }
            }

            function prefillFromIncoming() {
                const urlParams = new URLSearchParams(window.location.search);
                let checkIn = urlParams.get('checkin');
                let checkOut = urlParams.get('checkout');
                let guests = urlParams.get('guests') || urlParams.get('pax');

                const stored = localStorage.getItem('availabilityData');
                if (stored) {
                    try {
                        const data = JSON.parse(stored);
                        checkIn = checkIn || data.checkIn;
                        checkOut = checkOut || data.checkOut;
                        guests = guests || data.guests;
                        localStorage.removeItem('availabilityData');
                    } catch (error) {
                        console.error('Error parsing availability data:', error);
                    }
                }

                if (checkIn) startInput.value = checkIn;
                if (checkOut) endInput.value = checkOut;
                if (guests) paxInput.value = String(Math.min(12, Math.max(1, parseInt(guests, 10) || 2)));
                syncCheckoutMin();

                return Boolean(startInput.value && endInput.value);
            }

            function updateSummary(visibleCount, filter, availableCount) {
                if (!filter) {
                    summaryEl.innerHTML = `Showing <strong>${visibleCount}</strong> room${visibleCount === 1 ? '' : 's'}. Select dates to check availability.`;
                    return;
                }

                const nights = nightCount(filter.checkIn, filter.checkOut);
                const dates = `${formatPrettyDate(filter.checkIn)} – ${formatPrettyDate(filter.checkOut)}`;
                if (availableCount > 0) {
                    summaryEl.innerHTML = `<strong>${availableCount}</strong> room type${availableCount === 1 ? '' : 's'} available for ${dates} · ${nights} night${nights === 1 ? '' : 's'} · ${filter.guests} pax/room`;
                } else {
                    summaryEl.innerHTML = `No rooms available for ${dates} with ${filter.guests} pax/room. Try different dates or a smaller party.`;
                }
            }

            function roomStatus(room, filter) {
                if (!filter) {
                    return { kind: 'open', label: '', remaining: null };
                }

                const capacity = parseInt(room.capacity, 10) || 0;
                if (capacity && capacity < parseInt(filter.guests, 10)) {
                    return { kind: 'capacity', label: `Fits up to ${capacity} guests`, remaining: 0 };
                }

                const match = availabilityById && availabilityById[String(room.id)];
                if (match) {
                    const remaining = parseInt(match.remaining_rooms, 10);
                    const label = Number.isFinite(remaining) && remaining > 0
                        ? `${remaining} available`
                        : 'Available';
                    return { kind: 'available', label, remaining };
                }

                return { kind: 'booked', label: 'Fully booked for these dates', remaining: 0 };
            }

            function renderRooms() {
                if (!allRooms.length) {
                    roomsContainer.innerHTML = '<div class="rooms-empty"><p>No rooms available at the moment. Please check back later.</p></div>';
                    updateSummary(0, activeFilter, 0);
                    return;
                }

                const ranked = allRooms.map(room => {
                    const status = roomStatus(room, activeFilter);
                    const rank = status.kind === 'available' || status.kind === 'open' ? 0 : (status.kind === 'booked' ? 1 : 2);
                    return { room, status, rank };
                }).sort((a, b) => a.rank - b.rank || String(a.room.room_name).localeCompare(String(b.room.room_name)));

                const visible = ranked.filter(item => item.status.kind !== 'capacity');
                const availableCount = visible.filter(item => item.status.kind === 'available' || item.status.kind === 'open').length;

                if (!visible.length) {
                    roomsContainer.innerHTML = '<div class="rooms-empty"><p>No rooms match this party size.</p><p>Lower Pax/Room or choose different dates.</p></div>';
                    updateSummary(0, activeFilter, 0);
                    return;
                }

                roomsContainer.innerHTML = visible.map(({ room, status }) => {
                    const roomCode = roomCodeOf(room);
                    const imagePath = resolveRoomImage(room, roomCode);
                    const isPerHead = /dormitory/i.test(`${room.room_type || ''} ${room.room_name || ''}`);
                    const priceLabel = room.price
                        ? `₱${parseFloat(room.price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ${isPerHead ? 'per head' : 'per night'}`
                        : 'Price on request';
                    const capacityText = room.capacity
                        ? `Good for ${room.capacity} person${Number(room.capacity) > 1 ? 's' : ''}`
                        : 'Capacity varies';
                    const amenities = amenityList(room);
                    const description = room.description || `${room.room_name} offers a comfortable and well-appointed space for your stay.`;
                    const href = detailUrl(room, activeFilter);
                    const unavailable = status.kind === 'booked';
                    const ctaLabel = unavailable ? 'Not available' : (activeFilter ? 'Select Room' : 'View Details');
                    const badgeClass = status.kind === 'available' ? 'is-available' : (status.kind === 'booked' ? 'is-booked' : '');
                    const badgeHtml = status.label
                        ? `<span class="room-availability-badge ${badgeClass}">${escapeHtml(status.label)}</span>`
                        : '';

                    return `
                        <article class="room-detail-card${unavailable ? ' is-unavailable' : ''}">
                            <a class="room-card-media" href="${escapeHtml(href)}" aria-label="${escapeHtml(room.room_name || 'Room')}">
                                <img src="${escapeHtml(imagePath)}" alt="${escapeHtml(room.room_name || 'Room')}" onerror="this.onerror=null; this.src='img/og-default.jpg'">
                            </a>
                            <div class="room-info">
                                ${badgeHtml}
                                <h2>${escapeHtml(room.room_name || 'Room')}</h2>
                                <p class="room-price">${escapeHtml(priceLabel)}</p>
                                <p class="room-card-desc">${escapeHtml(description)}</p>
                                <div class="room-card-meta">
                                    <span><i class="bi bi-people"></i>${escapeHtml(capacityText)}</span>
                                    <span><i class="bi bi-lamp"></i>${escapeHtml(bedTypeOf(room))}</span>
                                </div>
                                <ul>
                                    <li><strong>Services:</strong> ${escapeHtml(amenities.join(', '))}</li>
                                </ul>
                                <div class="room-card-cta">
                                    <a href="${escapeHtml(href)}" class="cta-button">${ctaLabel}</a>
                                </div>
                            </div>
                        </article>
                    `;
                }).join('');

                updateSummary(visible.length, activeFilter, activeFilter ? availableCount : visible.length);
            }

            async function applyFilter(checkIn, checkOut, guests) {
                if (!checkIn || !checkOut) {
                    alert('Please select both a start date and an end date.');
                    return;
                }
                if (parseDateLocal(checkOut) <= parseDateLocal(checkIn)) {
                    alert('End date must be after the start date.');
                    return;
                }

                summaryEl.textContent = 'Checking availability...';
                try {
                    const response = await API.booking.checkAvailability(checkIn, checkOut, guests);
                    availabilityById = {};
                    (response.rooms || []).forEach(room => {
                        availabilityById[String(room.id)] = room;
                    });
                    activeFilter = { checkIn, checkOut, guests: String(guests || '2') };

                    const params = new URLSearchParams(window.location.search);
                    params.set('checkin', checkIn);
                    params.set('checkout', checkOut);
                    params.set('guests', activeFilter.guests);
                    const nextUrl = `${window.location.pathname}?${params.toString()}`;
                    window.history.replaceState({}, '', nextUrl);

                    renderRooms();
                } catch (error) {
                    console.error('Error checking availability:', error);
                    summaryEl.textContent = 'Unable to check availability. Please try again.';
                }
            }

            function clearFilter() {
                startInput.value = '';
                endInput.value = '';
                paxInput.value = '2';
                availabilityById = null;
                activeFilter = null;
                window.history.replaceState({}, '', window.location.pathname);
                setDateBounds();
                renderRooms();
            }

            async function loadRooms() {
                try {
                    if (typeof API === 'undefined') {
                        throw new Error('API configuration not loaded');
                    }

                    const response = await API.booking.getRooms();
                    allRooms = (response.success && response.rooms) ? response.rooms : [];
                    renderRooms();

                    if (prefillFromIncoming()) {
                        await applyFilter(startInput.value, endInput.value, paxInput.value);
                    }
                } catch (error) {
                    console.error('Error loading rooms:', error);
                    roomsContainer.innerHTML = '<div class="rooms-empty"><p>Unable to load rooms. Please refresh the page or contact us for assistance.</p></div>';
                }
            }

            document.querySelectorAll('.rooms-view-btn').forEach(btn => {
                btn.addEventListener('click', () => setView(btn.dataset.view));
            });

            startInput.addEventListener('change', syncCheckoutMin);
            filterForm.addEventListener('submit', (event) => {
                event.preventDefault();
                applyFilter(startInput.value, endInput.value, paxInput.value);
            });
            clearBtn.addEventListener('click', clearFilter);

            setDateBounds();
            setView(currentView());
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', loadRooms);
            } else {
                loadRooms();
            }
        })();
    </script>
</body>
</html>

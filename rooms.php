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
            <!-- Availability Results Banner -->
            <div id="availability-banner" style="display: none; background: #f0f0f0; padding: 20px; margin-bottom: 30px; border-radius: 8px; border-left: 4px solid #b2945b;">
                <h3 style="margin-top: 0; color: #b2945b;">Availability Results</h3>
                <p id="availability-message" style="margin-bottom: 10px;"></p>
                <p id="availability-dates" style="font-size: 0.9em; color: #666;"></p>
            </div>

            <!-- Rooms will be dynamically loaded here -->
            <div id="rooms-container">
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

        </div>
    </main>

<?php
    include __DIR__ . '/includes/site-footer.php';
?>

    <script src="api-config.js"></script>
    <script src="script.js?v=<?php echo filemtime(__DIR__ . '/script.js'); ?>"></script>
    <script>
        // Load and display rooms dynamically
        async function loadRooms() {
            const roomsContainer = document.getElementById('rooms-container');
            
            try {
                if (typeof API === 'undefined') {
                    throw new Error('API configuration not loaded');
                }
                
                // Fetch all active rooms
                const response = await API.booking.getRooms();
                
                if (response.success && response.rooms && response.rooms.length > 0) {
                    roomsContainer.innerHTML = '';
                    
                    // Display each room
                    response.rooms.forEach(room => {
                        const roomCode = room.room_code || room.room_name.toLowerCase().replace(/\s+/g, '');
                        const imagePath = resolveRoomImage(room, roomCode);
                        
                        // Format price
                        const priceDisplay = room.price ? `₱${parseFloat(room.price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} per night` : 'Price on request';
                        
                        // Format capacity
                        const capacityText = room.capacity ? `Good for ${room.capacity} person${room.capacity > 1 ? 's' : ''}` : 'Capacity varies';
                        
                        // Format amenities
                        const amenitiesText = room.amenities || 'Complimentary Wifi, Television, Private Bathroom';
                        
                        // Description
                        const description = room.description || `${room.room_name} offers a comfortable and well-appointed space for your stay.`;
                        
                        // Create room card
                        const roomCard = document.createElement('div');
                        roomCard.className = 'room-detail-card';
                        roomCard.innerHTML = `
                            <img src="${imagePath}" alt="${room.room_name}" onerror="this.onerror=null; this.src='img/og-default.jpg'">
                            <div class="room-info">
                                <h2>${room.room_name || 'Room'}</h2>
                                <p class="room-price">${priceDisplay}</p>
                                <p>${description}</p>
                                <ul>
                                    <li><strong>Capacity:</strong> ${capacityText}</li>
                                    <li><strong>Bed Type:</strong> Normal Beds</li>
                                    <li><strong>Services:</strong> ${amenitiesText}</li>
                                </ul>
                                <a href="room-detail.php?room=${roomCode}" class="cta-button">View Details</a>
                            </div>
                        `;
                        
                        roomsContainer.appendChild(roomCard);
                    });
                } else {
                    roomsContainer.innerHTML = '<div class="text-center" style="padding: 40px;"><p>No rooms available at the moment. Please check back later.</p></div>';
                }
            } catch (error) {
                console.error('Error loading rooms:', error);
                roomsContainer.innerHTML = '<div class="text-center" style="padding: 40px;"><p>Unable to load rooms. Please refresh the page or contact us for assistance.</p></div>';
            }
        }
        
        // Display availability results if redirected from index page
        document.addEventListener('DOMContentLoaded', () => {
            // Load rooms first
            loadRooms();
            const availabilityBanner = document.getElementById('availability-banner');
            const availabilityMessage = document.getElementById('availability-message');
            const availabilityDates = document.getElementById('availability-dates');
            
            // Check for availability data in localStorage
            const availabilityData = localStorage.getItem('availabilityData');
            const urlParams = new URLSearchParams(window.location.search);
            
            if (availabilityData || (urlParams.get('checkin') && urlParams.get('checkout'))) {
                let checkIn, checkOut, guests, rooms = [];
                
                if (availabilityData) {
                    try {
                        const data = JSON.parse(availabilityData);
                        checkIn = data.checkIn;
                        checkOut = data.checkOut;
                        guests = data.guests;
                        rooms = data.rooms || [];
                        
                        // Clear the data after displaying
                        localStorage.removeItem('availabilityData');
                    } catch (e) {
                        console.error('Error parsing availability data:', e);
                    }
                } else {
                    // Get from URL parameters
                    checkIn = urlParams.get('checkin');
                    checkOut = urlParams.get('checkout');
                    guests = urlParams.get('guests');
                }
                
                if (checkIn && checkOut) {
                    // Format dates for display
                    const checkInDate = new Date(checkIn + 'T00:00:00');
                    const checkOutDate = new Date(checkOut + 'T00:00:00');
                    const formattedCheckIn = checkInDate.toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    });
                    const formattedCheckOut = checkOutDate.toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    });
                    
                    // Calculate nights
                    const nights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));
                    
                    // Display availability information
                    availabilityDates.textContent = `Check-in: ${formattedCheckIn} | Check-out: ${formattedCheckOut} | ${nights} night${nights !== 1 ? 's' : ''} | ${guests || '2'} guest${guests !== '1' ? 's' : ''}`;
                    
                    if (rooms && rooms.length > 0) {
                        const roomNames = rooms.map(room => room.room_name || room.room_type || 'Room').join(', ');
                        availabilityMessage.innerHTML = `<strong>${rooms.length} room${rooms.length !== 1 ? 's' : ''} available:</strong> ${roomNames}`;
                        availabilityBanner.style.borderLeftColor = '#4caf50';
                    } else {
                        availabilityMessage.innerHTML = '<strong>Checking availability...</strong> Please wait while we verify room availability for your selected dates.';
                        availabilityBanner.style.borderLeftColor = '#ff9800';
                        
                        // If we have URL params but no rooms data, try to fetch it
                        if (urlParams.get('checkin') && typeof API !== 'undefined') {
                            API.booking.checkAvailability(checkIn, checkOut, guests)
                                .then(response => {
                                    if (response.success && response.rooms) {
                                        const roomNames = response.rooms.map(room => room.room_name || room.room_type || 'Room').join(', ');
                                        if (response.rooms.length > 0) {
                                            availabilityMessage.innerHTML = `<strong>${response.rooms.length} room${response.rooms.length !== 1 ? 's' : ''} available:</strong> ${roomNames}`;
                                            availabilityBanner.style.borderLeftColor = '#4caf50';
                                        } else {
                                            availabilityMessage.innerHTML = '<strong>No rooms available</strong> for the selected dates. Please try different dates.';
                                            availabilityBanner.style.borderLeftColor = '#f44336';
                                        }
                                    }
                                })
                                .catch(error => {
                                    console.error('Error checking availability:', error);
                                    availabilityMessage.innerHTML = '<strong>Unable to check availability.</strong> Please try again or contact us.';
                                    availabilityBanner.style.borderLeftColor = '#f44336';
                                });
                        }
                    }
                    
                    availabilityBanner.style.display = 'block';
                    
                    // Scroll to banner
                    availabilityBanner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }
        });
    </script>
</body>
</html>



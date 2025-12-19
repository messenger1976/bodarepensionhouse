// --- DATABASE FOR ROOM DETAILS ---
const roomData = {
    'dormitory': {
        title: 'Dormitory',
        price: 299,
        priceUnit: 'per head',
        capacity: 'Minimum 8 persons',
        description: 'Ideal for groups and budget travelers, our dormitory offers a comfortable and social atmosphere without compromising on essential amenities.',
        imageUrl: 'img/dormitory.jpg',
        gridImages: [
            'img/dormitory.jpg', 
            'img/dormitory.jpg', 
            'img/dormitory.jpg' 
        ]
    },
    'executive': {
        title: 'Executive Room',
        price: 1999,
        priceUnit: 'per night',
        capacity: 'Good for 4 persons',
        description: 'Spacious and elegantly appointed, the Executive Room is designed for guests seeking extra comfort and space, perfect for families or business travelers.',
        imageUrl: 'img/executive.jpg',
        gridImages: [
            'img/executive.jpg', 
            'img/executive.jpg',
            'img/executive.jpg'  
        ]
    },
    'ambassador': {
        title: 'Ambassador Room',
        price: 1399,
        priceUnit: 'per night',
        capacity: 'Good for 3 persons',
        description: 'A perfect blend of comfort and value, the Ambassador Room provides a cozy retreat for couples or small families after a day of exploration.',
        imageUrl: 'img/ambassador.jpg',
        gridImages: [
            'img/ambassador.jpg', 
            'img/ambassador.jpg', 
            'img/ambassador.jpg' 
        ]
    },
    'deluxea': {
        title: 'Deluxe A Room',
        price: 1299,
        priceUnit: 'per night',
        capacity: 'Good for 3 persons',
        description: 'Our Deluxe A Room offers a stylish and comfortable setting for your stay, featuring modern amenities to ensure a relaxing experience.',
        imageUrl: 'img/deluxea.jpg',
        gridImages: [
            'img/deluxea.jpg', 
            'img/deluxea.jpg',
            'img/deluxea.jpg'  
        ]
    },
    'deluxeb': {
        title: 'Deluxe B Room',
        price: 1199,
        priceUnit: 'per night',
        capacity: 'Good for 4 persons',
        description: 'Our Deluxe B Room offers a stylish and comfortable setting for your stay, featuring modern amenities to ensure a relaxing experience.',
        imageUrl: 'img/deluxeb.jpg',
        gridImages: [
            'img/deluxeb.jpg',
            'img/deluxeb.jpg', 
            'img/deluxeb.jpg'
        ]
    },
    'standard': {
        title: 'Standard Room',
        price: 999,
        priceUnit: 'per night',
        capacity: 'Good for 2 persons',
        description: 'Our Standard Room offers a stylish and comfortable setting for your stay, featuring modern amenities to ensure a relaxing experience.',
        imageUrl: 'img/standard.jpg',
        gridImages: [
            'img/standard.jpg', 
            'img/standard.jpg',
            'img/standard.jpg'
        ]
    }
};

// --- CART MANAGEMENT FUNCTIONS ---

// Get cart from localStorage
function getCart() {
    const cartStr = localStorage.getItem('bookingCart');
    return cartStr ? JSON.parse(cartStr) : [];
}

// Save cart to localStorage
function saveCart(cart) {
    localStorage.setItem('bookingCart', JSON.stringify(cart));
    updateCartBadge();
}

// Add item to cart
function addToCart(item) {
    const cart = getCart();
    
    // Generate unique ID for cart item
    const itemId = `${item.roomKey || 'room'}_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    item.cartId = itemId;
    item.addedAt = new Date().toISOString();
    
    cart.push(item);
    saveCart(cart);
    return itemId;
}

// Remove item from cart
function removeFromCart(cartId) {
    const cart = getCart();
    const updatedCart = cart.filter(item => item.cartId !== cartId);
    saveCart(updatedCart);
    return updatedCart;
}

// Update item in cart
function updateCartItem(cartId, updates) {
    const cart = getCart();
    const itemIndex = cart.findIndex(item => item.cartId === cartId);
    
    if (itemIndex !== -1) {
        cart[itemIndex] = { ...cart[itemIndex], ...updates };
        saveCart(cart);
    }
    return cart;
}

// Get cart total
function getCartTotal() {
    const cart = getCart();
    return cart.reduce((total, item) => {
        const itemTotal = parseFloat(item.total.replace(/[₱,]/g, '')) || 0;
        return total + itemTotal;
    }, 0);
}

// Get cart item count
function getCartItemCount() {
    return getCart().length;
}

// Update cart badge in navigation
function updateCartBadge() {
    const cartCount = getCartItemCount();
    const cartBadge = document.getElementById('cart-badge');
    const cartLink = document.getElementById('cart-link');
    
    if (cartBadge) {
        if (cartCount > 0) {
            cartBadge.textContent = cartCount;
            cartBadge.style.display = 'flex';
        } else {
            cartBadge.style.display = 'none';
        }
    }
    
    // Update cart link visibility - always show the cart link
    if (cartLink) {
        cartLink.style.display = 'flex';
    }
}

// Clear cart
function clearCart() {
    localStorage.removeItem('bookingCart');
    updateCartBadge();
}

// --- MAIN EVENT LISTENER ---
document.addEventListener('DOMContentLoaded', () => {
    
    const header = document.querySelector('.header');
    if (header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    if (mobileMenuToggle && navMenu) {
        mobileMenuToggle.addEventListener('click', () => {
            const isExpanded = mobileMenuToggle.getAttribute('aria-expanded') === 'true';
            mobileMenuToggle.setAttribute('aria-expanded', !isExpanded);
            mobileMenuToggle.classList.toggle('active');
            navMenu.classList.toggle('active');
            document.body.classList.toggle('menu-open');
        });

        // Close menu when clicking on a link (except dropdown toggles)
        const navLinks = navMenu.querySelectorAll('.nav-link:not(.dropdown-toggle)');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                mobileMenuToggle.setAttribute('aria-expanded', 'false');
                mobileMenuToggle.classList.remove('active');
                navMenu.classList.remove('active');
                document.body.classList.remove('menu-open');
            });
        });
        
        // Dropdown toggle for mobile and desktop
        const dropdownToggles = navMenu.querySelectorAll('.dropdown-toggle');
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const dropdownItem = toggle.closest('.nav-item');
                // Close other dropdowns
                document.querySelectorAll('.nav-item.dropdown').forEach(item => {
                    if (item !== dropdownItem) {
                        item.classList.remove('show');
                    }
                });
                dropdownItem.classList.toggle('show');
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.nav-item.dropdown')) {
                document.querySelectorAll('.nav-item.dropdown').forEach(item => {
                    item.classList.remove('show');
                });
            }
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!navMenu.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                mobileMenuToggle.setAttribute('aria-expanded', 'false');
                mobileMenuToggle.classList.remove('active');
                navMenu.classList.remove('active');
                document.body.classList.remove('menu-open');
            }
        });
    }

    if (document.body.querySelector('.room-content-section')) {
        populateRoomDetails();
        setupBookingWidget();
        setupAvailabilityCalendar();
        setupGalleryLightbox();
    }
    else if (document.body.querySelector('.checkout-layout')) {
        populateCheckoutPage();
        setupCheckoutLogin();
    }
    else if (document.body.querySelector('.gallery-grid')) {
        setupGalleryLightbox(); 
    }
    
    // Update cart badge on page load
    updateCartBadge();
});


// --- ROOM DETAIL PAGE FUNCTIONS ---

// Helper function to format date in local timezone (YYYY-MM-DD)
function formatDateLocal(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function populateRoomDetails() {
    const params = new URLSearchParams(window.location.search);
    const roomKey = params.get('room');
    if (!roomKey || !roomData[roomKey]) {
        document.getElementById('room-title').textContent = 'Room Not Found';

        const gridContainer = document.getElementById('room-image-grid-container');
        if (gridContainer) gridContainer.style.display = 'none';
        return;
    }
    const room = roomData[roomKey];

    // Populate main details
    document.title = `${room.title} - Bodare Coop`;
    document.querySelector('.room-hero').style.backgroundImage = `url('${room.imageUrl}')`;
    document.getElementById('room-title').textContent = room.title;
    document.getElementById('room-capacity').textContent = room.capacity;
    document.querySelector('.room-description').textContent = room.description;
    document.getElementById('room-price-display').innerHTML = `<strong>₱${room.price.toLocaleString()}</strong> / ${room.priceUnit}`;
    const widget = document.querySelector('.booking-widget');
    if (widget) {
        widget.dataset.basePrice = room.price;
        console.log('Room price set:', room.price);
    }

    // --- START: NEW DYNAMIC GRID LOGIC ---
    const gridContainer = document.getElementById('room-image-grid-container');
    if (gridContainer && room.gridImages) {
        gridContainer.innerHTML = '';

        room.gridImages.forEach(imageUrl => {
            const img = document.createElement('img');
            img.src = imageUrl;
            img.alt = `${room.title} detail image`;
            img.classList.add('gallery-image');
            gridContainer.appendChild(img);
        });
    }
}

function setupBookingWidget() {
    const widget = document.querySelector('.booking-widget');
    if (!widget) {
        console.error('Booking widget not found');
        return;
    }
    
    const form = document.getElementById('booking-form-widget');
    if (!form) {
        console.error('Booking form not found');
        return;
    }
    form.addEventListener('submit', handleBookingSubmit);

    // Set minimum date to today for both date inputs (using local timezone)
    const today = new Date();
    const todayString = formatDateLocal(today);
    
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tomorrowString = formatDateLocal(tomorrow);
    
    const checkinInput = document.getElementById('checkin-widget');
    const checkoutInput = document.getElementById('checkout-widget');
    
    if (!checkinInput || !checkoutInput) {
        console.error('Date inputs not found');
        return;
    }
    
    // Set minimum dates
    checkinInput.setAttribute('min', todayString);
    checkoutInput.setAttribute('min', tomorrowString);
    
    // Set default check-in to today (current date)
    checkinInput.value = todayString;
    
    // Set default check-out to tomorrow (current date + 1 day)
    checkoutInput.value = tomorrowString;
    
    // Trigger input events to ensure validation runs
    checkinInput.dispatchEvent(new Event('change', { bubbles: true }));
    checkoutInput.dispatchEvent(new Event('change', { bubbles: true }));
    
    // Recalculate total cost with default dates
    // Ensure this runs after room data is populated
    // Use setTimeout to ensure basePrice is set from populateRoomDetails
    setTimeout(() => {
        const basePrice = parseFloat(widget.dataset.basePrice) || 0;
        if (basePrice > 0) {
            calculateTotalCost();
        } else {
            // If basePrice not set yet, try again after a longer delay
            console.warn('Base price not set, retrying calculation...');
            setTimeout(() => {
                calculateTotalCost();
            }, 200);
        }
    }, 100);

    // Add date validation event listeners
    if (checkinInput) {
        checkinInput.addEventListener('change', function() {
            validateDates();
            // Update checkout minimum date to be at least check-in date
            if (this.value) {
                const checkinDate = new Date(this.value);
                const nextDay = new Date(checkinDate);
                nextDay.setDate(nextDay.getDate() + 1);
                checkoutInput.setAttribute('min', formatDateLocal(nextDay));
                
                // If checkout is before or equal to check-in, update it
                if (checkoutInput.value && new Date(checkoutInput.value) <= checkinDate) {
                    checkoutInput.value = formatDateLocal(nextDay);
                }
            }
            calculateTotalCost();
        });
    }

    if (checkoutInput) {
        checkoutInput.addEventListener('change', function() {
            validateDates();
            calculateTotalCost();
        });
    }

    const counters = widget.querySelectorAll('.counter');

    counters.forEach(counter => {
        const minusBtn = counter.querySelector('button:first-of-type');
        const plusBtn = counter.querySelector('button:last-of-type');
        const input = counter.querySelector('input');
        const min = parseInt(counter.dataset.min, 10);

        minusBtn.addEventListener('click', () => {
            let value = parseInt(input.value);
            if (value > min) {
                input.value = value - 1;
                calculateTotalCost();
            }
        });

        plusBtn.addEventListener('click', () => {
            let value = parseInt(input.value);
            input.value = value + 1;
            calculateTotalCost();
        });
    });

    // Initial validation and calculation
    validateDates();
    calculateTotalCost();
}

// Date validation function
function validateDates() {
    const checkinInput = document.getElementById('checkin-widget');
    const checkoutInput = document.getElementById('checkout-widget');
    const errorMessage = document.getElementById('date-error-message');
    
    if (!checkinInput || !checkoutInput) return;
    
    // Get today's date in local timezone (set to midnight local time)
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    const checkinDate = checkinInput.value ? new Date(checkinInput.value) : null;
    const checkoutDate = checkoutInput.value ? new Date(checkoutInput.value) : null;
    
    let error = '';
    
    if (checkinDate) {
        checkinDate.setHours(0, 0, 0, 0);
        if (checkinDate < today) {
            error = 'Check-in date cannot be in the past. Please select today or a future date.';
            checkinInput.setCustomValidity(error);
        } else {
            checkinInput.setCustomValidity('');
        }
    }
    
    if (checkoutDate) {
        checkoutDate.setHours(0, 0, 0, 0);
        if (checkoutDate < today) {
            error = 'Check-out date cannot be in the past. Please select today or a future date.';
            checkoutInput.setCustomValidity(error);
        } else if (checkinDate && checkoutDate <= checkinDate) {
            error = 'Check-out date must be after check-in date. Please select a later date.';
            checkoutInput.setCustomValidity(error);
        } else {
            checkoutInput.setCustomValidity('');
        }
    }
    
    // Display error message
    if (error && errorMessage) {
        errorMessage.textContent = error;
        errorMessage.style.display = 'block';
    } else if (errorMessage) {
        errorMessage.style.display = 'none';
    }
}

function calculateTotalCost(newNights = null) {
    const widget = document.querySelector('.booking-widget');
    if (!widget) return;
    
    const basePrice = parseFloat(widget.dataset.basePrice) || 0;
    
    const params = new URLSearchParams(window.location.search);
    const roomKey = params.get('room');
    const room = roomData[roomKey];
    const priceUnit = room ? room.priceUnit : 'per night';

    // Calculate number of days (nights)
    let nights = 0;
    if (newNights) {
        nights = newNights;
    } else {
        const checkinInput = document.getElementById('checkin-widget');
        const checkoutInput = document.getElementById('checkout-widget');
        
        if (checkinInput && checkoutInput && checkinInput.value && checkoutInput.value) {
            const checkinDate = new Date(checkinInput.value);
            const checkoutDate = new Date(checkoutInput.value);
            
            // Calculate difference in days
            const timeDiff = checkoutDate - checkinDate;
            nights = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
            nights = Math.max(nights, 1); // Minimum 1 night
        } else {
            nights = 1; // Default to 1 night if dates not selected
        }
    }

    const roomCount = parseInt(document.getElementById('room-count-input').value) || 1;
    const adultCount = parseInt(document.getElementById('adults-count').value) || 1;
    const childCount = parseInt(document.getElementById('children-count').value) || 0;
    const guestCount = adultCount + childCount;

    // Calculate room cost: number of days × room rate × number of rooms
    let roomCost = 0;
    if (priceUnit === 'per head') {
        // For per head pricing: (price per head × guests) × nights × rooms
        roomCost = (basePrice * guestCount) * nights * roomCount;
    } else {
        // For per night pricing: (room rate × nights) × number of rooms
        // Formula: number of days × room rate × number of rooms
        roomCost = basePrice * nights * roomCount;
    }

    // Add extra bed cost
    const extraBedCounter = widget.querySelector('.counter[data-cost]');
    if (extraBedCounter) {
        const extraBedCount = parseInt(extraBedCounter.querySelector('input').value) || 0;
        const extraBedCost = parseFloat(extraBedCounter.dataset.cost) || 0;
        roomCost += extraBedCount * extraBedCost * nights;
    }

    // Services are now handled in cart page, not here
    const totalCost = roomCost;
    const totalCostDisplay = document.getElementById('total-cost-display');
    if (totalCostDisplay) {
        const formattedCost = `₱${totalCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        totalCostDisplay.textContent = formattedCost;
        console.log('Total cost calculated:', formattedCost, 'Nights:', nights, 'Room count:', roomCount, 'Base price:', basePrice);
    } else {
        console.error('Total cost display element not found');
    }
}


function setupAvailabilityCalendar() {
    const calendarInput = document.getElementById('availability-calendar');
    if (calendarInput && typeof confirmDatePlugin !== 'undefined') {
        flatpickr(calendarInput, {
            inline: true,
            mode: "range",
            showMonths: 2,
            minDate: "today",
            plugins: [new confirmDatePlugin({ confirmText: "Apply", showAlways: true })],
            onConfirm: function(selectedDates) {
                if (selectedDates.length === 2) {
                    const checkinInput = document.getElementById('checkin-widget');
                    const checkoutInput = document.getElementById('checkout-widget');
                    
                    if (checkinInput && checkoutInput) {
                        checkinInput.value = formatDateLocal(selectedDates[0]);
                        checkoutInput.value = formatDateLocal(selectedDates[1]);
                        
                        // Update minimum dates (using local timezone)
                        const today = new Date();
                        const todayString = formatDateLocal(today);
                        checkinInput.setAttribute('min', todayString);
                        
                        const nextDay = new Date(selectedDates[0]);
                        nextDay.setDate(nextDay.getDate() + 1);
                        checkoutInput.setAttribute('min', formatDateLocal(nextDay));
                        
                        // Validate dates
                        validateDates();
                        
                        // Calculate nights and total cost
                        const checkin = selectedDates[0];
                        const checkout = selectedDates[1];
                        let calculatedNights = Math.ceil((checkout - checkin) / (1000 * 60 * 60 * 24));
                        calculatedNights = Math.max(calculatedNights, 1);
                        
                        calculateTotalCost(calculatedNights);
                    }
                }
            }
        });
    }
}

// --- CHECKOUT PAGE FUNCTIONS ---

async function handleBookingSubmit(event) {
    event.preventDefault(); 
    
    // Validate dates before submission
    validateDates();
    const checkinInput = document.getElementById('checkin-widget');
    const checkoutInput = document.getElementById('checkout-widget');
    
    if (!checkinInput.value || !checkoutInput.value) {
        alert('Please select both check-in and check-out dates.');
        return;
    }
    
    const checkinDate = new Date(checkinInput.value);
    const checkoutDate = new Date(checkoutInput.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (checkinDate < today) {
        alert('Check-in date cannot be in the past. Please select today or a future date.');
        checkinInput.focus();
        return;
    }
    
    if (checkoutDate < today) {
        alert('Check-out date cannot be in the past. Please select today or a future date.');
        checkoutInput.focus();
        return;
    }
    
    if (checkoutDate <= checkinDate) {
        alert('Check-out date must be after check-in date. Please select a later date.');
        checkoutInput.focus();
        return;
    }

    const params = new URLSearchParams(window.location.search);
    const roomKey = params.get('room');
    const room = roomData[roomKey];
    
    // Try to get room_id from API
    let roomId = null;
    if (typeof API !== 'undefined') {
        try {
            const roomsResponse = await API.booking.getRooms();
            if (roomsResponse.success && roomsResponse.rooms) {
                const matchedRoom = roomsResponse.rooms.find(r => 
                    r.room_name === room.title || 
                    r.room_name.toLowerCase().includes(room.title.toLowerCase()) ||
                    room.title.toLowerCase().includes(r.room_name.toLowerCase())
                );
                if (matchedRoom) {
                    roomId = matchedRoom.id;
                }
            }
        } catch (error) {
            console.error('Error fetching room ID:', error);
        }
    }
    
    // Services are now handled in cart page, not here - start with empty array
    const selectedServices = [];
    
    // Get extra beds
    const widget = document.querySelector('.booking-widget');
    const extraBedCounter = widget?.querySelector('.counter[data-cost]');
    const extraBeds = extraBedCounter ? parseInt(extraBedCounter.querySelector('input').value) || 0 : 0;
    const extraBedCost = extraBedCounter ? parseFloat(extraBedCounter.dataset.cost) || 0 : 0;
    
    // Calculate nights
    const checkin = new Date(checkinInput.value);
    const checkout = new Date(checkoutInput.value);
    const nights = Math.ceil((checkout - checkin) / (1000 * 60 * 60 * 24));
    
    const cartItem = {
        roomKey: roomKey,
        roomId: roomId,
        roomName: room.title,
        imageUrl: room.imageUrl,
        price: room.price,
        priceUnit: room.priceUnit,
        checkin: checkinInput.value,
        checkout: checkoutInput.value,
        nights: nights,
        adults: parseInt(document.getElementById('adults-count').value) || 1,
        children: parseInt(document.getElementById('children-count').value) || 0,
        rooms: parseInt(document.getElementById('room-count-input').value) || 1,
        extraBeds: extraBeds,
        extraBedCost: extraBedCost,
        services: selectedServices,
        total: document.getElementById('total-cost-display').textContent,
        totalAmount: parseFloat(document.getElementById('total-cost-display').textContent.replace(/[₱,]/g, '')) || 0
    };
    
    // Add to cart
    addToCart(cartItem);
    
    // Redirect to cart page immediately
    window.location.href = 'cart.php';
}

function populateCheckoutPage() {
    const cart = getCart();
    const summaryContent = document.getElementById('checkout-summary-content');
    
    if (!summaryContent) return;
    
    if (!cart || cart.length === 0) {
        // Fallback to old bookingDetails for backward compatibility
        const bookingDetails = localStorage.getItem('bookingDetails');
        if (bookingDetails) {
            const details = JSON.parse(bookingDetails);
            summaryContent.innerHTML = `
                <img src="${details.imageUrl}" alt="Room Image" style="width: 100%; border-radius: 8px; margin-bottom: 1.5rem;">
                <div class="summary-content">
                    <h3>${details.roomName}</h3>
                    <div class="summary-item">
                        <span>Check-In</span>
                        <strong>${details.checkin}</strong>
                    </div>
                    <div class="summary-item">
                        <span>Check-Out</span>
                        <strong>${details.checkout}</strong>
                    </div>
                    <div class="summary-item">
                        <span>Guests</span>
                        <strong>Adults: ${details.adults}, Children: ${details.children}</strong>
                    </div>
                    <div class="summary-item">
                        <span>Rooms</span>
                        <strong>${details.rooms}</strong>
                    </div>
                    <div class="summary-total">
                        <span>Total</span>
                        <strong>${details.total}</strong>
                    </div>
                </div>
            `;
            return;
        }
        
        summaryContent.innerHTML = '<h3>Your cart is empty.</h3><p><a href="rooms.php">Browse Rooms</a></p>';
        return;
    }
    
    // Display all cart items
    let summaryHTML = '<div class="summary-content"><h3>Booking Summary</h3>';
    let totalAmount = 0;
    
    cart.forEach((item, index) => {
        totalAmount += item.totalAmount || 0;
        summaryHTML += `
            <div style="border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 1rem;">
                <h4 style="margin-bottom: 0.5rem; color: var(--dark-blue);">${item.roomName}</h4>
                <div class="summary-item">
                    <span>Check-In</span>
                    <strong>${formatDateDisplay(item.checkin)}</strong>
                </div>
                <div class="summary-item">
                    <span>Check-Out</span>
                    <strong>${formatDateDisplay(item.checkout)}</strong>
                </div>
                <div class="summary-item">
                    <span>Nights</span>
                    <strong>${item.nights}</strong>
                </div>
                <div class="summary-item">
                    <span>Guests</span>
                    <strong>${item.adults} Adult(s), ${item.children} Child(ren)</strong>
                </div>
                <div class="summary-item">
                    <span>Rooms</span>
                    <strong>${item.rooms}</strong>
                </div>
                <div class="summary-item">
                    <span>Subtotal</span>
                    <strong>${item.total}</strong>
                </div>
            </div>
        `;
    });
    
    // Add services total
    const savedServices = localStorage.getItem('cartServices');
    let servicesTotal = 0;
    let servicesHTML = '';
    if (savedServices) {
        const services = JSON.parse(savedServices);
        servicesTotal = services.reduce((sum, s) => sum + s.cost, 0);
        if (services.length > 0) {
            servicesHTML = `
                <div style="border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 1rem;">
                    <h4 style="margin-bottom: 0.5rem; color: var(--dark-blue);">Extra Services</h4>
                    ${services.map(s => `
                        <div class="summary-item">
                            <span>${s.name}</span>
                            <strong>₱${s.cost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>
                        </div>
                    `).join('')}
                    <div class="summary-item">
                        <span>Services Total</span>
                        <strong>₱${servicesTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>
                    </div>
                </div>
            `;
        }
    }
    
    const grandTotal = totalAmount + servicesTotal;
    
    summaryHTML += servicesHTML;
    summaryHTML += `
        <div class="summary-total">
            <span>Total</span>
            <strong>₱${grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>
        </div>
    </div>`;
    
    summaryContent.innerHTML = summaryHTML;
}

function formatDateDisplay(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function setupCheckoutLogin() {
    const loginButton = document.getElementById('login-button');
    const loginFields = document.getElementById('login-fields');
    const paymentSection = document.getElementById('payment-section');
    const loginHeader = document.querySelector('.checkout-auth-header h2');
    const emailInput = document.getElementById('login-email');

    if (loginButton && loginFields && paymentSection && loginHeader) {
        
        loginButton.addEventListener('click', (event) => {
            event.preventDefault(); 
            
            loginFields.style.display = 'none';
            paymentSection.style.display = 'block';

            const email = emailInput.value.trim();
            if (email !== '') {
                loginHeader.textContent = 'Welcome, ' + email;
            } else {
                loginHeader.textContent = 'Welcome!';
            }
        });
    }
}

// --- GALLERY PAGE FUNCTION ---

function setupGalleryLightbox() {
    const modal = document.getElementById('lightbox-modal');
    const modalImg = document.getElementById('lightbox-image');
    const images = document.querySelectorAll('.gallery-image'); 
    const closeBtn = document.querySelector('.lightbox-close');

    if (!modal || !modalImg || !closeBtn || images.length === 0) return; 

    images.forEach(image => {
        image.addEventListener('click', () => {
            modal.style.display = 'flex';
            modalImg.src = image.src;
        });
    });

    function closeModal() {
        modal.style.display = 'none';
    }

    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });
}
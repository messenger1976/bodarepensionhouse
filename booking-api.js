// Booking API Integration Functions
// This file extends script.js with API connectivity

// Registration form handler
function setupRegistrationForm() {
    const form = document.getElementById('registration-form');
    if (!form) return;
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Clear previous error messages
        clearFormErrors();
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating Account...';
        
        // Get form data
        const userData = {
            first_name: document.getElementById('reg-first-name').value.trim(),
            last_name: document.getElementById('reg-last-name').value.trim(),
            email: document.getElementById('reg-email').value.trim(),
            phone: document.getElementById('reg-phone').value.trim(),
            address: document.getElementById('reg-address').value.trim(),
            city: document.getElementById('reg-city')?.value.trim() || '',
            province: document.getElementById('reg-province')?.value.trim() || '',
            postal_code: document.getElementById('reg-postal-code')?.value.trim() || '',
            country: document.getElementById('reg-country')?.value.trim() || 'Philippines',
            date_of_birth: document.getElementById('reg-date-of-birth')?.value || '',
            gender: document.getElementById('reg-gender')?.value || '',
            nationality: document.getElementById('reg-nationality')?.value.trim() || '',
            id_type: document.getElementById('reg-id-type')?.value || '',
            id_number: document.getElementById('reg-id-number')?.value.trim() || '',
            password: document.getElementById('reg-password').value,
            confirm_password: document.getElementById('reg-confirm-password').value
        };
        
        // Basic client-side validation
        if (!userData.first_name || !userData.last_name || !userData.email || 
            !userData.phone || !userData.address || !userData.password || !userData.confirm_password) {
            showMessage('Please complete all required fields to create your account.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            return;
        }
        
        if (userData.password !== userData.confirm_password) {
            showMessage('The passwords you entered do not match. Please try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            return;
        }
        
        if (userData.password.length < 6) {
            showMessage('Your password must be at least 6 characters long for security purposes.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            return;
        }
        
        try {
            const cartSnapshot = typeof getCartStorageSnapshot === 'function'
                ? getCartStorageSnapshot()
                : null;

            const response = await API.auth.register(userData);
            
            if (response.success) {
                // Email confirmation required — do not auto-login.
                if (response.requires_verification) {
                    showMessage(response.message || 'Please check your email and click the confirmation link to activate your account.', 'success');

                    form.reset();
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;

                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 4000);
                    return;
                }

                // Legacy path (auto-login) if API still returns a user session.
                showMessage('Welcome! Your account has been created successfully. Redirecting to your dashboard...', 'success');
                
                // Store user info
                localStorage.setItem('user', JSON.stringify(response.user));

                // Keep cart intact after registration/login transition.
                if (typeof restoreCartStorageSnapshot === 'function') {
                    restoreCartStorageSnapshot(cartSnapshot);
                }
                
                // Check if booking exists in cart
                const bookingDetails = localStorage.getItem('bookingDetails');
                
                if (bookingDetails) {
                    // If booking exists, show customer info and payment on registration page
                    showCustomerInfo(response.user, userData);
                    checkAndShowBooking();
                } else {
                    // If no booking, redirect to dashboard
                    setTimeout(() => {
                        window.location.href = 'customer-dashboard.php';
                    }, 1500);
                }
            }
        } catch (error) {
            // Handle API errors
            let errorMessage = 'We encountered an issue creating your account. Please check your information and try again.';
            
            if (error.message) {
                // Make error messages more user-friendly
                if (error.message.includes('Email already registered') || error.message.includes('email')) {
                    errorMessage = 'This email address is already registered. Please use a different email or try logging in instead.';
                } else if (error.message.includes('Validation failed')) {
                    errorMessage = 'Please check the form fields and ensure all information is entered correctly.';
                } else {
                    errorMessage = error.message;
                }
            } else if (error.response && error.response.errors) {
                // Handle validation errors from API
                const errors = error.response.errors;
                const errorMessages = Object.values(errors).flat();
                
                // Format error messages to be more user-friendly
                if (errorMessages.length === 1) {
                    errorMessage = errorMessages[0];
                } else {
                    errorMessage = 'Please correct the following: ' + errorMessages.join(', ');
                }
                displayFieldErrors(errors);
            } else if (error.status === 500) {
                errorMessage = 'Our server is experiencing issues. Please try again in a few moments.';
            } else if (error.status === 400) {
                errorMessage = 'Please check your information and try again.';
            }
            
            showMessage(errorMessage, 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
}

// Clear form error messages
function clearFormErrors() {
    const form = document.getElementById('registration-form');
    if (!form) return;
    
    // Remove error classes from inputs
    form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
    
    // Remove error messages
    form.querySelectorAll('.field-error').forEach(el => el.remove());
}

// Display field-specific errors
function displayFieldErrors(errors) {
    const fieldMap = {
        'first_name': 'reg-first-name',
        'last_name': 'reg-last-name',
        'email': 'reg-email',
        'phone': 'reg-phone',
        'address': 'reg-address',
        'city': 'reg-city',
        'province': 'reg-province',
        'postal_code': 'reg-postal-code',
        'country': 'reg-country',
        'date_of_birth': 'reg-date-of-birth',
        'gender': 'reg-gender',
        'nationality': 'reg-nationality',
        'id_type': 'reg-id-type',
        'id_number': 'reg-id-number',
        'password': 'reg-password',
        'confirm_password': 'reg-confirm-password'
    };
    
    Object.keys(errors).forEach(field => {
        const fieldId = fieldMap[field];
        if (fieldId) {
            const input = document.getElementById(fieldId);
            if (input) {
                input.classList.add('error');
                const errorMsg = document.createElement('div');
                errorMsg.className = 'field-error';
                errorMsg.textContent = errors[field];
                errorMsg.style.cssText = 'color: #721c24; font-size: 0.875rem; margin-top: 0.25rem;';
                input.parentElement.appendChild(errorMsg);
            }
        }
    });
}

async function showAuthenticatedCheckout(user) {
    if (!user) return;

    localStorage.setItem('user', JSON.stringify(user));

    if (typeof setCheckoutGuestMode === 'function') {
        setCheckoutGuestMode(false);
    }

    const loginFields = document.getElementById('login-fields');
    const accountInfoSection = document.getElementById('account-info-section');
    const paymentSection = document.getElementById('payment-section');

    const guestLogin = document.getElementById('checkout-guest-login');
    if (guestLogin) guestLogin.style.display = 'none';
    if (loginFields) loginFields.style.display = 'none';
    if (accountInfoSection) accountInfoSection.style.display = 'block';
    if (paymentSection) paymentSection.style.display = 'block';

    const requiredFields = [
        'account-first-name', 'account-last-name', 'account-email',
        'account-phone', 'account-address', 'account-city',
        'account-province', 'account-postal-code', 'account-country',
        'payment-method'
    ];
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) field.setAttribute('required', 'required');
    });

    try {
        const profileResponse = await API.user.getProfile();
        if (profileResponse.success && profileResponse.user) {
            const profile = profileResponse.user;
            const fullName = [profile.first_name, profile.last_name].filter(Boolean).join(' ');
            const storedUser = {
                ...user,
                ...profile,
                name: fullName || user.name || profile.email
            };
            localStorage.setItem('user', JSON.stringify(storedUser));
            if (typeof displayAccountInfo === 'function') {
                displayAccountInfo(profile);
            }
        }
    } catch (error) {
        console.error('Error loading checkout profile:', error);
        if (typeof displayAccountInfo === 'function') {
            displayAccountInfo(user);
        }
    }

    if (typeof updateHeaderAuthButtons === 'function') {
        updateHeaderAuthButtons();
    }
}

// Checkout login handler
async function handleCheckoutLogin() {
    const emailInput = document.getElementById('login-email');
    const passwordInput = document.getElementById('login-password');
    
    if (!emailInput || !passwordInput) {
        showMessage('Login form fields not found.', 'error');
        return false;
    }

    clearCheckoutInputErrors();
    
    const email = emailInput.value.trim().toLowerCase();
    const password = passwordInput.value;
    
    if (!email || !password) {
        if (!email) emailInput.classList.add('input-error');
        if (!password) passwordInput.classList.add('input-error');
        showMessage('Please enter both your email address and password to continue.', 'error');
        return false;
    }
    
    try {
        const loginButton = document.getElementById('login-button');
        if (loginButton) {
            loginButton.disabled = true;
            loginButton.textContent = 'Logging in...';
        }

        const cartSnapshot = typeof getCartStorageSnapshot === 'function'
            ? getCartStorageSnapshot()
            : null;

        const response = await API.auth.login(email, password);
        
        if (response.success) {
            // Store user info
            localStorage.setItem('user', JSON.stringify(response.user));

            // Keep cart intact after login transition.
            if (typeof restoreCartStorageSnapshot === 'function') {
                restoreCartStorageSnapshot(cartSnapshot);
            }

            // Reload so the page renders from the established server session.
            sessionStorage.setItem('checkoutLoginMessage', 'Welcome back! You have successfully logged in.');
            window.location.reload();
            
            return true;
        }
    } catch (error) {
        let errorMessage = 'We couldn\'t log you in. Please check your email and password and try again.';
        
        if (error.message) {
            if (error.message.includes('Invalid email') || error.message.includes('Invalid password')) {
                errorMessage = 'The email or password you entered is incorrect. Please try again.';
            } else if (error.message.includes('not found') || error.message.includes('does not exist')) {
                errorMessage = 'No account found with this email address. Please register first.';
            } else {
                errorMessage = error.message;
            }
        }
        
        showMessage(errorMessage, 'error');
        return false;
    } finally {
        const loginButton = document.getElementById('login-button');
        if (loginButton) {
            loginButton.disabled = false;
            loginButton.textContent = 'Login';
        }
    }
}

// Create booking handler
async function createBooking(bookingData) {
    try {
        const response = await API.booking.create(bookingData);
        
        // Check if response indicates failure even if no exception was thrown
        if (!response.success) {
            const error = new Error(response.message || 'Booking failed');
            error.response = response;
            throw error;
        }
        
        // Store booking number
        localStorage.setItem('booking_number', response.booking_number);
        localStorage.setItem('last_booking', JSON.stringify(response.booking));
        
        return response;
    } catch (error) {
        let errorMessage = 'We couldn\'t complete your booking at this time. Please try again or contact us for assistance.';
        
        // Log full error for debugging
        console.error('Booking error details:', error);
        
        // Check for conflicting bookings in debug info first
        if (error.response && error.response.debug && error.response.debug.conflicting_bookings) {
            const conflicts = error.response.debug.conflicting_bookings;
            const requestedDates = error.response.debug.requested_dates || {};
            const checkIn = requestedDates.check_in || bookingData.check_in;
            const checkOut = requestedDates.check_out || bookingData.check_out;
            const roomName = requestedDates.room_name || 'the selected room';
            const requestedRooms = requestedDates.requested_rooms || bookingData.rooms || 1;
            const availableRooms = requestedDates.available_rooms || 1;
            const bookedRooms = requestedDates.booked_rooms || 0;
            const remainingRooms = requestedDates.remaining_rooms || 0;
            
            // Format dates for display
            const formatDate = (dateStr) => {
                if (!dateStr) return '';
                const date = new Date(dateStr);
                return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            };
            
            // Build detailed error message with room availability info
            let conflictDetails = '';
            if (conflicts.length > 0) {
                const conflictDates = conflicts.map(conflict => {
                    const conflictCheckIn = formatDate(conflict.check_in);
                    const conflictCheckOut = formatDate(conflict.check_out);
                    return `${conflictCheckIn} to ${conflictCheckOut}`;
                }).join(', ');
                
                conflictDetails = ` There ${conflicts.length === 1 ? 'is' : 'are'} ${conflicts.length} existing booking${conflicts.length === 1 ? '' : 's'} for ${roomName} during: ${conflictDates}.`;
            }
            
            // Add room availability information
            let availabilityInfo = '';
            if (availableRooms > 1) {
                availabilityInfo = ` ${roomName} has ${availableRooms} rooms available, but ${bookedRooms} ${bookedRooms === 1 ? 'is' : 'are'} already booked. Only ${remainingRooms} ${remainingRooms === 1 ? 'room is' : 'rooms are'} available for your dates.`;
                if (requestedRooms > remainingRooms) {
                    availabilityInfo += ` You requested ${requestedRooms} ${requestedRooms === 1 ? 'room' : 'rooms'}, but only ${remainingRooms} ${remainingRooms === 1 ? 'is' : 'are'} available.`;
                }
            }
            
            errorMessage = `Sorry, ${roomName} is not available for your chosen dates (${formatDate(checkIn)} to ${formatDate(checkOut)}).${conflictDetails}${availabilityInfo} Please select different dates or try a different room.`;
        } else if (error.response && error.response.booking_created && error.response.booking_number) {
            // Booking exists; PayMongo checkout failed — allow retry without a new booking
            try {
                sessionStorage.setItem('paymongo_retry_booking', error.response.booking_number);
                sessionStorage.setItem(
                    'paymongo_retry_method',
                    (error.response.payment_method === 'card') ? 'card' : 'gcash'
                );
                localStorage.setItem('booking_number', error.response.booking_number);
            } catch (storageError) {
                console.warn('Unable to store PayMongo retry booking number', storageError);
            }
            errorMessage = error.response.message
                || `Your booking ${error.response.booking_number} was saved, but online payment could not start. Select the same payment method and continue to retry.`;
        } else if (error.response && error.response.message) {
            // Check response message
            const responseMessage = error.response.message.toLowerCase();
            const isOnlinePaymentUnavailable = /gcash|paymongo|online\s+payment/.test(responseMessage)
                && (responseMessage.includes('not available') || responseMessage.includes('not configured'));
            if (isOnlinePaymentUnavailable) {
                errorMessage = error.response.message;
            } else if (responseMessage.includes('not available') || responseMessage.includes('unavailable') || responseMessage.includes('conflict')) {
                errorMessage = 'Sorry, the selected room is not available for your chosen dates. Please select different dates or try a different room.';
            } else if (responseMessage.includes('validation')) {
                errorMessage = 'Please check your booking details and ensure all information is correct.';
            } else {
                errorMessage = error.response.message;
            }
        } else if (error.message) {
            // Check error message
            const msg = error.message.toLowerCase();
            const isOnlinePaymentUnavailable = /gcash|paymongo|online\s+payment/.test(msg)
                && (msg.includes('not available') || msg.includes('not configured'));
            if (isOnlinePaymentUnavailable) {
                errorMessage = error.message;
            } else if (msg.includes('not available') || msg.includes('unavailable') || msg.includes('conflict')) {
                errorMessage = 'Sorry, the selected room is not available for your chosen dates. Please select different dates or try a different room.';
            } else if (error.message.includes('Validation failed')) {
                errorMessage = 'Please check your booking details and ensure all information is correct.';
            } else {
                errorMessage = error.message;
            }
        }
        
        showMessage(errorMessage, 'error');
        throw error;
    }
}

// Checkout form submission
async function handleCheckoutSubmit(e) {
    e.preventDefault();
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Processing...';
    clearCheckoutInputErrors();
    
    // Get cart items instead of single bookingDetails
    // getCart is defined in script.js, but we'll define it here if not available
    function getCartLocal() {
        const cartStr = localStorage.getItem('bookingCart');
        return cartStr ? JSON.parse(cartStr) : [];
    }
    
    function clearCartLocal() {
        // Clear cart data
        if (typeof withExpectedCartMutation === 'function') {
            withExpectedCartMutation('booking-success-clear-cart', () => {
                localStorage.removeItem('bookingCart');
            });
        } else {
            localStorage.removeItem('bookingCart');
        }
        localStorage.removeItem('cartServices');
        localStorage.removeItem('bookingDetails'); // Also clear old booking details if exists
        
        // Update cart badge if function exists (from script.js)
        if (typeof updateCartBadge === 'function') {
            updateCartBadge();
        }
        
        // Also try to use global clearCart function if available
        if (typeof clearCart === 'function') {
            clearCart();
        }
        
        console.log('Cart cleared successfully');
    }
    
    const cart = getCartLocal().map(item => (
        typeof applyCartItemPricing === 'function' ? applyCartItemPricing(item) : item
    ));

    if (typeof withExpectedCartMutation === 'function') {
        withExpectedCartMutation('normalize-cart-before-checkout', () => {
            localStorage.setItem('bookingCart', JSON.stringify(cart));
        });
    } else {
        localStorage.setItem('bookingCart', JSON.stringify(cart));
    }

    if (!cart || cart.length === 0) {
        showMessage('Your cart is empty. Please add rooms to your cart first.', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        return;
    }

    // Capture services immediately so later cart cleanup cannot drop them.
    let selectedExtraServices = [];
    try {
        const savedCartServices = localStorage.getItem('cartServices');
        if (savedCartServices) {
            const parsed = JSON.parse(savedCartServices);
            if (Array.isArray(parsed)) {
                selectedExtraServices = parsed.filter(service => service && service.name);
            }
        }
    } catch (e) {
        console.warn('Unable to read cart services at checkout start:', e);
    }
    
    // Get user info
    const userStr = localStorage.getItem('user');
    if (!userStr) {
        showMessage('Please log in or create an account to complete your booking.', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        return;
    }
    
    const user = JSON.parse(userStr);
    
    // Validate required fields
    const accountFirstName = document.getElementById('account-first-name');
    const accountLastName = document.getElementById('account-last-name');
    const accountEmail = document.getElementById('account-email');
    const accountPhone = document.getElementById('account-phone');
    const accountAddress = document.getElementById('account-address');
    const accountCity = document.getElementById('account-city');
    const accountProvince = document.getElementById('account-province');
    const accountPostalCode = document.getElementById('account-postal-code');
    const accountCountry = document.getElementById('account-country');
    
    // Get guest information from form (required when logged in)
    let guestName, guestEmail, guestPhone, guestAddress, guestCity, guestProvince, guestCountry, guestZipcode;
    
    if (accountFirstName && accountLastName) {
        // User is logged in - get from form fields
        const firstName = accountFirstName.value.trim();
        const lastName = accountLastName.value.trim();
        
        if (!firstName || !lastName) {
            failCheckoutValidation('Please enter your first name and last name.', !firstName ? 'account-first-name' : 'account-last-name', submitBtn, originalText);
            return;
        }
        
        guestName = `${firstName} ${lastName}`;
        guestEmail = accountEmail?.value.trim() || '';
        guestPhone = accountPhone?.value.trim() || '';
        guestAddress = accountAddress?.value.trim() || '';
        guestCity = accountCity?.value.trim() || '';
        guestProvince = accountProvince?.value.trim() || '';
        guestCountry = accountCountry?.value.trim() || '';
        guestZipcode = accountPostalCode?.value.trim() || '';
        
        if (!guestEmail) {
            failCheckoutValidation('Please enter your email address.', 'account-email', submitBtn, originalText);
            return;
        }
        
        if (!guestPhone) {
            failCheckoutValidation('Please enter your contact number.', 'account-phone', submitBtn, originalText);
            return;
        }
        
        if (!guestAddress) {
            failCheckoutValidation('Please enter your street address.', 'account-address', submitBtn, originalText);
            return;
        }
        
        if (!guestCity) {
            failCheckoutValidation('Please enter your city.', 'account-city', submitBtn, originalText);
            return;
        }
        
        if (!guestProvince) {
            failCheckoutValidation('Please enter your province.', 'account-province', submitBtn, originalText);
            return;
        }
        
        if (!guestCountry) {
            failCheckoutValidation('Please enter your country.', 'account-country', submitBtn, originalText);
            return;
        }
        
        if (!guestZipcode) {
            failCheckoutValidation('Please enter your zip/postal code.', 'account-postal-code', submitBtn, originalText);
            return;
        }
    } else {
        // Not logged in - use user data from localStorage
        guestName = user.name || '';
        guestEmail = user.email || '';
        guestPhone = user.phone || '';
        guestAddress = user.address || '';
        guestCity = user.city || '';
        guestProvince = user.province || '';
        guestCountry = user.country || 'Philippines';
        guestZipcode = user.postal_code || '';
    }
    
    // Get payment method
    const paymentMethod = document.getElementById('payment-method')?.value || 'pay_at_hotel';
    
    // Get all rooms from API to match room names to IDs
    let roomsList = [];
    try {
        const roomsResponse = await API.booking.getRooms();
        if (roomsResponse.success && roomsResponse.rooms) {
            roomsList = roomsResponse.rooms;
        }
    } catch (error) {
        console.error('Error fetching rooms:', error);
        showMessage('Error loading room information. Please try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        return;
    }
    
    // Process each cart item to create room_selections array (same logic as admin panel)
    const roomSelections = [];
    let totalRooms = 0;
    let totalAdults = 0;
    let totalChildren = 0;
    let allServices = [];
    const roomDetails = [];
    let firstCheckIn = null;
    let firstCheckOut = null;
    
    // Process each cart item separately
    for (const item of cart) {
        // Prefer exact identifiers so similarly named rooms cannot be confused
        const matchedRoom = (item.roomId && roomsList.find(room => String(room.id) === String(item.roomId)))
            || (item.roomKey && roomsList.find(room => room.room_code === item.roomKey))
            || roomsList.find(room => room.room_name === item.roomName)
            || roomsList.find(room =>
                room.room_name.toLowerCase().includes(item.roomName.toLowerCase()) ||
                item.roomName.toLowerCase().includes(room.room_name.toLowerCase())
            );
        
        if (!matchedRoom) {
            showMessage(`Room "${item.roomName}" not found in system.`, 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            return;
        }
        
        // Format dates to YYYY-MM-DD (API stores dates only; times are guest preference)
        let itemCheckIn = item.checkin;
        let itemCheckOut = item.checkout;

        const toBookingDate = (value) => {
            if (!value) return value;
            const match = String(value).match(/^(\d{4}-\d{2}-\d{2})/);
            if (match) return match[1];
            const d = new Date(value);
            if (Number.isNaN(d.getTime())) return value;
            return d.getFullYear() + '-' +
                String(d.getMonth() + 1).padStart(2, '0') + '-' +
                String(d.getDate()).padStart(2, '0');
        };

        itemCheckIn = toBookingDate(itemCheckIn);
        itemCheckOut = toBookingDate(itemCheckOut);

        // Hotel billing uses calendar nights. If times made same-day valid in the UI,
        // ensure API dates still span at least one night.
        if (itemCheckIn && itemCheckOut && itemCheckOut <= itemCheckIn) {
            const nights = Math.max(1, parseInt(item.nights, 10) || 1);
            const start = (typeof parseDateLocal === 'function')
                ? parseDateLocal(itemCheckIn)
                : new Date(itemCheckIn + 'T12:00:00');
            if (start && !Number.isNaN(start.getTime())) {
                start.setDate(start.getDate() + nights);
                itemCheckOut = (typeof formatDateLocal === 'function')
                    ? formatDateLocal(start)
                    : [
                        start.getFullYear(),
                        String(start.getMonth() + 1).padStart(2, '0'),
                        String(start.getDate()).padStart(2, '0')
                    ].join('-');
            }
        }

        if (!itemCheckIn || !itemCheckOut || itemCheckOut <= itemCheckIn) {
            showMessage('Check-out date must be after check-in date for every room in your cart.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            return;
        }
        
        const itemRooms = parseInt(item.rooms) || 1;
        const itemAdults = parseInt(item.adults) || 0;
        const itemChildren = parseInt(item.children) || 0;
        const itemGuests = itemAdults + itemChildren;
        
        // Store first item's dates for main booking record
        if (firstCheckIn === null) {
            firstCheckIn = itemCheckIn;
            firstCheckOut = itemCheckOut;
        }
        
        const extraBedInfo = typeof resolveCartItemExtraBed === 'function'
            ? resolveCartItemExtraBed(item)
            : {
                extraBeds: parseInt(item.extraBeds, 10) || 0,
                extraBedCost: parseFloat(item.extraBedCost) || 199,
                extraBedTotal: 0,
                roomSubtotal: item.totalAmount || 0
            };
        const extraBeds = extraBedInfo.extraBeds;
        const extraBedPrice = extraBedInfo.extraBedCost;

        // Add room selection (same format as admin panel)
        roomSelections.push({
            room_id: matchedRoom.id,
            quantity: itemRooms,
            check_in: itemCheckIn,
            check_out: itemCheckOut,
            guests: itemGuests > 0 ? itemGuests : 1,
            extra_beds: extraBeds,
            extra_bed_price: extraBedPrice
        });

        if (extraBeds > 0 && extraBedInfo.extraBedTotal > 0) {
            allServices.push({
                name: `Extra Bed (${extraBeds} bed${extraBeds > 1 ? 's' : ''} × ${item.nights} night${item.nights > 1 ? 's' : ''} @ ₱${extraBedPrice.toLocaleString()} — ${item.roomName})`,
                cost: extraBedInfo.extraBedTotal
            });
        }
        
        totalRooms += itemRooms;
        totalAdults += itemAdults;
        totalChildren += itemChildren;
        
        // Collect services from all items
        if (item.services && item.services.length > 0) {
            allServices = allServices.concat(item.services);
        }
        
        // Track room details for notes
        let roomDetail = `${item.roomName} (${itemRooms} room${itemRooms > 1 ? 's' : ''}, ${itemGuests} guest${itemGuests !== 1 ? 's' : ''})`;
        if (extraBeds > 0) {
            roomDetail += `, ${extraBeds} extra bed${extraBeds > 1 ? 's' : ''}`;
        }
        roomDetails.push(roomDetail);
    }

    // Merge room-level services with cart-level extras captured at submit start
    allServices = allServices.concat(selectedExtraServices);
    
    // Calculate total guests
    const totalGuests = totalAdults + totalChildren;

    // Deduplicate services once for notes + API payload
    const uniqueServices = [];
    const serviceMap = new Map();
    allServices.forEach(service => {
        if (!service || !service.name || serviceMap.has(service.name)) return;
        const normalized = {
            name: String(service.name).trim(),
            cost: Number.isFinite(parseFloat(service.cost)) ? parseFloat(service.cost) : 0
        };
        serviceMap.set(normalized.name, normalized);
        uniqueServices.push(normalized);
    });
    
    // Build comprehensive notes with payment method, all rooms, and services
    let notes = `Payment Method: ${paymentMethod === 'pay_at_hotel' ? 'Pay at Hotel' : paymentMethod === 'card' ? 'Credit/Debit Card (PayMongo)' : 'GCash / QR Ph'}`;
    notes += ` | Total Rooms: ${totalRooms}`;
    notes += ` | Room Details: ${roomDetails.join(', ')}`;
    notes += ` | Guests: ${totalAdults} Adult(s), ${totalChildren} Child(ren)`;

    const preferredTimes = cart
        .map(item => {
            if (!item.checkin && !item.checkout) return null;
            const hasTime = /[ T]\d{2}:\d{2}/.test(String(item.checkin || '')) || /[ T]\d{2}:\d{2}/.test(String(item.checkout || ''));
            if (!hasTime) return null;
            return `${item.roomName}: in ${item.checkin}, out ${item.checkout}`;
        })
        .filter(Boolean);
    if (preferredTimes.length > 0) {
        notes += ` | Preferred Check-in/out Times: ${preferredTimes.join('; ')}`;
    }
    const extraBedSummary = cart
        .map(item => {
            const info = typeof resolveCartItemExtraBed === 'function' ? resolveCartItemExtraBed(item) : null;
            if (!info || info.extraBeds <= 0 || info.extraBedTotal <= 0) {
                return null;
            }
            return `${item.roomName}: ${info.extraBeds} bed(s) × ${item.nights} night(s) @ ₱${info.extraBedCost.toLocaleString()} = ₱${info.extraBedTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        })
        .filter(Boolean);
    if (extraBedSummary.length > 0) {
        notes += ` | Extra Beds: ${extraBedSummary.join('; ')}`;
    }
    
    // Add services to notes if any
    if (uniqueServices.length > 0) {
        const serviceNames = uniqueServices.map(s => {
            return Number.isFinite(s.cost)
                ? `${s.name} (₱${s.cost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})})`
                : s.name;
        }).join(', ');
        notes += ` | Services: ${serviceNames}`;
    }
    
    // Prepare booking data with room_selections array (same format as admin panel)
    const bookingData = {
        guest_name: guestName,
        guest_email: guestEmail,
        guest_phone: guestPhone,
        guest_address: guestAddress,
        guest_city: guestCity,
        guest_province: guestProvince,
        guest_country: guestCountry,
        guest_zipcode: guestZipcode,
        check_in: firstCheckIn, // Use first item's dates for main booking record
        check_out: firstCheckOut,
        guests: totalGuests > 0 ? totalGuests : 1, // Total guests across all cart rooms
        room_selections: roomSelections, // Array of room selections (same as admin panel)
        extra_services: uniqueServices,
        notes: notes,
        payment_method: paymentMethod
    };
    
    // Debug logging
    console.log('Booking data with room_selections:', bookingData);
    console.log('Total rooms from cart:', totalRooms);
    console.log('Cart items:', cart.length);
    console.log('Room selections:', roomSelections.length);
    
    try {
        const response = await createBooking(bookingData);
        
        if (response.success) {
            // Verify rooms were saved
            const savedRooms = response.rooms_booked || response.booking?.rooms || totalRooms;
            const savedItems = response.booking_items || [];
            console.log('Rooms saved in booking:', savedRooms);
            console.log('Booking items created:', savedItems.length);
            
            if (savedRooms !== totalRooms) {
                console.warn(`Warning: Expected ${totalRooms} rooms, but booking shows ${savedRooms} rooms.`);
            }
            
            if (savedItems.length !== totalRooms) {
                console.warn(`Warning: Expected ${totalRooms} booking items, but ${savedItems.length} were created.`);
            }

            // Store booking result for confirmation page
            const confirmationItems = cart.map(item => {
                const info = typeof resolveCartItemExtraBed === 'function'
                    ? resolveCartItemExtraBed(item)
                    : {
                        extraBeds: parseInt(item.extraBeds, 10) || 0,
                        extraBedCost: parseFloat(item.extraBedCost) || 199,
                        extraBedTotal: 0,
                        roomSubtotal: item.totalAmount || 0
                    };

                return {
                    roomName: item.roomName,
                    checkin: item.checkin,
                    checkout: item.checkout,
                    nights: item.nights,
                    adults: item.adults,
                    children: item.children,
                    rooms: item.rooms,
                    extraBeds: info.extraBeds,
                    extraBedCost: info.extraBedCost,
                    roomSubtotal: info.roomSubtotal,
                    extraBedTotal: info.extraBedTotal,
                    total: info.roomSubtotal + info.extraBedTotal
                };
            });

            const confirmationPayload = {
                booking_number: response.booking_number,
                total_rooms: savedRooms || totalRooms,
                room_details: roomDetails,
                items: confirmationItems,
                extra_services: uniqueServices,
                total_amount: confirmationItems.reduce((sum, item) => sum + (item.total || 0), 0)
                    + uniqueServices.reduce((sum, service) => sum + (parseFloat(service.cost) || 0), 0),
                payment_method: paymentMethod
            };
            localStorage.setItem('bookingResult', JSON.stringify(confirmationPayload));

            // GCash / QR Ph via PayMongo: stay on-site and show QR on confirmation
            if (paymentMethod === 'gcash' && response.payment && response.payment.qr_image_url) {
                clearCartLocal();
                localStorage.removeItem('cartServices');
                sessionStorage.setItem('paymongo_booking_number', response.booking_number);
                if (response.payment.payment_intent_id) {
                    sessionStorage.setItem('paymongo_payment_intent_id', response.payment.payment_intent_id);
                }
                try {
                    sessionStorage.setItem('paymongo_qrph_payment', JSON.stringify(response.payment));
                } catch (e) {}
                sessionStorage.removeItem('paymongo_retry_booking');
                sessionStorage.removeItem('paymongo_retry_method');
                showMessage('Reservation created. Opening QR Ph payment…', 'success');
                submitBtn.textContent = 'Opening QR payment…';
                const invQs = response.payment.invoice_id ? `&invoice=${encodeURIComponent(response.payment.invoice_id)}` : '';
                window.location.href = `booking-confirmation.php?booking=${encodeURIComponent(response.booking_number)}&payment=qrph${invQs}`;
                return;
            }

            // Card via PayMongo Hosted Checkout — redirect to PayMongo
            const cardCheckoutUrl = response.payment && response.payment.checkout_url;
            if (paymentMethod === 'card' && cardCheckoutUrl) {
                clearCartLocal();
                localStorage.removeItem('cartServices');
                sessionStorage.setItem('paymongo_booking_number', response.booking_number);
                if (response.payment.checkout_session_id) {
                    sessionStorage.setItem('paymongo_session_id', response.payment.checkout_session_id);
                }
                // Do not stash payment_intent_id here — Hosted Checkout may create a different PI at pay time.
                // Verify uses checkout_session_id (cs_…) from DB / sessionStorage.
                sessionStorage.removeItem('paymongo_retry_booking');
                sessionStorage.removeItem('paymongo_retry_method');
                showMessage('Reservation created. Redirecting to secure card payment…', 'success');
                submitBtn.textContent = 'Redirecting to PayMongo…';
                window.location.href = cardCheckoutUrl;
                return;
            }

            // Legacy hosted checkout URL (should not be returned for QRPH flow)
            const checkoutUrl = response.payment && response.payment.checkout_url;
            if (paymentMethod === 'gcash' && checkoutUrl) {
                clearCartLocal();
                localStorage.removeItem('cartServices');
                window.location.href = checkoutUrl;
                return;
            }
            
            // Clear cart immediately after successful booking (pay at hotel)
            clearCartLocal();
            
            // Verify cart is cleared
            const remainingCart = getCartLocal();
            if (remainingCart.length > 0) {
                console.warn('Cart was not fully cleared. Remaining items:', remainingCart.length);
                // Force clear again
                if (typeof withExpectedCartMutation === 'function') {
                    withExpectedCartMutation('booking-force-clear-cart', () => {
                        localStorage.removeItem('bookingCart');
                    });
                } else {
                    localStorage.removeItem('bookingCart');
                }
                localStorage.removeItem('cartServices');
            }
            
            // Small delay to ensure cart is cleared before redirect
            setTimeout(() => {
                // Redirect to confirmation page
                window.location.href = `booking-confirmation.php?booking=${response.booking_number}`;
            }, 100);
        }
    } catch (error) {
        // Error message is already displayed by createBooking function
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}

function clearCheckoutInputErrors() {
    const form = document.getElementById('checkout-login-form');
    if (!form) return;
    form.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
}

function failCheckoutValidation(message, fieldId, submitBtn, originalText) {
    showMessage(message, 'error');
    clearCheckoutInputErrors();
    if (fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.classList.add('input-error');
            field.focus();
            field.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}

// Message display helper
function showMessage(message, type = 'info') {
    const isCheckoutPage = !!document.getElementById('checkout-login-form');

    if (isCheckoutPage && typeof showToast === 'function') {
        showToast(message, type);
        return;
    }

    // Remove existing messages
    const existing = document.querySelector('.api-message');
    if (existing) existing.remove();
    
    // Create message element
    const messageEl = document.createElement('div');
    messageEl.className = `api-message ${type}`;
    messageEl.textContent = message;
    messageEl.style.cssText = `
        padding: 1rem;
        margin: 1rem 0;
        border-radius: 4px;
        background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
        color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
        border: 1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#bee5eb'};
    `;
    
    // Insert at top of form
    const form = document.querySelector('form');
    if (form) {
        form.insertBefore(messageEl, form.firstChild);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            messageEl.remove();
        }, 5000);
    }
}

// Show customer information after registration/login
function showCustomerInfo(user, fullUserData = null) {
    const registrationSection = document.getElementById('registration-section');
    const customerInfoSection = document.getElementById('customer-info-section');
    
    if (!customerInfoSection) return;
    
    // Hide registration form
    if (registrationSection) {
        registrationSection.style.display = 'none';
    }
    
    // Update page title
    const pageTitle = document.getElementById('page-title');
    const pageSubtitle = document.getElementById('page-subtitle');
    if (pageTitle) {
        pageTitle.textContent = 'Your Account';
    }
    if (pageSubtitle) {
        pageSubtitle.textContent = 'Review your information and complete your booking.';
    }
    
    // Show customer info section
    customerInfoSection.style.display = 'block';
    
    // Populate customer info
    const customerName = user.name || (fullUserData ? `${fullUserData.first_name} ${fullUserData.last_name}` : '-');
    document.getElementById('customer-name').textContent = customerName;
    document.getElementById('customer-email').textContent = user.email || '-';
    
    // Get phone and address from form if available, otherwise use provided data
    let phone = fullUserData?.phone || '';
    let address = fullUserData?.address || '';
    
    // Try to get from form fields if still visible
    const phoneField = document.getElementById('reg-phone');
    const addressField = document.getElementById('reg-address');
    if (phoneField && phoneField.value.trim()) {
        phone = phoneField.value.trim();
    }
    if (addressField && addressField.value.trim()) {
        address = addressField.value.trim();
    }
    
    document.getElementById('customer-phone').textContent = phone || '-';
    document.getElementById('customer-address').textContent = address || '-';
    
    // Store full user data in localStorage for later use
    if (fullUserData) {
        const fullUser = {
            ...user,
            phone: phone,
            address: address
        };
        localStorage.setItem('user', JSON.stringify(fullUser));
    }
}

// Check for booking in localStorage and display it
function checkAndShowBooking() {
    const bookingDetails = localStorage.getItem('bookingDetails');
    
    if (!bookingDetails) {
        // No booking, hide booking sections
        const bookingSection = document.getElementById('booking-summary-section');
        const paymentSection = document.getElementById('payment-section');
        if (bookingSection) bookingSection.style.display = 'none';
        if (paymentSection) paymentSection.style.display = 'none';
        return;
    }
    
    try {
        const booking = JSON.parse(bookingDetails);
        displayBookingSummary(booking);
        showPaymentSection();
    } catch (error) {
        console.error('Error parsing booking details:', error);
    }
}

// Display booking summary
function displayBookingSummary(bookingDetails) {
    const bookingSection = document.getElementById('booking-summary-section');
    if (!bookingSection) return;
    
    bookingSection.style.display = 'block';
    
    // Populate booking summary
    if (document.getElementById('summary-room-image')) {
        document.getElementById('summary-room-image').src = bookingDetails.imageUrl || '';
    }
    if (document.getElementById('summary-room-name')) {
        document.getElementById('summary-room-name').textContent = bookingDetails.roomName || '-';
    }
    if (document.getElementById('summary-checkin')) {
        document.getElementById('summary-checkin').textContent = bookingDetails.checkin || '-';
    }
    if (document.getElementById('summary-checkout')) {
        document.getElementById('summary-checkout').textContent = bookingDetails.checkout || '-';
    }
    if (document.getElementById('summary-guests')) {
        const guests = `Adults: ${bookingDetails.adults || 0}, Children: ${bookingDetails.children || 0}`;
        document.getElementById('summary-guests').textContent = guests;
    }
    if (document.getElementById('summary-rooms')) {
        document.getElementById('summary-rooms').textContent = bookingDetails.rooms || '1';
    }
    if (document.getElementById('summary-total')) {
        document.getElementById('summary-total').textContent = bookingDetails.total || '₱0';
    }
}

// Show payment section
function showPaymentSection() {
    const paymentSection = document.getElementById('payment-section');
    if (!paymentSection) return;
    
    paymentSection.style.display = 'block';
    
    // Scroll to payment section smoothly
    setTimeout(() => {
        paymentSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 100);
}

// Handle payment form submission
function handlePaymentSubmit(e) {
    e.preventDefault();
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Processing...';
    
    // Get booking details from localStorage
    const bookingDetails = JSON.parse(localStorage.getItem('bookingDetails'));
    if (!bookingDetails) {
        showMessage('Your booking session has expired. Please select a room and dates again to continue.', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        return;
    }
    
    // Get user info
    const userStr = localStorage.getItem('user');
    if (!userStr) {
        showMessage('Please log in or create an account to complete your booking.', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        return;
    }
    
    const user = JSON.parse(userStr);
    
    // Get room ID - we'll need to map room name to ID or get from API
    // For now, we'll use a default or get from localStorage
    const roomId = parseInt(localStorage.getItem('selected_room_id') || '1');
    
    // Prepare booking data
    const bookingData = {
        room_id: roomId,
        guest_name: user.name,
        guest_email: user.email,
        guest_phone: document.getElementById('customer-phone')?.textContent || '',
        check_in: bookingDetails.checkin,
        check_out: bookingDetails.checkout,
        guests: parseInt(bookingDetails.adults) + parseInt(bookingDetails.children || 0),
        notes: ''
    };
    
    // Create booking
    createBooking(bookingData)
        .then((response) => {
            if (response.success) {
                // Show success message
                showMessage('Your reservation is being processed. Redirecting to confirmation page...', 'success');
                
                // Clear booking details
                localStorage.removeItem('bookingDetails');
                
                // Redirect to confirmation page
                setTimeout(() => {
                    window.location.href = `booking-confirmation.php?booking=${response.booking_number}`;
                }, 1500);
            }
        })
        .catch((error) => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const pendingLoginMessage = sessionStorage.getItem('checkoutLoginMessage');
    if (pendingLoginMessage) {
        sessionStorage.removeItem('checkoutLoginMessage');
        showMessage(pendingLoginMessage, 'success');
    }

    // Setup registration form if on registration page
    if (document.getElementById('registration-form')) {
        setupRegistrationForm();
    }
    
    // Setup payment form if on registration page
    const paymentForm = document.getElementById('payment-form');
    if (paymentForm) {
        paymentForm.addEventListener('submit', handlePaymentSubmit);
    }
    
    // Setup checkout login if on checkout page
    const loginButton = document.getElementById('login-button');
    if (loginButton) {
        loginButton.addEventListener('click', async (e) => {
            e.preventDefault();
            await handleCheckoutLogin();
        });
    }
    
    // Setup checkout form submission
    const checkoutForm = document.getElementById('checkout-login-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', handleCheckoutSubmit);
    }
    
    // Check if user is already logged in
    checkUserLogin().then(() => {
        // If user is logged in and on registration page, show their info
        const userStr = localStorage.getItem('user');
        if (userStr && document.getElementById('customer-info-section')) {
            try {
                const user = JSON.parse(userStr);
                showCustomerInfo(user);
                checkAndShowBooking();
            } catch (e) {
                console.error('Error loading user info:', e);
            }
        }
    });
});

// Check if user is logged in
async function checkUserLogin() {
    try {
        const response = await API.auth.check();
        if (response.success && response.logged_in) {
            localStorage.setItem('user', JSON.stringify(response.user));

            if (typeof window.BODARE_syncPushToken === 'function') {
                window.BODARE_syncPushToken();
            }
            
            // If on checkout, restore the complete authenticated state and profile.
            if (document.getElementById('checkout-login-form')) {
                await showAuthenticatedCheckout(response.user);
            }
            
            return response.user;
        }

        // Session missing — logout the local account
        if (localStorage.getItem('user')) {
            if (typeof API.auth.clearLocalSession === 'function') {
                API.auth.clearLocalSession();
            } else {
                localStorage.removeItem('user');
            }
        }
        return null;
    } catch (error) {
        console.log('User not logged in');
        return null;
    }
}


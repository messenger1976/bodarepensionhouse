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
            const response = await API.auth.register(userData);
            
            if (response.success) {
                // Show success message
                showMessage('Welcome! Your account has been created successfully. Redirecting to your dashboard...', 'success');
                
                // Store user info
                localStorage.setItem('user', JSON.stringify(response.user));
                
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

// Checkout login handler
async function handleCheckoutLogin() {
    const emailInput = document.getElementById('login-email');
    const passwordInput = document.getElementById('login-password');
    
    if (!emailInput || !passwordInput) {
        showMessage('Login form fields not found.', 'error');
        return false;
    }
    
    const email = emailInput.value.trim().toLowerCase();
    const password = passwordInput.value;
    
    if (!email || !password) {
        showMessage('Please enter both your email address and password to continue.', 'error');
        return false;
    }
    
    try {
        const response = await API.auth.login(email, password);
        
        if (response.success) {
            // Store user info
            localStorage.setItem('user', JSON.stringify(response.user));
            
            // Show success message
            showMessage('Welcome back! You have successfully logged in.', 'success');
            
            // Show payment section
            document.getElementById('login-fields').style.display = 'none';
            document.getElementById('payment-section').style.display = 'block';
            
            const loginHeader = document.querySelector('.checkout-auth-header h2');
            if (loginHeader) {
                loginHeader.textContent = 'Welcome, ' + response.user.name;
            }
            
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
        } else if (error.response && error.response.message) {
            // Check response message
            const responseMessage = error.response.message.toLowerCase();
            if (responseMessage.includes('not available') || responseMessage.includes('unavailable') || responseMessage.includes('conflict')) {
                errorMessage = 'Sorry, the selected room is not available for your chosen dates. Please select different dates or try a different room.';
            } else if (responseMessage.includes('validation')) {
                errorMessage = 'Please check your booking details and ensure all information is correct.';
            } else {
                errorMessage = error.response.message;
            }
        } else if (error.message) {
            // Check error message
            if (error.message.includes('not available') || error.message.includes('unavailable') || error.message.includes('conflict')) {
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
    
    // Get cart items instead of single bookingDetails
    // getCart is defined in script.js, but we'll define it here if not available
    function getCartLocal() {
        const cartStr = localStorage.getItem('bookingCart');
        return cartStr ? JSON.parse(cartStr) : [];
    }
    
    function clearCartLocal() {
        // Clear cart data
        localStorage.removeItem('bookingCart');
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
    
    const cart = getCartLocal();
    if (!cart || cart.length === 0) {
        showMessage('Your cart is empty. Please add rooms to your cart first.', 'error');
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
            showMessage('Please enter your first name and last name.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
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
            showMessage('Please enter your email address.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            return;
        }
        
        if (!guestPhone) {
            showMessage('Please enter your contact number.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            return;
        }
        
        if (!guestAddress) {
            showMessage('Please enter your street address.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            return;
        }
        
        if (!guestCity) {
            showMessage('Please enter your city.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            return;
        }
        
        if (!guestProvince) {
            showMessage('Please enter your province.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            return;
        }
        
        if (!guestCountry) {
            showMessage('Please enter your country.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            return;
        }
        
        if (!guestZipcode) {
            showMessage('Please enter your zip/postal code.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
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
    let firstGuests = null;
    
    // Process each cart item separately
    for (const item of cart) {
        // Find room ID by matching room name
        const matchedRoom = roomsList.find(room => 
            room.room_name === item.roomName || 
            room.room_name.toLowerCase().includes(item.roomName.toLowerCase()) ||
            item.roomName.toLowerCase().includes(room.room_name.toLowerCase())
        );
        
        if (!matchedRoom) {
            showMessage(`Room "${item.roomName}" not found in system.`, 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            return;
        }
        
        // Format dates to YYYY-MM-DD
        let itemCheckIn = item.checkin;
        let itemCheckOut = item.checkout;
        
        if (itemCheckIn && !/^\d{4}-\d{2}-\d{2}$/.test(itemCheckIn)) {
            const d = new Date(itemCheckIn + 'T12:00:00');
            itemCheckIn = d.getFullYear() + '-' + 
                          String(d.getMonth() + 1).padStart(2, '0') + '-' + 
                          String(d.getDate()).padStart(2, '0');
        }
        
        if (itemCheckOut && !/^\d{4}-\d{2}-\d{2}$/.test(itemCheckOut)) {
            const d = new Date(itemCheckOut + 'T12:00:00');
            itemCheckOut = d.getFullYear() + '-' + 
                          String(d.getMonth() + 1).padStart(2, '0') + '-' + 
                          String(d.getDate()).padStart(2, '0');
        }
        
        const itemRooms = parseInt(item.rooms) || 1;
        const itemAdults = parseInt(item.adults) || 0;
        const itemChildren = parseInt(item.children) || 0;
        const itemGuests = itemAdults + itemChildren;
        
        // Store first item's dates and guests for main booking record
        if (firstCheckIn === null) {
            firstCheckIn = itemCheckIn;
            firstCheckOut = itemCheckOut;
            firstGuests = itemGuests;
        }
        
        // Add room selection (same format as admin panel)
        roomSelections.push({
            room_id: matchedRoom.id,
            quantity: itemRooms,
            check_in: itemCheckIn,
            check_out: itemCheckOut,
            guests: itemGuests
        });
        
        totalRooms += itemRooms;
        totalAdults += itemAdults;
        totalChildren += itemChildren;
        
        // Collect services from all items
        if (item.services && item.services.length > 0) {
            allServices = allServices.concat(item.services);
        }
        
        // Track room details for notes
        roomDetails.push(`${item.roomName} (${itemRooms} room${itemRooms > 1 ? 's' : ''})`);
    }
    
    // Calculate total guests
    const totalGuests = totalAdults + totalChildren;
    
    // Build comprehensive notes with payment method, all rooms, and services
    let notes = `Payment Method: ${paymentMethod === 'pay_at_hotel' ? 'Pay at Hotel' : paymentMethod === 'card' ? 'Credit/Debit Card' : 'GCash'}`;
    notes += ` | Total Rooms: ${totalRooms}`;
    notes += ` | Room Details: ${roomDetails.join(', ')}`;
    notes += ` | Guests: ${totalAdults} Adult(s), ${totalChildren} Child(ren)`;
    
    // Add services to notes if any
    if (allServices.length > 0) {
        // Remove duplicate services
        const uniqueServices = [];
        const serviceMap = new Map();
        allServices.forEach(service => {
            if (!serviceMap.has(service.name)) {
                serviceMap.set(service.name, service);
                uniqueServices.push(service);
            }
        });
        const serviceNames = uniqueServices.map(s => s.name).join(', ');
        notes += ` | Services: ${serviceNames}`;
    }
    
    // Add card details to notes if card payment
    if (paymentMethod === 'card') {
        const cardNumber = document.getElementById('card-number')?.value;
        if (cardNumber) {
            const last4 = cardNumber.replace(/\s/g, '').slice(-4);
            notes += ` (Card ending in ${last4})`;
        }
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
        guests: firstGuests, // Use first item's guests for main booking record
        room_selections: roomSelections, // Array of room selections (same as admin panel)
        notes: notes
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
            
            // Clear cart immediately after successful booking
            clearCartLocal();
            
            // Verify cart is cleared
            const remainingCart = getCartLocal();
            if (remainingCart.length > 0) {
                console.warn('Cart was not fully cleared. Remaining items:', remainingCart.length);
                // Force clear again
                localStorage.removeItem('bookingCart');
                localStorage.removeItem('cartServices');
            }
            
            // Store booking result for confirmation page
            localStorage.setItem('bookingResult', JSON.stringify({
                booking_number: response.booking_number,
                total_rooms: savedRooms || totalRooms,
                room_details: roomDetails
            }));
            
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
        
        // Scroll to top to show error message
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

// Message display helper
function showMessage(message, type = 'info') {
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
            
            // If on checkout page, show payment section
            if (document.getElementById('payment-section') && document.getElementById('login-fields')) {
                document.getElementById('login-fields').style.display = 'none';
                document.getElementById('payment-section').style.display = 'block';
            }
            
            return response.user;
        }
        return null;
    } catch (error) {
        console.log('User not logged in');
        return null;
    }
}


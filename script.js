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

const DEFAULT_ROOM_IMAGE = 'img/og-default.jpg';

// Uploaded room images are stored relative to the admin app root.
function resolveRoomImagePath(imagePath) {
    if (!imagePath) return null;
    const clean = String(imagePath).trim().replace(/^\/+/, '');
    if (clean === '') return null;
    if (/^https?:\/\//i.test(clean)) return clean;
    return clean.startsWith('img/rooms/') ? `admin/${clean}` : clean;
}

// Collect every usable image for a room, newest uploads first, then static art.
function collectRoomImages(apiRoom, roomKey) {
    const images = [];
    const addImage = (path) => {
        const resolved = resolveRoomImagePath(path);
        if (resolved && !images.includes(resolved)) {
            images.push(resolved);
        }
    };

    if (apiRoom) {
        if (apiRoom.primary_image) addImage(apiRoom.primary_image.image_path);
        (apiRoom.images || []).forEach(image => addImage(image.image_path));
    }

    if (roomKey && roomData[roomKey]) {
        addImage(roomData[roomKey].imageUrl);
    } else if (roomKey) {
        addImage(`img/${roomKey}.jpg`);
    }

    return images;
}

function resolveRoomImage(apiRoom, roomKey) {
    const images = collectRoomImages(apiRoom, roomKey);
    return images.length > 0 ? images[0] : DEFAULT_ROOM_IMAGE;
}

// --- CART MANAGEMENT FUNCTIONS ---

let bookingCartMutationAllowed = false;

function withExpectedCartMutation(actionLabel, mutator) {
    bookingCartMutationAllowed = true;
    try {
        return mutator();
    } finally {
        bookingCartMutationAllowed = false;
    }
}

function installBookingCartGuard() {
    if (window.__bookingCartGuardInstalled) return;
    window.__bookingCartGuardInstalled = true;

    const originalSetItem = localStorage.setItem.bind(localStorage);
    const originalRemoveItem = localStorage.removeItem.bind(localStorage);

    localStorage.setItem = function(key, value) {
        if (key === 'bookingCart' && !bookingCartMutationAllowed) {
            console.warn('[Cart Guard] Unexpected bookingCart write detected.', {
                key,
                valuePreview: typeof value === 'string' ? value.substring(0, 120) : value,
                stack: new Error().stack
            });
        }
        return originalSetItem(key, value);
    };

    localStorage.removeItem = function(key) {
        if (key === 'bookingCart' && !bookingCartMutationAllowed) {
            console.warn('[Cart Guard] Unexpected bookingCart removal detected.', {
                key,
                stack: new Error().stack
            });
        }
        return originalRemoveItem(key);
    };
}

installBookingCartGuard();

// Get cart from localStorage
function getCart() {
    const cartStr = localStorage.getItem('bookingCart');
    return cartStr ? JSON.parse(cartStr) : [];
}

// Capture cart-related storage so auth flows can safely preserve it.
function getCartStorageSnapshot() {
    return {
        bookingCart: localStorage.getItem('bookingCart'),
        cartServices: localStorage.getItem('cartServices'),
        bookingDetails: localStorage.getItem('bookingDetails')
    };
}

function restoreCartStorageSnapshot(snapshot) {
    if (!snapshot) return;

    withExpectedCartMutation('restore-cart-snapshot', () => {
        if (snapshot.bookingCart !== null) {
            localStorage.setItem('bookingCart', snapshot.bookingCart);
        }
    });

    if (snapshot.cartServices !== null) {
        localStorage.setItem('cartServices', snapshot.cartServices);
    }
    if (snapshot.bookingDetails !== null) {
        localStorage.setItem('bookingDetails', snapshot.bookingDetails);
    }

    updateCartBadge();
}

// Save cart to localStorage
function saveCart(cart) {
    withExpectedCartMutation('save-cart', () => {
        localStorage.setItem('bookingCart', JSON.stringify(cart));
    });
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

function formatPeso(amount) {
    return `₱${Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function getDefaultExtraBedPrice() {
    const value = parseFloat(window.BODARE_EXTRA_BED_PRICE);
    return Number.isFinite(value) && value > 0 ? value : 199;
}

function computeRoomSubtotal(item) {
    const nights = Math.max(1, parseInt(item.nights, 10) || 1);
    const rooms = Math.max(1, parseInt(item.rooms, 10) || 1);
    const adults = parseInt(item.adults, 10) || 0;
    const children = parseInt(item.children, 10) || 0;
    const guests = Math.max(1, adults + children);
    const price = parseFloat(item.price) || 0;
    const priceUnit = String(item.priceUnit || 'per night').toLowerCase();

    if (priceUnit.includes('head')) {
        return price * guests * nights * rooms;
    }

    return price * nights * rooms;
}

function resolveCartItemExtraBed(item) {
    let extraBeds = parseInt(item.extraBeds ?? item.extra_beds, 10) || 0;
    let extraBedCost = parseFloat(item.extraBedCost ?? item.extra_bed_cost ?? item.extraBedPrice) || 0;
    if (extraBedCost <= 0) {
        extraBedCost = getDefaultExtraBedPrice();
    }

    const nights = Math.max(1, parseInt(item.nights, 10) || 1);
    const roomTotal = computeRoomSubtotal(item);
    const itemTotal = item.totalAmount || parseFloat(String(item.total || '').replace(/[₱,]/g, '')) || 0;

    if (extraBeds > 0) {
        const extraBedTotal = extraBeds * extraBedCost * nights;
        return {
            extraBeds,
            extraBedCost,
            extraBedTotal,
            roomSubtotal: roomTotal,
            itemTotal: roomTotal + extraBedTotal
        };
    }

    if (itemTotal > roomTotal + 0.009) {
        const diff = Math.round((itemTotal - roomTotal) * 100) / 100;
        const perBedNight = extraBedCost * nights;
        if (perBedNight > 0) {
            extraBeds = Math.max(1, Math.round(diff / perBedNight));
            const extraBedTotal = extraBeds * extraBedCost * nights;
            return {
                extraBeds,
                extraBedCost,
                extraBedTotal,
                roomSubtotal: roomTotal,
                itemTotal: roomTotal + extraBedTotal
            };
        }
    }

    return {
        extraBeds: 0,
        extraBedCost,
        extraBedTotal: 0,
        roomSubtotal: itemTotal || roomTotal,
        itemTotal: itemTotal || roomTotal
    };
}

function applyCartItemPricing(item) {
    const pricing = resolveCartItemExtraBed(item);
    return {
        ...item,
        extraBeds: pricing.extraBeds,
        extraBedCost: pricing.extraBedCost,
        totalAmount: pricing.itemTotal,
        total: formatPeso(pricing.itemTotal)
    };
}

function updateCartExtraBeds(cartId, delta) {
    const cart = getCart();
    const itemIndex = cart.findIndex(item => item.cartId === cartId);
    if (itemIndex === -1) {
        return;
    }

    const item = cart[itemIndex];
    const current = parseInt(item.extraBeds, 10) || 0;
    const next = Math.max(0, current + delta);
    const updated = applyCartItemPricing({
        ...item,
        extraBeds: next,
        extraBedCost: parseFloat(item.extraBedCost) || getDefaultExtraBedPrice()
    });

    cart[itemIndex] = updated;
    saveCart(cart);
}

function getCartItemExtraBedTotal(item) {
    return resolveCartItemExtraBed(item).extraBedTotal;
}

function getCartItemRoomSubtotal(item) {
    return resolveCartItemExtraBed(item).roomSubtotal;
}

// Get cart total
function getCartTotal() {
    const cart = getCart();
    return cart.reduce((total, item) => {
        const itemTotal = item.totalAmount || parseFloat(String(item.total || '').replace(/[₱,]/g, '')) || 0;
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

// Update header auth buttons globally for pages using shared header include.
function setHeaderAuthState(loggedIn) {
    const onLoginPage = window.location.pathname.includes('login.php');

    document.querySelectorAll('.app-auth-login').forEach((el) => {
        el.style.display = loggedIn || onLoginPage ? 'none' : 'inline-flex';
    });

    document.querySelectorAll('.app-auth-account').forEach((el) => {
        el.style.display = loggedIn ? 'inline-flex' : 'none';
    });

    document.querySelectorAll('.app-auth-logout').forEach((el) => {
        el.style.display = loggedIn ? 'inline-flex' : 'none';
    });
}

function updateAppTabAuth(loggedIn) {
    const profileTab = document.getElementById('nav-tab-profile');
    const bookingsTab = document.getElementById('nav-tab-bookings');
    if (profileTab) {
        profileTab.setAttribute('href', loggedIn ? 'customer-dashboard.php' : 'login.php');
    }
    if (bookingsTab && loggedIn) {
        bookingsTab.setAttribute('href', 'customer-dashboard.php');
    } else if (bookingsTab) {
        bookingsTab.setAttribute('href', 'rooms.php');
    }
}

async function updateHeaderAuthButtons() {
    const showLoggedOut = () => {
        setHeaderAuthState(false);
        updateAppTabAuth(false);
    };

    const showLoggedIn = () => {
        setHeaderAuthState(true);
        updateAppTabAuth(true);
    };

    try {
        if (typeof API !== 'undefined' && API.auth && typeof API.auth.check === 'function') {
            const response = await API.auth.check();
            if (response.success && response.logged_in) {
                showLoggedIn();
                return;
            }

            if (localStorage.getItem('user')) {
                if (typeof API.auth.clearLocalSession === 'function') {
                    API.auth.clearLocalSession();
                } else {
                    localStorage.removeItem('user');
                    if (typeof clearAuthToken === 'function') { clearAuthToken(); } else { localStorage.removeItem('bodare_auth_token'); }
                }
            }
            showLoggedOut();
            return;
        }
    } catch (error) {
        // API unreachable: fall back to local storage below.
    }

    const userStr = localStorage.getItem('user');
    if (userStr) {
        showLoggedIn();
    } else {
        showLoggedOut();
    }
}

function closeMobileNav() {
    const mobileNav = document.getElementById('app-mobile-nav');
    const mobileMenuToggle = document.querySelector('.app-mobile-menu-btn');
    if (!mobileNav || !mobileMenuToggle) {
        return;
    }
    mobileNav.classList.remove('is-open');
    mobileNav.setAttribute('aria-hidden', 'true');
    mobileMenuToggle.classList.remove('active');
    mobileMenuToggle.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('menu-open');
}

function openMobileNav() {
    const mobileNav = document.getElementById('app-mobile-nav');
    const mobileMenuToggle = document.querySelector('.app-mobile-menu-btn');
    if (!mobileNav || !mobileMenuToggle) {
        return;
    }
    mobileNav.classList.add('is-open');
    mobileNav.setAttribute('aria-hidden', 'false');
    mobileMenuToggle.classList.add('active');
    mobileMenuToggle.setAttribute('aria-expanded', 'true');
    document.body.classList.add('menu-open');
}

function setupMobileNav() {
    const mobileNav = document.getElementById('app-mobile-nav');
    const mobileMenuToggle = document.querySelector('.app-mobile-menu-btn');
    if (!mobileNav || !mobileMenuToggle) {
        return;
    }

    mobileMenuToggle.addEventListener('click', () => {
        const isOpen = mobileNav.classList.contains('is-open');
        if (isOpen) {
            closeMobileNav();
        } else {
            openMobileNav();
        }
    });

    mobileNav.querySelectorAll('.app-mobile-nav-backdrop, .app-mobile-nav-close').forEach((el) => {
        el.addEventListener('click', closeMobileNav);
    });

    mobileNav.querySelectorAll('.app-mobile-nav-link, .app-mobile-nav-btn').forEach((el) => {
        el.addEventListener('click', () => {
            if (el.classList.contains('app-auth-logout')) {
                return;
            }
            closeMobileNav();
        });
    });
}

async function handleHeaderLogout() {
    try {
        if (typeof API !== 'undefined' && API.auth && typeof API.auth.logout === 'function') {
            await API.auth.logout();
        }
    } catch (error) {
        console.error('Logout error:', error);
    } finally {
        localStorage.removeItem('user');
        if (typeof clearAuthToken === 'function') {
            clearAuthToken();
        } else {
            localStorage.removeItem('bodare_auth_token');
        }
        if (typeof clearBookingCartData === 'function') {
            clearBookingCartData();
        }
        window.location.href = 'index.php';
    }
}

function setupHeaderLogoutButtons() {
    document.querySelectorAll('.app-auth-logout').forEach((btn) => {
        if (btn.dataset.logoutBound === '1') {
            return;
        }
        btn.dataset.logoutBound = '1';
        btn.addEventListener('click', (event) => {
            event.preventDefault();
            handleHeaderLogout();
        });
    });
}

// Clear booking cart data (used after successful booking or explicit logout).
function clearBookingCartData() {
    withExpectedCartMutation('clear-booking-cart', () => {
        localStorage.removeItem('bookingCart');
    });
    localStorage.removeItem('cartServices');
    localStorage.removeItem('bookingDetails');
    updateCartBadge();
}

// Backward-compatible alias used by existing code.
function clearCart() {
    clearBookingCartData();
}

function setupServiceWorkerAutoUpdate() {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    if (window.__swAutoUpdateInitialized) {
        return;
    }
    window.__swAutoUpdateInitialized = true;

    let hasRefreshed = false;
    navigator.serviceWorker.addEventListener('controllerchange', () => {
        if (hasRefreshed) return;
        hasRefreshed = true;
        window.location.reload();
    });

    window.addEventListener('load', async () => {
        if (window.Capacitor) {
            document.documentElement.classList.add('is-capacitor');
            return;
        }
        try {
            const basePath = (typeof window.BODARE_BASE_PATH === 'string' && window.BODARE_BASE_PATH)
                ? window.BODARE_BASE_PATH
                : '/';
            const swPath = (basePath.endsWith('/') ? basePath : basePath + '/') + 'sw.js';
            const registration = await navigator.serviceWorker.register(swPath, { scope: basePath });

            // Ask the SW to check for updates periodically.
            setInterval(() => {
                registration.update().catch(() => {});
            }, 60 * 60 * 1000);

            registration.addEventListener('updatefound', () => {
                const newWorker = registration.installing;
                if (!newWorker) return;

                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        const shouldRefresh = window.confirm('A new version is available. Reload now to update?');
                        if (shouldRefresh) {
                            if (registration.waiting) {
                                registration.waiting.postMessage({ type: 'SKIP_WAITING' });
                            } else {
                                window.location.reload();
                            }
                        }
                    }
                });
            });
        } catch (error) {
            console.log('ServiceWorker registration failed:', error);
        }
    });
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

    // Mobile nav drawer
    setupMobileNav();
    setupHeaderLogoutButtons();

    if (document.body.querySelector('.room-content-section')) {
        populateRoomDetails();
        setupBookingWidget();
        setupAvailabilityCalendar();
        setupGalleryLightbox();
    }
    else if (document.body.querySelector('.checkout-layout')) {
        populateCheckoutPage();
    }
    else if (document.body.querySelector('.gallery-grid')) {
        setupGalleryLightbox(); 
    }
    
    // Update cart badge on page load
    updateCartBadge();

    // Ensure Login/My Account visibility is correct across all public pages.
    updateHeaderAuthButtons();

    // Register SW once globally and refresh users when updates are available.
    setupServiceWorkerAutoUpdate();

    // Landing page scroll reveals and soft motion
    setupLandingPageMotion();

    // Password fields: solid-circle mask + show/hide eye toggle
    setupPasswordFields();
});

function setupPasswordFields(root = document) {
    const inputs = root.querySelectorAll('input[type="password"]');
    inputs.forEach((input) => {
        if (input.closest('.password-field') || input.dataset.passwordEnhanced === '1') {
            return;
        }
        input.dataset.passwordEnhanced = '1';

        const wrap = document.createElement('div');
        wrap.className = 'password-field';

        const parent = input.parentNode;
        parent.insertBefore(wrap, input);
        wrap.appendChild(input);

        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'password-toggle';
        toggle.setAttribute('aria-label', 'Show password');
        toggle.setAttribute('aria-pressed', 'false');
        toggle.innerHTML =
            '<svg class="password-toggle-icon password-toggle-icon--show" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' +
            '<path fill="currentColor" d="M12 5c-5.5 0-9.5 4.5-10.7 6.2a1.2 1.2 0 0 0 0 1.6C2.5 14.5 6.5 19 12 19s9.5-4.5 10.7-6.2a1.2 1.2 0 0 0 0-1.6C21.5 9.5 17.5 5 12 5zm0 12c-3.9 0-7.1-3.1-8.4-5C4.9 10.1 8.1 7 12 7s7.1 3.1 8.4 5c-1.3 1.9-4.5 5-8.4 5zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>' +
            '</svg>' +
            '<svg class="password-toggle-icon password-toggle-icon--hide" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' +
            '<path fill="currentColor" d="M3.3 2.3 2 3.6l3.1 3.1C3.4 8.1 2.1 9.6 1.3 10.6a1.2 1.2 0 0 0 0 1.6C2.5 14.5 6.5 19 12 19c2 0 3.8-.6 5.3-1.5l3.1 3.1 1.3-1.3L3.3 2.3zM12 17c-3.9 0-7.1-3.1-8.4-5 .7-1 1.9-2.4 3.5-3.4l1.6 1.6A3 3 0 0 0 12 15c.4 0 .8-.1 1.1-.2l1.5 1.5c-.8.4-1.7.7-2.6.7zm8.4-5c-.4.6-1 1.3-1.7 2l1.4 1.4c1-.9 1.8-1.9 2.3-2.6a1.2 1.2 0 0 0 0-1.6C21.5 9.5 17.5 5 12 5c-1.1 0-2.1.2-3.1.5l1.6 1.6c.5-.1 1-.1 1.5-.1 3.9 0 7.1 3.1 8.4 5z"/>' +
            '</svg>';

        wrap.appendChild(toggle);

        toggle.addEventListener('click', () => {
            const revealing = input.type === 'password';
            input.type = revealing ? 'text' : 'password';
            wrap.classList.toggle('is-revealed', revealing);
            toggle.setAttribute('aria-pressed', revealing ? 'true' : 'false');
            toggle.setAttribute('aria-label', revealing ? 'Hide password' : 'Show password');
            input.focus({ preventScroll: true });
        });
    });
}

function setupLandingPageMotion() {
    const landing = document.querySelector('.lp');
    if (!landing) {
        return;
    }

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const reveals = landing.querySelectorAll('.lp-reveal');

    if (reduceMotion || !('IntersectionObserver' in window)) {
        reveals.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }
            entry.target.classList.add('is-visible');
            obs.unobserve(entry.target);
        });
    }, {
        threshold: 0.12,
        rootMargin: '0px 0px -8% 0px'
    });

    reveals.forEach((el) => observer.observe(el));
}

// --- ROOM DETAIL PAGE FUNCTIONS ---

// --- Asia/Manila timezone helpers (transactions + “today”) ---
window.BODARE_TIMEZONE = window.BODARE_TIMEZONE || 'Asia/Manila';

function bodareTodayYmd(date) {
    const d = date instanceof Date ? date : new Date();
    return new Intl.DateTimeFormat('en-CA', {
        timeZone: window.BODARE_TIMEZONE,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    }).format(d);
}

/** Midnight Date for Manila's current calendar day (for past-date checks). */
function bodareManilaTodayDate() {
    const parts = bodareTodayYmd().split('-').map(Number);
    return new Date(parts[0], parts[1] - 1, parts[2], 0, 0, 0, 0);
}

function bodareParseServerDate(value) {
    if (value == null || value === '') {
        return null;
    }
    if (value instanceof Date) {
        return Number.isNaN(value.getTime()) ? null : value;
    }
    const raw = String(value).trim();
    if (!raw) {
        return null;
    }
    if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
        const parts = raw.split('-');
        return new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]), 0, 0, 0, 0);
    }
    let normalized = raw.replace(' ', 'T');
    const hasTz = /[zZ]|[+-]\d{2}:?\d{2}$/.test(normalized);
    if (!hasTz) {
        normalized += '+08:00';
    }
    const parsed = new Date(normalized);
    return Number.isNaN(parsed.getTime()) ? null : parsed;
}

// Helper function to format date from local calendar components (YYYY-MM-DD)
// Use this for booking date math — not toISOString() which shifts to UTC.
function formatDateLocal(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function formatDateTimeLocal(date) {
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${formatDateLocal(date)} ${hours}:${minutes}`;
}

function parseHotelTime(value, fallbackHour, fallbackMinute) {
    const match = String(value || '').match(/^(\d{1,2}):(\d{2})/);
    if (!match) {
        return { hour: fallbackHour, minute: fallbackMinute };
    }
    return {
        hour: Math.min(23, Math.max(0, parseInt(match[1], 10))),
        minute: Math.min(59, Math.max(0, parseInt(match[2], 10))),
    };
}

function getHotelCheckTimes() {
    const checkIn = parseHotelTime(window.BODARE_CHECK_IN_TIME, 14, 0);
    const checkOut = parseHotelTime(window.BODARE_CHECK_OUT_TIME, 12, 0);
    return { checkIn, checkOut };
}

function withHotelTime(date, hour, minute) {
    const next = new Date(date.getFullYear(), date.getMonth(), date.getDate(), hour, minute, 0, 0);
    return next;
}

// Parse YYYY-MM-DD or YYYY-MM-DD HH:mm as local time
function parseDateLocal(dateString) {
    if (!dateString) return null;
    const match = String(dateString).trim().match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/);
    if (!match) return null;
    return new Date(
        Number(match[1]),
        Number(match[2]) - 1,
        Number(match[3]),
        Number(match[4] || 0),
        Number(match[5] || 0),
        0,
        0
    );
}

function toApiDate(value) {
    if (!value) return value;
    const match = String(value).match(/^(\d{4}-\d{2}-\d{2})/);
    if (match) return match[1];
    const parsed = parseDateLocal(value);
    return parsed ? formatDateLocal(parsed) : value;
}

function calendarNightCount(checkInValue, checkOutValue) {
    const checkinDate = parseDateLocal(checkInValue);
    const checkoutDate = parseDateLocal(checkOutValue);
    if (!checkinDate || !checkoutDate) return 1;
    const start = new Date(checkinDate.getFullYear(), checkinDate.getMonth(), checkinDate.getDate());
    const end = new Date(checkoutDate.getFullYear(), checkoutDate.getMonth(), checkoutDate.getDate());
    return Math.max(1, Math.round((end - start) / (1000 * 60 * 60 * 24)));
}

async function fetchRoomByCode(roomKey) {
    if (typeof API === 'undefined' || !API.booking || typeof API.booking.getRoomByCode !== 'function') {
        return null;
    }

    try {
        const response = await API.booking.getRoomByCode(roomKey);
        return response && response.success && response.room ? response.room : null;
    } catch (error) {
        console.error('Unable to load room details from API:', error);
        return null;
    }
}

function buildRoomEntry(apiRoom, roomKey) {
    const fallback = roomData[roomKey] || {};
    const images = collectRoomImages(apiRoom, roomKey);
    const price = parseFloat(apiRoom.price);
    const capacity = parseInt(apiRoom.capacity, 10);
    const title = apiRoom.room_name || fallback.title || 'Room';

    return {
        title: title,
        price: Number.isFinite(price) ? price : (fallback.price || 0),
        priceUnit: /dormitory/i.test(`${apiRoom.room_type || ''} ${roomKey}`) ? 'per head' : 'per night',
        capacity: Number.isFinite(capacity) && capacity > 0
            ? `Good for ${capacity} person${capacity > 1 ? 's' : ''}`
            : (fallback.capacity || 'Capacity varies'),
        description: apiRoom.description || fallback.description
            || `${title} offers a comfortable and well-appointed space for your stay.`,
        imageUrl: images[0] || DEFAULT_ROOM_IMAGE,
        gridImages: images.length > 0 ? images : [DEFAULT_ROOM_IMAGE],
        roomId: apiRoom.id || null
    };
}

async function populateRoomDetails() {
    const params = new URLSearchParams(window.location.search);
    const roomKey = params.get('room');

    // Rooms added in the admin panel are not in the static roomData catalog,
    // so always try the API before deciding a room does not exist.
    const apiRoom = roomKey ? await fetchRoomByCode(roomKey) : null;
    if (apiRoom) {
        roomData[roomKey] = buildRoomEntry(apiRoom, roomKey);
    }

    const room = roomKey ? roomData[roomKey] : null;
    if (!room) {
        document.getElementById('room-title').textContent = 'Room Not Found';

        const gridContainer = document.getElementById('room-image-grid-container');
        if (gridContainer) gridContainer.style.display = 'none';
        return;
    }

    // Populate main details
    document.title = `${room.title} | BODARE Pension House Tagbilaran`;
    document.querySelector('.room-hero').style.backgroundImage = `url('${room.imageUrl}')`;
    document.getElementById('room-title').textContent = room.title;
    document.getElementById('room-capacity').textContent = room.capacity;
    document.querySelector('.room-description').textContent = room.description;
    document.getElementById('room-price-display').innerHTML = `<strong>₱${room.price.toLocaleString()}</strong> / ${room.priceUnit}`;
    const widget = document.querySelector('.booking-widget');
    if (widget) {
        widget.dataset.basePrice = room.price;
        calculateTotalCost();
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
            img.addEventListener('error', () => img.remove());
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

    const checkinInput = document.getElementById('checkin-widget');
    const checkoutInput = document.getElementById('checkout-widget');
    
    if (!checkinInput || !checkoutInput) {
        console.error('Date inputs not found');
        return;
    }

    const hotelTimes = getHotelCheckTimes();
    const today = bodareManilaTodayDate();
    const defaultCheckIn = withHotelTime(today, hotelTimes.checkIn.hour, hotelTimes.checkIn.minute);
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const defaultCheckOut = withHotelTime(tomorrow, hotelTimes.checkOut.hour, hotelTimes.checkOut.minute);

    const bookingParams = new URLSearchParams(window.location.search);
    const paramCheckin = bookingParams.get('checkin');
    const paramCheckout = bookingParams.get('checkout');
    const paramGuests = parseInt(bookingParams.get('guests'), 10);

    let initialCheckIn = defaultCheckIn;
    let initialCheckOut = defaultCheckOut;

    if (paramCheckin) {
        const parsedCheckIn = parseDateLocal(paramCheckin);
        if (parsedCheckIn && formatDateLocal(parsedCheckIn) >= formatDateLocal(today)) {
            initialCheckIn = withHotelTime(parsedCheckIn, hotelTimes.checkIn.hour, hotelTimes.checkIn.minute);
            if (/[ T]\d{2}:\d{2}/.test(paramCheckin)) {
                initialCheckIn = parsedCheckIn;
            }
        }
    }

    if (paramCheckout) {
        const parsedCheckOut = parseDateLocal(paramCheckout);
        if (parsedCheckOut && formatDateLocal(parsedCheckOut) > formatDateLocal(initialCheckIn)) {
            initialCheckOut = withHotelTime(parsedCheckOut, hotelTimes.checkOut.hour, hotelTimes.checkOut.minute);
            if (/[ T]\d{2}:\d{2}/.test(paramCheckout)) {
                initialCheckOut = parsedCheckOut;
            }
        }
    }

    if (formatDateLocal(initialCheckOut) <= formatDateLocal(initialCheckIn)) {
        const nextDay = new Date(initialCheckIn);
        nextDay.setDate(nextDay.getDate() + 1);
        initialCheckOut = withHotelTime(nextDay, hotelTimes.checkOut.hour, hotelTimes.checkOut.minute);
    }

    if (Number.isFinite(paramGuests) && paramGuests > 0) {
        const adultsInput = document.getElementById('adults-count');
        if (adultsInput) {
            adultsInput.value = String(paramGuests);
        }
    }

    const useFlatpickr = typeof flatpickr === 'function';
    let checkinPicker = null;
    let checkoutPicker = null;

    const syncCheckoutMin = (checkinDate) => {
        if (!checkinDate) return;
        const minCheckout = new Date(checkinDate);
        minCheckout.setDate(minCheckout.getDate() + 1);
        minCheckout.setHours(hotelTimes.checkOut.hour, hotelTimes.checkOut.minute, 0, 0);

        if (checkoutPicker) {
            checkoutPicker.set('minDate', minCheckout);
            const currentCheckout = checkoutPicker.selectedDates[0];
            if (!currentCheckout || currentCheckout <= checkinDate) {
                checkoutPicker.setDate(minCheckout, true);
            }
        } else {
            const currentCheckout = parseDateLocal(checkoutInput.value);
            if (!currentCheckout || currentCheckout <= checkinDate) {
                checkoutInput.value = formatDateTimeLocal(minCheckout);
            }
        }
    };

    if (useFlatpickr) {
        checkinPicker = flatpickr(checkinInput, {
            enableTime: true,
            time_24hr: true,
            dateFormat: 'Y-m-d H:i',
            altInput: true,
            altFormat: 'm / d / Y H:i',
            allowInput: false,
            minDate: 'today',
            defaultDate: initialCheckIn,
            defaultHour: hotelTimes.checkIn.hour,
            defaultMinute: hotelTimes.checkIn.minute,
            minuteIncrement: 15,
            onChange: function(selectedDates) {
                if (selectedDates[0]) {
                    syncCheckoutMin(selectedDates[0]);
                }
                validateDates();
                calculateTotalCost();
            }
        });

        checkoutPicker = flatpickr(checkoutInput, {
            enableTime: true,
            time_24hr: true,
            dateFormat: 'Y-m-d H:i',
            altInput: true,
            altFormat: 'm / d / Y H:i',
            allowInput: false,
            minDate: withHotelTime(
                new Date(initialCheckIn.getFullYear(), initialCheckIn.getMonth(), initialCheckIn.getDate() + 1),
                hotelTimes.checkOut.hour,
                hotelTimes.checkOut.minute
            ),
            defaultDate: initialCheckOut,
            defaultHour: hotelTimes.checkOut.hour,
            defaultMinute: hotelTimes.checkOut.minute,
            minuteIncrement: 15,
            onChange: function() {
                validateDates();
                calculateTotalCost();
            }
        });

        widget._checkinPicker = checkinPicker;
        widget._checkoutPicker = checkoutPicker;
    } else {
        checkinInput.type = 'datetime-local';
        checkoutInput.type = 'datetime-local';
        checkinInput.readOnly = false;
        checkoutInput.readOnly = false;
        checkinInput.value = formatDateTimeLocal(initialCheckIn).replace(' ', 'T');
        checkoutInput.value = formatDateTimeLocal(initialCheckOut).replace(' ', 'T');
        checkinInput.addEventListener('change', function() {
            const checkinDate = parseDateLocal(this.value);
            if (checkinDate) {
                syncCheckoutMin(checkinDate);
            }
            validateDates();
            calculateTotalCost();
        });
        checkoutInput.addEventListener('change', function() {
            validateDates();
            calculateTotalCost();
        });
    }

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
    
    // Get today's date in Asia/Manila (set to midnight)
    const today = bodareManilaTodayDate();
    
    const checkinDate = parseDateLocal(checkinInput.value);
    const checkoutDate = parseDateLocal(checkoutInput.value);
    const checkinDay = checkinDate
        ? new Date(checkinDate.getFullYear(), checkinDate.getMonth(), checkinDate.getDate())
        : null;
    const checkoutDay = checkoutDate
        ? new Date(checkoutDate.getFullYear(), checkoutDate.getMonth(), checkoutDate.getDate())
        : null;
    
    let error = '';
    
    if (checkinDay) {
        if (checkinDay < today) {
            error = 'Check-in date cannot be in the past. Please select today or a future date.';
            checkinInput.setCustomValidity(error);
        } else {
            checkinInput.setCustomValidity('');
        }
    }
    
    if (checkoutDay) {
        if (checkoutDay < today) {
            error = 'Check-out date cannot be in the past. Please select today or a future date.';
            checkoutInput.setCustomValidity(error);
        } else if (checkinDate && checkoutDate <= checkinDate) {
            error = 'Check-out must be after check-in. Please select a later date or time.';
            checkoutInput.setCustomValidity(error);
        } else if (checkinDay && checkoutDay.getTime() <= checkinDay.getTime()) {
            error = 'Check-out date must be at least one day after check-in.';
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
            nights = calendarNightCount(checkinInput.value, checkoutInput.value);
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
        let extraBedCost = parseFloat(extraBedCounter.dataset.cost) || 0;
        if (extraBedCost <= 0) {
            extraBedCost = getDefaultExtraBedPrice();
        }
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
                    const widget = document.querySelector('.booking-widget');
                    const hotelTimes = getHotelCheckTimes();
                    
                    if (checkinInput && checkoutInput) {
                        const checkinDate = withHotelTime(selectedDates[0], hotelTimes.checkIn.hour, hotelTimes.checkIn.minute);
                        const checkoutDate = withHotelTime(selectedDates[1], hotelTimes.checkOut.hour, hotelTimes.checkOut.minute);

                        if (widget && widget._checkinPicker && widget._checkoutPicker) {
                            widget._checkinPicker.setDate(checkinDate, true);
                            widget._checkoutPicker.set('minDate', withHotelTime(
                                new Date(checkinDate.getFullYear(), checkinDate.getMonth(), checkinDate.getDate() + 1),
                                hotelTimes.checkOut.hour,
                                hotelTimes.checkOut.minute
                            ));
                            widget._checkoutPicker.setDate(checkoutDate, true);
                        } else {
                            checkinInput.value = formatDateTimeLocal(checkinDate);
                            checkoutInput.value = formatDateTimeLocal(checkoutDate);
                        }
                        
                        // Validate dates
                        validateDates();
                        
                        // Calculate nights and total cost
                        const calculatedNights = calendarNightCount(
                            formatDateTimeLocal(checkinDate),
                            formatDateTimeLocal(checkoutDate)
                        );
                        
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
    
    const checkinDate = parseDateLocal(checkinInput.value);
    const checkoutDate = parseDateLocal(checkoutInput.value);
    const today = bodareManilaTodayDate();
    
    if (!checkinDate || !checkoutDate) {
        alert('Please select valid check-in and check-out dates.');
        return;
    }

    const checkinDay = new Date(checkinDate.getFullYear(), checkinDate.getMonth(), checkinDate.getDate());
    const checkoutDay = new Date(checkoutDate.getFullYear(), checkoutDate.getMonth(), checkoutDate.getDate());

    if (checkinDay < today) {
        alert('Check-in date cannot be in the past. Please select today or a future date.');
        checkinInput.focus();
        return;
    }
    
    if (checkoutDay < today) {
        alert('Check-out date cannot be in the past. Please select today or a future date.');
        checkoutInput.focus();
        return;
    }
    
    if (checkoutDate <= checkinDate) {
        alert('Check-out must be after check-in. Please select a later date or time.');
        checkoutInput.focus();
        return;
    }

    // Billing uses calendar nights, so checkout must be a later calendar day
    if (checkoutDay.getTime() <= checkinDay.getTime()) {
        alert('Check-out date must be at least one day after check-in.');
        checkoutInput.focus();
        return;
    }

    const params = new URLSearchParams(window.location.search);
    const roomKey = params.get('room');
    const room = roomData[roomKey];

    if (!room) {
        alert('This room is no longer available. Please pick another room.');
        return;
    }
    
    // Try to get room_id from API
    let roomId = room.roomId || null;
    if (!roomId && typeof API !== 'undefined') {
        try {
            const roomsResponse = await API.booking.getRooms();
            if (roomsResponse.success && roomsResponse.rooms) {
                const matchedRoom = roomsResponse.rooms.find(r => r.room_code === roomKey)
                    || roomsResponse.rooms.find(r => r.room_name === room.title)
                    || roomsResponse.rooms.find(r =>
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
    const extraBedInput = document.getElementById('extra-bed-count');
    const extraBedCounter = widget?.querySelector('.counter[data-cost]');
    const extraBeds = extraBedInput
        ? parseInt(extraBedInput.value, 10) || 0
        : (extraBedCounter ? parseInt(extraBedCounter.querySelector('input').value, 10) || 0 : 0);
    let extraBedCost = extraBedCounter ? parseFloat(extraBedCounter.dataset.cost) || 0 : 0;
    if (extraBedCost <= 0) {
        extraBedCost = getDefaultExtraBedPrice();
    }
    
    // Calculate nights (calendar nights; times are for guest preference / hotel policy)
    const nights = calendarNightCount(checkinInput.value, checkoutInput.value);
    
    let cartItem = {
        roomKey: roomKey,
        roomId: roomId,
        roomName: room.title,
        imageUrl: room.imageUrl,
        price: room.price,
        priceUnit: room.priceUnit,
        checkin: formatDateTimeLocal(checkinDate),
        checkout: formatDateTimeLocal(checkoutDate),
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

    cartItem = applyCartItemPricing(cartItem);
    
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
        const roomSubtotal = getCartItemRoomSubtotal(item);
        const extraBedTotal = getCartItemExtraBedTotal(item);
        totalAmount += item.totalAmount || (roomSubtotal + extraBedTotal);

        let extraBedRow = '';
        if (extraBedTotal > 0) {
            const extraBeds = parseInt(item.extraBeds, 10) || 0;
            const extraBedCost = parseFloat(item.extraBedCost) || 0;
            extraBedRow = `
                <div class="summary-item">
                    <span>Extra Bed (${extraBeds} × ${item.nights} night${item.nights > 1 ? 's' : ''} @ ${formatPeso(extraBedCost)})</span>
                    <strong>${formatPeso(extraBedTotal)}</strong>
                </div>
            `;
        }

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
                    <span>Room Rate</span>
                    <strong>${formatPeso(roomSubtotal)}</strong>
                </div>
                ${extraBedRow}
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
    const raw = String(dateString || '');
    const hasTime = /[ T]\d{2}:\d{2}/.test(raw);
    const date = hasTime
        ? (bodareParseServerDate(dateString) || parseDateLocal(dateString) || new Date(dateString))
        : (parseDateLocal(dateString) || bodareParseServerDate(dateString) || new Date(dateString));
    if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
        return dateString || '-';
    }
    const options = hasTime
        ? { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true, timeZone: window.BODARE_TIMEZONE }
        : { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleString('en-US', options);
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
            modalImg.alt = image.alt || 'Enlarged photo of BODARE Pension House';
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

// --- Toast notifications (checkout and shared UI feedback) ---
function showToast(message, type = 'info', options = {}) {
    if (!message) return;

    const duration = options.duration || (type === 'error' ? 6500 : 4500);
    let container = document.getElementById('bodare-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'bodare-toast-container';
        container.className = 'bodare-toast-container';
        container.setAttribute('aria-live', 'polite');
        container.setAttribute('aria-atomic', 'true');
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `bodare-toast bodare-toast--${type}`;
    toast.setAttribute('role', type === 'error' ? 'alert' : 'status');

    const icon = document.createElement('span');
    icon.className = 'bodare-toast-icon';
    icon.textContent = type === 'success' ? '✓' : type === 'error' ? '!' : 'i';

    const body = document.createElement('div');
    body.className = 'bodare-toast-body';

    const text = document.createElement('span');
    text.className = 'bodare-toast-message';
    text.textContent = message;
    body.appendChild(text);

    if (options.actionHref && options.actionLabel) {
        const action = document.createElement('a');
        action.className = 'bodare-toast-action';
        action.href = options.actionHref;
        action.textContent = options.actionLabel;
        body.appendChild(action);
    }

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'bodare-toast-close';
    closeBtn.setAttribute('aria-label', 'Dismiss notification');
    closeBtn.textContent = '×';

    toast.appendChild(icon);
    toast.appendChild(body);
    toast.appendChild(closeBtn);
    container.appendChild(toast);

    requestAnimationFrame(() => toast.classList.add('is-visible'));

    let hideTimer = setTimeout(dismissToast, duration);

    function dismissToast() {
        clearTimeout(hideTimer);
        toast.classList.remove('is-visible');
        setTimeout(() => {
            toast.remove();
            if (container.childElementCount === 0) {
                container.remove();
            }
        }, 260);
    }

    closeBtn.addEventListener('click', dismissToast);
}
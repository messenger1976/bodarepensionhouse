// API Configuration for Frontend
// Use relative API paths only so uploads/deployments work across domains and folders.
const API_BASE_CANDIDATES = (function() {
    const pathname = window.location.pathname;
    const currentDir = pathname.substring(0, pathname.lastIndexOf('/') + 1);

    const candidates = [
        `${currentDir}admin/index.php/api`,
        `${currentDir}admin/api`,
        'admin/index.php/api',
        'admin/api',
        './admin/index.php/api',
        './admin/api'
    ];

    // Deduplicate while preserving order.
    return [...new Set(candidates.map(url => url.replace(/\/+$/, '')))];
})();

let ACTIVE_API_BASE_URL = API_BASE_CANDIDATES[0];
const API_BASE_URL = ACTIVE_API_BASE_URL;

// API Helper Functions
const API = {
    baseURL: ACTIVE_API_BASE_URL,
    
    // Helper method for API calls
    async request(endpoint, options = {}) {
        const normalizedEndpoint = String(endpoint || '').replace(/^\/+/, '');
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include', // Include cookies for session
        };

        const config = {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...(options.headers || {})
            }
        };

        let lastError;
        for (const baseUrl of API_BASE_CANDIDATES) {
            const url = `${baseUrl}/${normalizedEndpoint}`;

            try {
                const response = await fetch(url, config);
            
                // Get response text first to check if it's JSON
                const responseText = await response.text();
                let data;
            
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    // If response is not JSON, log the full response for debugging
                    console.error('API returned non-JSON response. Status:', response.status, 'StatusText:', response.statusText, 'URL:', url);
                    console.error('Response preview:', responseText.substring(0, 500));
                    console.error('Full response length:', responseText.length);
                
                    // Check if it's an HTML error page
                    if (responseText.includes('<!DOCTYPE') || responseText.includes('<html') || responseText.includes('<body')) {
                        const htmlError = new Error('Server returned an error page. The API endpoint may not be accessible. Please check the server configuration.');
                        htmlError.url = url;
                        throw htmlError;
                    } else if (responseText.trim() === '') {
                        const emptyError = new Error('Server returned an empty response. Please check the API endpoint and try again.');
                        emptyError.url = url;
                        throw emptyError;
                    } else {
                        // Show a snippet of what we got
                        const snippet = responseText.substring(0, 100).replace(/\n/g, ' ');
                        const invalidError = new Error(`Invalid response from server: ${snippet}... Please try again.`);
                        invalidError.url = url;
                        throw invalidError;
                    }
                }
            
                if (!response.ok) {
                    const error = new Error(data.message || 'Request failed');
                    error.response = data;
                    error.status = response.status;
                    error.url = url;
                    throw error;
                }

                // Persist the working base URL for subsequent calls.
                ACTIVE_API_BASE_URL = baseUrl;
                API.baseURL = ACTIVE_API_BASE_URL;
                return data;
            } catch (error) {
                console.error('API Error:', error, 'URL:', url);
                lastError = error;

                const isNetworkError = error instanceof TypeError || /NetworkError|Failed to fetch/i.test(error.message || '');

                // Retry on alternative bases only for network-level failures.
                if (isNetworkError) {
                    continue;
                }

                // For non-network errors, stop retries and bubble up immediately.
                if (!error.response && !(error.message || '').includes('Server returned')) {
                    const wrappedError = new Error(error.message || 'Network error. Please check your connection.');
                    wrappedError.originalError = error;
                    throw wrappedError;
                }
                throw error;
            }
        }
            
        if (!lastError) {
            throw new Error('Unable to connect to API. Please try again later.');
        }

        if (!lastError.response && !(lastError.message || '').includes('Server returned')) {
            const wrappedError = new Error(lastError.message || 'Network error. Please check your connection.');
            wrappedError.originalError = lastError;
            throw wrappedError;
        }

        throw lastError;
    },
    
    // Auth endpoints
    auth: {
        async register(userData) {
            return API.request('auth/register', {
                method: 'POST',
                body: JSON.stringify(userData)
            });
        },
        
        async login(email, password) {
            return API.request('auth/login', {
                method: 'POST',
                body: JSON.stringify({ email, password })
            });
        },
        
        async logout() {
            return API.request('auth/logout', {
                method: 'POST'
            });
        },

        // Clear client-side account state when the server session is missing/expired.
        // Keep the booking cart/services so checkout can continue after re-login.
        clearLocalSession() {
            localStorage.removeItem('user');
        },

        // Logout server session (best effort) and clear the local account.
        async forceLogout(redirectTo = 'login.php') {
            try {
                await API.auth.logout();
            } catch (error) {
                // Session may already be gone; still clear local account state.
            } finally {
                API.auth.clearLocalSession();
                if (redirectTo) {
                    window.location.href = redirectTo;
                }
            }
        },
        
        async check() {
            return API.request('auth/check');
        },
        
        async forgotPassword(email) {
            return API.request('auth/forgot-password', {
                method: 'POST',
                body: JSON.stringify({ email })
            });
        },
        
        async verifyResetToken(token) {
            return API.request('auth/verify-reset-token', {
                method: 'POST',
                body: JSON.stringify({ token })
            });
        },
        
        async resetPassword(token, password) {
            return API.request('auth/reset-password', {
                method: 'POST',
                body: JSON.stringify({ token, password })
            });
        },

        async activateAccount(token) {
            return API.request('auth/activate-account', {
                method: 'POST',
                body: JSON.stringify({ token })
            });
        }
    },
    
    // Booking endpoints
    booking: {
        async checkAvailability(checkIn, checkOut, guests = null) {
            const params = new URLSearchParams({ check_in: checkIn, check_out: checkOut });
            if (guests) params.append('guests', guests);
            return API.request(`booking/availability?${params}`);
        },
        
        async getRooms() {
            return API.request('booking/get_rooms');
        },
        
        async getRoomByCode(roomCode) {
            return API.request(`booking/get_room_by_code/${roomCode}`);
        },
        
        async getRoom(roomId) {
            return API.request(`booking/room/${roomId}`);
        },
        
        async create(bookingData) {
            return API.request('booking/create', {
                method: 'POST',
                body: JSON.stringify(bookingData)
            });
        },
        
        async calculateTotal(roomId, checkIn, checkOut, guests = 1) {
            const params = new URLSearchParams({
                room_id: roomId,
                check_in: checkIn,
                check_out: checkOut,
                guests: guests
            });
            return API.request(`booking/calculate?${params}`);
        },
        
        async getMyBookings() {
            return API.request('booking/my-bookings');
        },

        async cancel(bookingId) {
            return API.request('booking/cancel', {
                method: 'POST',
                body: JSON.stringify({ booking_id: bookingId })
            });
        },
        
        async getByNumber(bookingNumber) {
            return API.request(`booking/number/${bookingNumber}`);
        }
    },
    
    // User profile endpoints
    user: {
        async getProfile() {
            return API.request('user/profile');
        },
        
        async updateProfile(profileData) {
            return API.request('user/update', {
                method: 'POST',
                body: JSON.stringify(profileData)
            });
        },

        async changePassword(payload) {
            return API.request('user/change_password', {
                method: 'POST',
                body: JSON.stringify(payload)
            });
        }
    },
    
    // Inquiry endpoints
    inquiry: {
        async csrf() {
            return API.request('inquiry/csrf', {
                method: 'GET'
            });
        },

        async submit(inquiryData) {
            const headers = {
                'Content-Type': 'application/json',
            };
            if (inquiryData && inquiryData.csrf_token) {
                headers['X-CSRF-Token'] = inquiryData.csrf_token;
            }
            return API.request('inquiry/submit', {
                method: 'POST',
                headers,
                body: JSON.stringify(inquiryData)
            });
        }
    },

    // Invoice / billing endpoints
    invoice: {
        async getMyInvoices() {
            return API.request('invoice/my-invoices');
        },

        async getInvoice(invoiceId) {
            return API.request(`invoice/view/${invoiceId}`);
        },

        async getByNumber(invoiceNumber) {
            return API.request(`invoice/number/${encodeURIComponent(invoiceNumber)}`);
        }
    },

    // PayMongo / online payment endpoints
    payment: {
        async createGcashCheckout(payload) {
            return API.request('payment/gcash-checkout', {
                method: 'POST',
                body: JSON.stringify(payload)
            });
        },

        async createQrph(payload) {
            return API.request('payment/qrph', {
                method: 'POST',
                body: JSON.stringify(payload || {})
            });
        },

        async createCardCheckout(payload) {
            return API.request('payment/card-checkout', {
                method: 'POST',
                body: JSON.stringify(payload || {})
            });
        },

        async verify(payload) {
            const params = new URLSearchParams();
            if (payload && payload.booking_number) params.set('booking', payload.booking_number);
            if (payload && payload.payment_intent_id) params.set('payment_intent_id', payload.payment_intent_id);
            if (payload && payload.session_id) params.set('session_id', payload.session_id);
            const qs = params.toString();
            return API.request(`payment/verify${qs ? `?${qs}` : ''}`);
        }
    },

    push: {
        async registerToken(payload) {
            return API.request('push/register', {
                method: 'POST',
                body: JSON.stringify(payload || {})
            });
        },

        async unregisterToken(payload) {
            return API.request('push/unregister', {
                method: 'POST',
                body: JSON.stringify(payload || {})
            });
        }
    }
};


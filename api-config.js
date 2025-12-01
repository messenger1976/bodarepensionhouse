// API Configuration for Frontend
// This automatically detects the correct path
const API_BASE_URL = (function() {
    // Get the current path
    const path = window.location.pathname;
    // Remove filename if present (e.g., index.php, checkout.php)
    const basePath = path.substring(0, path.lastIndexOf('/') + 1);
    // Return the API path relative to current location
    // Try with index.php first (if mod_rewrite not working)
    const apiPath = basePath + 'admin/index.php/api';
    return apiPath;
})();

// API Helper Functions
const API = {
    baseURL: API_BASE_URL,
    
    // Helper method for API calls
    async request(endpoint, options = {}) {
        const url = `${API_BASE_URL}/${endpoint}`;
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include', // Include cookies for session
        };
        
        const config = { ...defaultOptions, ...options };
        
        try {
            const response = await fetch(url, config);
            
            // Get response text first to check if it's JSON
            const responseText = await response.text();
            let data;
            
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                // If response is not JSON, log the full response for debugging
                console.error('API returned non-JSON response. Status:', response.status, 'StatusText:', response.statusText);
                console.error('Response preview:', responseText.substring(0, 500));
                console.error('Full response length:', responseText.length);
                
                // Check if it's an HTML error page
                if (responseText.includes('<!DOCTYPE') || responseText.includes('<html') || responseText.includes('<body')) {
                    throw new Error('Server returned an error page. The API endpoint may not be accessible. Please check the server configuration.');
                } else if (responseText.trim() === '') {
                    throw new Error('Server returned an empty response. Please check the API endpoint and try again.');
                } else {
                    // Show a snippet of what we got
                    const snippet = responseText.substring(0, 100).replace(/\n/g, ' ');
                    throw new Error(`Invalid response from server: ${snippet}... Please try again.`);
                }
            }
            
            if (!response.ok) {
                const error = new Error(data.message || 'Request failed');
                error.response = data;
                error.status = response.status;
                throw error;
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            // If it's not already our custom error, wrap it
            if (!error.response && !error.message.includes('Server returned')) {
                const wrappedError = new Error(error.message || 'Network error. Please check your connection.');
                wrappedError.originalError = error;
                throw wrappedError;
            }
            throw error;
        }
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
        }
    },
    
    // Inquiry endpoints
    inquiry: {
        async submit(inquiryData) {
            return API.request('inquiry/submit', {
                method: 'POST',
                body: JSON.stringify(inquiryData)
            });
        }
    }
};


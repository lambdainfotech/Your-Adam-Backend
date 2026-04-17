/**
 * JWT Authentication Helper
 * 
 * Handles JWT token management for the admin panel web interface.
 * Works with HTTP-Only cookies for web and provides automatic token refresh.
 */

(function(window) {
    'use strict';

    /**
     * JWT Auth Manager
     */
    const JWTAuth = {
        /**
         * Configuration
         */
        config: {
            cookieName: 'jwt_token',
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.content,
            refreshThreshold: 0.25, // Refresh when 25% of time remains (30 min before expiry for 2 hour token)
            checkInterval: 5 * 60 * 1000, // Check every 5 minutes
        },

        /**
         * State
         */
        state: {
            isRedirecting: false,
            isRefreshing: false,
            lastCheck: 0,
        },

        /**
         * Initialize JWT Auth
         */
        init() {
            this.setupAjaxHeaders();
            this.setupTokenRefresh();
            this.setupActivityMonitor();
        },

        /**
         * Setup AJAX headers for all requests
         */
        setupAjaxHeaders() {
            // For fetch API
            const originalFetch = window.fetch;
            window.fetch = (...args) => {
                const [url, options = {}] = args;
                
                // Add CSRF token for same-origin requests
                if (this.isSameOrigin(url)) {
                    options.headers = options.headers || {};
                    if (typeof options.headers === 'object') {
                        options.headers['X-CSRF-TOKEN'] = this.config.csrfToken;
                    }
                }

                return originalFetch(url, options).then(response => {
                    // Handle 401 Unauthorized
                    if (response.status === 401) {
                        // Only handle if not already redirecting
                        if (!this.state.isRedirecting) {
                            this.handleUnauthorized();
                        }
                    }
                    return response;
                });
            };

            // For XMLHttpRequest
            const originalOpen = XMLHttpRequest.prototype.open;
            
            XMLHttpRequest.prototype.open = function(method, url, ...rest) {
                this._url = url;
                this._method = method;
                return originalOpen.call(this, method, url, ...rest);
            };

            // Add CSRF token to XMLHttpRequest
            const originalSend = XMLHttpRequest.prototype.send;
            XMLHttpRequest.prototype.send = function(body) {
                if (this._url && JWTAuth.isSameOrigin(this._url)) {
                    this.setRequestHeader('X-CSRF-TOKEN', JWTAuth.config.csrfToken);
                }
                
                // Add load listener for 401 handling
                this.addEventListener('load', function() {
                    if (this.status === 401 && !JWTAuth.state.isRedirecting) {
                        JWTAuth.handleUnauthorized();
                    }
                });

                return originalSend.call(this, body);
            };
        },

        /**
         * Check if URL is same origin
         */
        isSameOrigin(url) {
            try {
                const parsed = new URL(url, window.location.origin);
                return parsed.origin === window.location.origin;
            } catch (e) {
                return true; // Relative URLs are same origin
            }
        },

        /**
         * Setup automatic token refresh
         */
        setupTokenRefresh() {
            // Check token status periodically
            setInterval(() => {
                this.checkAndRefreshToken();
            }, this.config.checkInterval);

            // Also check before page unload to prevent data loss
            window.addEventListener('beforeunload', (e) => {
                if (this.state.isRefreshing) {
                    e.preventDefault();
                    e.returnValue = 'Token refresh in progress...';
                }
            });
        },

        /**
         * Setup activity monitor to refresh token on user activity
         */
        setupActivityMonitor() {
            let activityTimeout;
            const resetTimer = () => {
                clearTimeout(activityTimeout);
                // If user is active, ensure token is fresh
                activityTimeout = setTimeout(() => {
                    this.checkAndRefreshToken();
                }, 60 * 60 * 1000); // Check 60 minutes after last activity
            };

            // Monitor user activity
            ['click', 'keypress', 'scroll', 'mousemove'].forEach(event => {
                document.addEventListener(event, resetTimer, { passive: true });
            });
        },

        /**
         * Check token and refresh if needed
         */
        async checkAndRefreshToken() {
            // Prevent concurrent checks
            if (this.state.isRefreshing || this.state.isRedirecting) {
                return;
            }

            // Rate limit checks
            const now = Date.now();
            if (now - this.state.lastCheck < 30000) { // Min 30 seconds between checks
                return;
            }
            this.state.lastCheck = now;

            try {
                const response = await fetch('/api/v1/auth/check', {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': this.config.csrfToken,
                    },
                });

                if (!response.ok) {
                    if (response.status === 401) {
                        // Token expired - try to refresh
                        await this.refreshToken();
                    }
                    return;
                }

                const data = await response.json();
                
                if (!data.data?.authenticated) {
                    // Not authenticated, try to refresh
                    await this.refreshToken();
                }
            } catch (error) {
                // Silent fail - network errors shouldn't trigger redirect immediately
                console.warn('Token check failed:', error);
            }
        },

        /**
         * Refresh the token
         */
        async refreshToken() {
            if (this.state.isRefreshing || this.state.isRedirecting) {
                return false;
            }

            this.state.isRefreshing = true;

            try {
                // For web interface, use the admin refresh endpoint
                const response = await fetch('/admin/refresh', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': this.config.csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                });

                if (response.ok) {
                    console.log('Token refreshed successfully');
                    return true;
                }

                // If refresh fails with 401, token is truly expired
                if (response.status === 401) {
                    this.handleUnauthorized();
                    return false;
                }

                return false;
            } catch (error) {
                console.error('Token refresh failed:', error);
                return false;
            } finally {
                this.state.isRefreshing = false;
            }
        },

        /**
         * Handle unauthorized access
         */
        handleUnauthorized() {
            // Prevent multiple redirects
            if (this.state.isRedirecting) {
                return;
            }

            if (window.location.pathname === '/admin/login') {
                return;
            }

            this.state.isRedirecting = true;

            // Show notification if available
            if (typeof Toast !== 'undefined') {
                Toast.show('Session expired. Redirecting to login...', 'warning', 3000);
            }

            // Redirect to login after short delay
            setTimeout(() => {
                window.location.href = '/admin/login?expired=1';
            }, 2000);
        },

        /**
         * Logout user
         */
        async logout() {
            try {
                const response = await fetch('/admin/logout', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.config.csrfToken,
                        'Content-Type': 'application/json',
                    },
                    credentials: 'same-origin',
                });

                if (response.ok || response.redirected) {
                    window.location.href = '/admin/login';
                }
            } catch (error) {
                console.error('Logout failed:', error);
                window.location.href = '/admin/login';
            }
        },

        /**
         * Make authenticated API request
         */
        async api(url, options = {}) {
            const defaultOptions = {
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            };

            const mergedOptions = {
                ...defaultOptions,
                ...options,
                headers: {
                    ...defaultOptions.headers,
                    ...options.headers,
                },
            };

            const response = await fetch(url, mergedOptions);

            if (response.status === 401 && !this.state.isRedirecting) {
                // Try to refresh token first
                const refreshed = await this.refreshToken();
                if (!refreshed) {
                    this.handleUnauthorized();
                    throw new Error('Unauthorized');
                }
                // Retry the request with new token
                return this.api(url, options);
            }

            return response;
        },
    };

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => JWTAuth.init());
    } else {
        JWTAuth.init();
    }

    // Expose globally
    window.JWTAuth = JWTAuth;

})(window);

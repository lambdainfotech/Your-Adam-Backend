/**
 * JWT Authentication Helper
 * 
 * Handles JWT token management for the admin panel web interface.
 * Works with HTTP-Only cookies for web and provides fallback handling.
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
            refreshThreshold: 0.2, // Refresh when 20% of time remains
        },

        /**
         * Initialize JWT Auth
         */
        init() {
            this.setupAjaxHeaders();
            this.setupTokenRefresh();
            this.setupAutoLogout();
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
                        this.handleUnauthorized();
                    }
                    return response;
                });
            };

            // For XMLHttpRequest
            const originalOpen = XMLHttpRequest.prototype.open;
            const originalSetRequestHeader = XMLHttpRequest.prototype.setRequestHeader;
            
            XMLHttpRequest.prototype.open = function(method, url, ...rest) {
                this._url = url;
                this._method = method;
                return originalOpen.call(this, method, url, ...rest);
            };

            XMLHttpRequest.prototype.setRequestHeader = function(header, value) {
                return originalSetRequestHeader.call(this, header, value);
            };

            // Add CSRF token to XMLHttpRequest
            const originalSend = XMLHttpRequest.prototype.send;
            XMLHttpRequest.prototype.send = function(body) {
                if (this._url && JWTAuth.isSameOrigin(this._url)) {
                    this.setRequestHeader('X-CSRF-TOKEN', JWTAuth.config.csrfToken);
                }
                
                // Add load listener for 401 handling
                this.addEventListener('load', function() {
                    if (this.status === 401) {
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
            // Refresh token periodically (every 5 minutes)
            setInterval(() => {
                this.refreshTokenIfNeeded();
            }, 5 * 60 * 1000);

            // Also check before important actions
            document.addEventListener('click', (e) => {
                const target = e.target.closest('a, button, form');
                if (target && this.isProtectedAction(target)) {
                    this.refreshTokenIfNeeded();
                }
            }, true);
        },

        /**
         * Check if element is a protected action
         */
        isProtectedAction(element) {
            // Check if it's a form submission, link to admin, or important button
            const href = element.getAttribute('href');
            const action = element.getAttribute('action');
            
            if (href && href.includes('/admin/')) return true;
            if (action && action.includes('/admin/')) return true;
            if (element.type === 'submit') return true;
            
            return false;
        },

        /**
         * Refresh token if needed
         */
        async refreshTokenIfNeeded() {
            try {
                const response = await fetch('/api/v1/auth/check', {
                    method: 'GET',
                    credentials: 'same-origin',
                });

                if (!response.ok && response.status === 401) {
                    // Token is invalid or expired
                    this.handleUnauthorized();
                }
            } catch (error) {
                console.warn('Token check failed:', error);
            }
        },

        /**
         * Setup auto logout on token expiry
         */
        setupAutoLogout() {
            // Check authentication status every minute
            setInterval(() => {
                this.checkAuthStatus();
            }, 60 * 1000);
        },

        /**
         * Check authentication status
         */
        async checkAuthStatus() {
            try {
                const response = await fetch('/api/v1/auth/check', {
                    method: 'GET',
                    credentials: 'same-origin',
                });

                const data = await response.json();

                if (!data.data?.authenticated) {
                    this.handleUnauthorized();
                }
            } catch (error) {
                // Silent fail - will be caught on next request
            }
        },

        /**
         * Handle unauthorized access
         */
        handleUnauthorized() {
            // Prevent multiple redirects
            if (window.location.pathname === '/admin/login') {
                return;
            }

            // Show notification if available
            if (typeof Toast !== 'undefined') {
                Toast.show('Session expired. Redirecting to login...', 'warning');
            }

            // Redirect to login after short delay
            setTimeout(() => {
                window.location.href = '/admin/login?expired=1';
            }, 1500);
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

            if (response.status === 401) {
                this.handleUnauthorized();
                throw new Error('Unauthorized');
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

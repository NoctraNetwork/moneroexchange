/**
 * CSRF Protection for AJAX Requests
 * Automatically includes CSRF token in all AJAX requests
 */

(function() {
    'use strict';

    // Get CSRF token from meta tag
    const token = document.querySelector('meta[name="csrf-token"]');
    if (!token) {
        console.error('CSRF token not found. Please ensure the meta tag is present in the head.');
        return;
    }

    const csrfToken = token.getAttribute('content');

    // Set up CSRF token for all AJAX requests
    if (typeof $ !== 'undefined' && $.ajaxSetup) {
        // jQuery
        $.ajaxSetup({
            beforeSend: function(xhr, settings) {
                if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                }
            }
        });
    }

    // Set up CSRF token for fetch requests
    const originalFetch = window.fetch;
    window.fetch = function(url, options = {}) {
        // Only add CSRF token for same-origin requests
        if (url.startsWith('/') || url.startsWith(window.location.origin)) {
            options.headers = options.headers || {};
            
            // Only add CSRF token for state-changing requests
            if (options.method && !['GET', 'HEAD', 'OPTIONS'].includes(options.method.toUpperCase())) {
                options.headers['X-CSRF-TOKEN'] = csrfToken;
            }
        }
        
        return originalFetch(url, options);
    };

    // Set up CSRF token for XMLHttpRequest
    const originalXHROpen = XMLHttpRequest.prototype.open;
    XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
        this._method = method;
        this._url = url;
        return originalXHROpen.apply(this, arguments);
    };

    const originalXHRSend = XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.send = function(data) {
        // Only add CSRF token for same-origin requests
        if (this._url && (this._url.startsWith('/') || this._url.startsWith(window.location.origin))) {
            // Only add CSRF token for state-changing requests
            if (this._method && !['GET', 'HEAD', 'OPTIONS'].includes(this._method.toUpperCase())) {
                this.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            }
        }
        
        return originalXHRSend.apply(this, arguments);
    };

    // Handle CSRF token mismatch errors
    document.addEventListener('DOMContentLoaded', function() {
        // Listen for 419 errors (CSRF token mismatch)
        if (typeof $ !== 'undefined' && $.ajaxSetup) {
            $(document).ajaxError(function(event, xhr, settings, thrownError) {
                if (xhr.status === 419) {
                    handleCsrfError();
                }
            });
        }

        // Handle fetch errors
        window.addEventListener('unhandledrejection', function(event) {
            if (event.reason && event.reason.status === 419) {
                handleCsrfError();
            }
        });
    });

    function handleCsrfError() {
        // Show user-friendly error message
        const errorMessage = 'Your session has expired. Please refresh the page and try again.';
        
        // Try to show error in a modal or alert
        if (typeof $ !== 'undefined' && $.modal) {
            $.modal({
                title: 'Session Expired',
                content: errorMessage,
                buttons: {
                    'Refresh Page': function() {
                        window.location.reload();
                    }
                }
            });
        } else if (typeof alert !== 'undefined') {
            alert(errorMessage);
            window.location.reload();
        } else {
            console.error('CSRF token mismatch. Please refresh the page.');
            window.location.reload();
        }
    }

    // Refresh CSRF token periodically
    function refreshCsrfToken() {
        fetch('/csrf-token', {
            method: 'GET',
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.token) {
                // Update the meta tag
                const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                if (tokenMeta) {
                    tokenMeta.setAttribute('content', data.token);
                }
                
                // Update the global variable
                window.csrfToken = data.token;
            }
        })
        .catch(error => {
            console.warn('Failed to refresh CSRF token:', error);
        });
    }

    // Refresh CSRF token every 30 minutes
    setInterval(refreshCsrfToken, 30 * 60 * 1000);

    // Expose CSRF token globally for manual use
    window.csrfToken = csrfToken;

})();

/**
 * Jobbnorge Block Pagination JavaScript
 * 
 * Handles AJAX pagination for job listings.
 */

(function() {
    'use strict';
    
    // Initialize pagination when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initializePagination();
    });
    
    /**
     * Initialize pagination functionality
     */
    function initializePagination() {
        const paginationContainers = document.querySelectorAll('.wp-block-dss-jobbnorge__pagination');
        
        paginationContainers.forEach(function(container) {
            const blockContainer = container.closest('.wp-block-dss-jobbnorge');
            
            if (!blockContainer) return;
            
            // Get block attributes from data attribute
            const attributesData = blockContainer.getAttribute('data-attributes');
            if (!attributesData) return;
            
            let attributes;
            try {
                attributes = JSON.parse(attributesData);
            } catch (e) {
                console.error('Failed to parse block attributes:', e);
                return;
            }
            
            // Add event listeners to pagination buttons
            const prevButton = container.querySelector('.wp-block-dss-jobbnorge__pagination-prev');
            const nextButton = container.querySelector('.wp-block-dss-jobbnorge__pagination-next');
            
            if (prevButton && !prevButton.disabled) {
                prevButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    const page = parseInt(this.getAttribute('data-page'));
                    loadPage(page, attributes, blockContainer);
                });
            }
            
            if (nextButton && !nextButton.disabled) {
                nextButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    const page = parseInt(this.getAttribute('data-page'));
                    loadPage(page, attributes, blockContainer);
                });
            }
        });
    }
    
    /**
     * Load a specific page via AJAX
     * 
     * @param {number} page - Page number to load
     * @param {Object} attributes - Block attributes
     * @param {Element} container - Block container element
     */
    function loadPage(page, attributes, container) {
        // Show loading state
        container.classList.add('wp-block-dss-jobbnorge__loading');
        
        // Disable pagination buttons during loading
        const buttons = container.querySelectorAll('.wp-block-dss-jobbnorge__pagination button');
        buttons.forEach(function(button) {
            button.disabled = true;
        });
        
        // Prepare AJAX data
        const formData = new FormData();
        formData.append('action', 'jobbnorge_get_jobs');
        formData.append('page', page);
        formData.append('attributes', JSON.stringify(attributes));
        formData.append('nonce', jobbnorgeAjax.nonce);
        
        // Make AJAX request
        fetch(jobbnorgeAjax.ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                // Update the container with new content
                container.innerHTML = data.data.html;
                
                // Reinitialize pagination for the new content
                initializePagination();
                
                // Scroll to top of block
                container.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start' 
                });
                
                // Update URL with page parameter (optional)
                updateURL(page);
                
            } else {
                console.error('AJAX request failed:', data.data);
                showError(container, 'Failed to load page. Please try again.');
            }
        })
        .catch(function(error) {
            console.error('AJAX request error:', error);
            showError(container, 'An error occurred while loading the page.');
        })
        .finally(function() {
            // Remove loading state
            container.classList.remove('wp-block-dss-jobbnorge__loading');
        });
    }
    
    /**
     * Update URL with page parameter
     * 
     * @param {number} page - Current page number
     */
    function updateURL(page) {
        if (history.pushState) {
            const url = new URL(window.location);
            if (page > 1) {
                url.searchParams.set('jobbnorge_page', page);
            } else {
                url.searchParams.delete('jobbnorge_page');
            }
            history.pushState({ page: page }, '', url);
        }
    }
    
    /**
     * Show error message
     * 
     * @param {Element} container - Block container
     * @param {string} message - Error message
     */
    function showError(container, message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'wp-block-dss-jobbnorge__error notice notice-error';
        errorDiv.innerHTML = '<p>' + message + '</p>';
        
        // Insert error message at the top of the container
        container.insertBefore(errorDiv, container.firstChild);
        
        // Remove error message after 5 seconds
        setTimeout(function() {
            if (errorDiv.parentNode) {
                errorDiv.parentNode.removeChild(errorDiv);
            }
        }, 5000);
    }
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(event) {
        if (event.state && event.state.page) {
            // Reload the page with the correct page number
            window.location.reload();
        }
    });
    
})();

/**
 * Gouri Transport - Main JavaScript
 */

(function() {
    'use strict';
    
    // Initialize MDB components
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all MDB elements
        const dropdownElements = document.querySelectorAll('[data-mdb-dropdown-init]');
        const collapseElements = document.querySelectorAll('[data-mdb-collapse-init]');
        const modalElements = document.querySelectorAll('[data-mdb-modal-init]');
        const rippleElements = document.querySelectorAll('[data-mdb-ripple-init]');
        
        // Auto-initialize form inputs
        const formInputs = document.querySelectorAll('.form-outline input, .form-outline textarea');
        formInputs.forEach(function(input) {
            if (input.value) {
                input.parentElement.classList.add('active');
            }
        });
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Form validation enhancement
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const requiredInputs = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredInputs.forEach(function(input) {
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
    
    // Phone number formatting
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            // Allow only numbers, spaces, +, -, (, )
            this.value = this.value.replace(/[^0-9\s\+\-\(\)]/g, '');
        });
    });
    
    // Date picker - set min date to today
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(function(input) {
        if (!input.min) {
            const today = new Date().toISOString().split('T')[0];
            input.min = today;
        }
    });
    
    // Search form enhancement
    const searchInputs = document.querySelectorAll('input[type="search"], input[name="search"]');
    searchInputs.forEach(function(input) {
        input.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                this.closest('form').submit();
            }
        });
    });
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (alert && alert.parentNode) {
                const bsAlert = new mdb.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    });
    
    // Print functionality
    const printButtons = document.querySelectorAll('[data-print]');
    printButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            window.print();
        });
    });
    
    // Lazy loading images
    if ('IntersectionObserver' in window) {
        const lazyImages = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(function(img) {
            imageObserver.observe(img);
        });
    }
    
    // Accordion hash handling for FAQ
    const accordionButtons = document.querySelectorAll('.accordion-button');
    accordionButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const target = this.getAttribute('data-mdb-target');
            if (target) {
                window.location.hash = target.replace('#', '');
            }
        });
    });
    
    // Open accordion from hash
    if (window.location.hash) {
        const targetAccordion = document.querySelector(window.location.hash);
        if (targetAccordion && targetAccordion.classList.contains('accordion-collapse')) {
            const btn = document.querySelector('[data-mdb-target="' + window.location.hash + '"]');
            if (btn) {
                btn.click();
            }
        }
    }
    
    // Copy to clipboard functionality
    const copyButtons = document.querySelectorAll('[data-copy]');
    copyButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const text = this.getAttribute('data-copy');
            navigator.clipboard.writeText(text).then(function() {
                // Show success tooltip or feedback
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                setTimeout(function() {
                    btn.innerHTML = originalText;
                }, 2000);
            });
        });
    });
    
    // Responsive table wrapper
    const tables = document.querySelectorAll('.table-responsive table');
    tables.forEach(function(table) {
        if (!table.parentElement.classList.contains('table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });
    
})();

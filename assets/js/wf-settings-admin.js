// wf-settings-admin.js - Enhanced UI with notifications and improved UX
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced form validation and UX functionality
    initializeFormValidationUX();
    
    // Handle notification close buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('wf-notification-close') || e.target.getAttribute('data-action') === 'close') {
            e.preventDefault();
            const notification = e.target.closest('.wf-notification');
            if (notification) {
                // Remove persistent class when manually closed
                notification.classList.remove('wf-notification-persistent');
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(-10px)';
                setTimeout(function() {
                    notification.style.display = 'none';
                }, 300);
            }
        }
    });

    // Modified auto-hide notifications - make error notifications persistent until user interacts
    const notifications = document.querySelectorAll('.wf-notification');
    notifications.forEach(function(notification) {
        if (notification.classList.contains('wf-notification-error')) {
            // Mark error notifications as persistent
            notification.classList.add('wf-notification-persistent');
        } else {
            // Auto-hide success notifications after 5 seconds
            setTimeout(function() {
                if (notification.style.display !== 'none' && !notification.classList.contains('wf-notification-persistent')) {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateY(-10px)';
                    setTimeout(function() {
                        notification.style.display = 'none';
                    }, 300);
                }
            }, 5000);
        }
    });

    // Smooth scroll to notifications if they exist
    const notificationContainer = document.querySelector('.wf-notifications');
    if (notificationContainer && notificationContainer.children.length > 0) {
        notificationContainer.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
    }

    // Enhanced form submission with loading state and value preservation
    const form = document.querySelector('.wf-settings-form');
    const submitButton = document.querySelector('.wf-settings-form input[type="submit"]');
    
    if (form && submitButton) {
        form.addEventListener('submit', function() {
            // Store form values before submission in case of validation errors
            storeFormValues();
            
            submitButton.value = 'Saving...';
            submitButton.disabled = true;
            submitButton.style.opacity = '0.7';
        });
    }
    
    // Clear stored form data if we see a success notification (form saved successfully)
    const successNotifications = document.querySelectorAll('.wf-notification-success');
    if (successNotifications.length > 0) {
        localStorage.removeItem('wf_settings_form_data');
    }

    // Password visibility toggle
    document.querySelectorAll('.wf-field input[type="password"]').forEach(function(input) {
        var wrapper = input.parentElement;
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.innerHTML = '👁️';
        btn.style.marginLeft = '8px';
        btn.style.background = 'none';
        btn.style.border = 'none';
        btn.style.cursor = 'pointer';
        btn.onclick = function(e) {
            e.preventDefault();
            input.type = input.type === 'password' ? 'text' : 'password';
        };
        wrapper.appendChild(btn);
    });
    // Tag/multiselect UI (basic, for demo)
    document.querySelectorAll('.wf-multiselect input').forEach(function(input) {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && input.value.trim()) {
                var tag = document.createElement('span');
                tag.className = 'wf-tag';
                tag.textContent = input.value.trim();
                input.parentElement.insertBefore(tag, input);
                input.value = '';
            }
        });
    });

    // Range/Slider value display functionality
    document.querySelectorAll('.wf-input-range').forEach(function(rangeInput) {
        var valueDisplay = rangeInput.parentElement.querySelector('.wf-range-value');
        
        if (valueDisplay) {
            // Initialize the display with current value
            valueDisplay.textContent = rangeInput.value || rangeInput.min || '0';
            
            // Update value display when slider changes
            rangeInput.addEventListener('input', function() {
                valueDisplay.textContent = this.value;
                updateRangeProgress(this);
            });
            
            // Also update on change event for better compatibility
            rangeInput.addEventListener('change', function() {
                valueDisplay.textContent = this.value;
                updateRangeProgress(this);
            });
            
            // Initialize progress fill
            updateRangeProgress(rangeInput);
        }
    });

    // Function to update range slider progress fill
    function updateRangeProgress(slider) {
        var min = slider.min || 0;
        var max = slider.max || 100;
        var value = slider.value;
        var percentage = ((value - min) / (max - min)) * 100;
        
        // Create gradient background for WebKit browsers
        slider.style.background = 'linear-gradient(to right, #2563eb 0%, #2563eb ' + percentage + '%, #e2e8f0 ' + percentage + '%, #e2e8f0 100%)';
    }

    // Dual Range Slider functionality
    document.querySelectorAll('.wf-dual-range-slider').forEach(function(slider) {
        var minThumb = slider.querySelector('.wf-dual-range-thumb-min');
        var maxThumb = slider.querySelector('.wf-dual-range-thumb-max');
        var fill = slider.querySelector('.wf-dual-range-fill');
        var hiddenInput = document.getElementById(slider.dataset.target);
        var minDisplay = slider.parentElement.querySelector('.wf-range-min');
        var maxDisplay = slider.parentElement.querySelector('.wf-range-max');
        
        var min = parseFloat(slider.dataset.min);
        var max = parseFloat(slider.dataset.max);
        var step = parseFloat(slider.dataset.step) || 1;
        
        var minValue = parseFloat(minThumb.dataset.value) || min;
        var maxValue = parseFloat(maxThumb.dataset.value) || max;
        
        // Initialize positions and displays
        updateSlider();
        
        function updateSlider() {
            var minPercent = ((minValue - min) / (max - min)) * 100;
            var maxPercent = ((maxValue - min) / (max - min)) * 100;
            
            minThumb.style.left = minPercent + '%';
            maxThumb.style.left = maxPercent + '%';
            
            fill.style.left = minPercent + '%';
            fill.style.width = (maxPercent - minPercent) + '%';
            
            minDisplay.textContent = minValue;
            maxDisplay.textContent = maxValue;
            
            hiddenInput.value = minValue + '-' + maxValue;
        }
        
        function handleMouseDown(thumb, isMin) {
            return function(e) {
                e.preventDefault();
                
                function handleMouseMove(e) {
                    var rect = slider.getBoundingClientRect();
                    var percent = Math.max(0, Math.min(100, ((e.clientX - rect.left) / rect.width) * 100));
                    var value = min + (percent / 100) * (max - min);
                    
                    // Round to step
                    value = Math.round(value / step) * step;
                    
                    if (isMin) {
                        minValue = Math.min(value, maxValue - step);
                        minValue = Math.max(minValue, min);
                    } else {
                        maxValue = Math.max(value, minValue + step);
                        maxValue = Math.min(maxValue, max);
                    }
                    
                    updateSlider();
                }
                
                function handleMouseUp() {
                    document.removeEventListener('mousemove', handleMouseMove);
                    document.removeEventListener('mouseup', handleMouseUp);
                }
                
                document.addEventListener('mousemove', handleMouseMove);
                document.addEventListener('mouseup', handleMouseUp);
            };
        }
        
        // Touch support
        function handleTouchStart(thumb, isMin) {
            return function(e) {
                e.preventDefault();
                
                function handleTouchMove(e) {
                    var touch = e.touches[0];
                    var rect = slider.getBoundingClientRect();
                    var percent = Math.max(0, Math.min(100, ((touch.clientX - rect.left) / rect.width) * 100));
                    var value = min + (percent / 100) * (max - min);
                    
                    // Round to step
                    value = Math.round(value / step) * step;
                    
                    if (isMin) {
                        minValue = Math.min(value, maxValue - step);
                        minValue = Math.max(minValue, min);
                    } else {
                        maxValue = Math.max(value, minValue + step);
                        maxValue = Math.min(maxValue, max);
                    }
                    
                    updateSlider();
                }
                
                function handleTouchEnd() {
                    document.removeEventListener('touchmove', handleTouchMove);
                    document.removeEventListener('touchend', handleTouchEnd);
                }
                
                document.addEventListener('touchmove', handleTouchMove);
                document.addEventListener('touchend', handleTouchEnd);
            };
        }
        
        // Attach event listeners
        minThumb.addEventListener('mousedown', handleMouseDown(minThumb, true));
        maxThumb.addEventListener('mousedown', handleMouseDown(maxThumb, false));
        minThumb.addEventListener('touchstart', handleTouchStart(minThumb, true));
        maxThumb.addEventListener('touchstart', handleTouchStart(maxThumb, false));
        
        // Click on track to move nearest thumb
        slider.addEventListener('click', function(e) {
            if (e.target === minThumb || e.target === maxThumb) return;
            
            var rect = slider.getBoundingClientRect();
            var percent = ((e.clientX - rect.left) / rect.width) * 100;
            var value = min + (percent / 100) * (max - min);
            value = Math.round(value / step) * step;
            
            var distToMin = Math.abs(value - minValue);
            var distToMax = Math.abs(value - maxValue);
            
            if (distToMin < distToMax) {
                minValue = Math.min(value, maxValue - step);
                minValue = Math.max(minValue, min);
            } else {
                maxValue = Math.max(value, minValue + step);
                maxValue = Math.min(maxValue, max);
            }
            
            updateSlider();
        });
    });
    
    // WordPress Media Selector functionality
    initializeMediaSelector();

    function initializeMediaSelector() {
        // Handle media selector button clicks
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('wf-select-media-button')) {
                e.preventDefault();
                openMediaSelector(e.target);
            }
            
            if (e.target.classList.contains('wf-remove-media-button')) {
                e.preventDefault();
                removeSelectedMedia(e.target);
            }
        });
    }

    function openMediaSelector(button) {
        const fieldId = button.getAttribute('data-field-id');
        const acceptTypes = button.getAttribute('accept');
        
        // Check if wp.media is available
        if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
            alert('WordPress media library is not available. Please refresh the page and try again.');
            return;
        }
        
        // Parse accept types if provided
        let libraryType = {};
        if (acceptTypes) {
            // Remove array brackets and quotes, split by comma
            const types = acceptTypes.replace(/[\[\]"']/g, '').split(',').map(s => s.trim());
            if (types.length > 0 && types[0] !== '') {
                libraryType.type = types;
            }
        }
        
        // Create media frame
        const mediaFrame = wp.media({
            title: 'Select File',
            button: {
                text: 'Use this file'
            },
            multiple: false,
            library: libraryType
        });

        // When file is selected
        mediaFrame.on('select', function() {
            try {
                const attachment = mediaFrame.state().get('selection').first().toJSON();
                updateFileField(fieldId, attachment);
            } catch (error) {
                console.error('Error selecting media:', error);
                alert('Error selecting file. Please try again.');
            }
        });

        // Open the media frame
        try {
            mediaFrame.open();
        } catch (error) {
            console.error('Error opening media frame:', error);
            alert('Error opening media library. Please refresh the page and try again.');
        }
    }

    function updateFileField(fieldId, attachment) {
        const hiddenInput = document.getElementById(fieldId);
        const previewContainer = document.querySelector('[data-field-id="' + fieldId + '"]');
        const buttonsContainer = previewContainer.parentElement.querySelector('.wf-file-upload-buttons');

        if (!hiddenInput || !previewContainer || !buttonsContainer) {
            console.error('Could not find required elements for field:', fieldId);
            return;
        }

        // Update hidden input with attachment ID
        hiddenInput.value = attachment.id;

        // Update preview
        let previewHTML = '';
        
        if (attachment.type === 'image') {
            const imageUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
            previewHTML = `
                <div class="wf-file-preview-item">
                    <img src="${escapeHtml(imageUrl)}" alt="${escapeHtml(attachment.title || attachment.filename)}" class="wf-file-preview-image" />
                    <div class="wf-file-info">
                        <span class="wf-file-name">${escapeHtml(attachment.title || attachment.filename)}</span>
                        <span class="wf-file-id">ID: ${attachment.id}</span>
                    </div>
                </div>
            `;
        } else {
            const fileExtension = attachment.filename ? attachment.filename.split('.').pop().toLowerCase() : 'file';
            previewHTML = `
                <div class="wf-file-preview-item">
                    <div class="wf-file-icon">📄</div>
                    <div class="wf-file-info">
                        <span class="wf-file-name">${escapeHtml(attachment.title || attachment.filename)}</span>
                        <span class="wf-file-id">ID: ${attachment.id}</span>
                        <span class="wf-file-type">${escapeHtml(fileExtension)}</span>
                    </div>
                </div>
            `;
        }

        previewContainer.innerHTML = previewHTML;
        previewContainer.classList.add('has-file');

        // Remove error state if file was successfully selected
        const field = hiddenInput.closest('.wf-field');
        if (field && field.classList.contains('wf-field-error')) {
            field.classList.remove('wf-field-error');
            
            // Hide persistent error notifications when user fixes the issue
            hidePersistentErrorNotifications();
        }

        // Add remove button if it doesn't exist
        if (!buttonsContainer.querySelector('.wf-remove-media-button')) {
            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'button wf-remove-media-button';
            removeButton.setAttribute('data-field-id', fieldId);
            removeButton.textContent = 'Remove';
            buttonsContainer.appendChild(removeButton);
        }
    }

    function removeSelectedMedia(button) {
        const fieldId = button.getAttribute('data-field-id');
        const hiddenInput = document.getElementById(fieldId);
        const previewContainer = document.querySelector('[data-field-id="' + fieldId + '"]');

        if (!hiddenInput || !previewContainer) {
            console.error('Could not find required elements for field:', fieldId);
            return;
        }

        // Clear hidden input
        hiddenInput.value = '';

        // Update preview to show placeholder
        previewContainer.innerHTML = '<div class="wf-file-placeholder">No file selected</div>';
        previewContainer.classList.remove('has-file');

        // Remove the remove button
        button.remove();
    }

    // Function to initialize enhanced form validation UX
    function initializeFormValidationUX() {
        // Supported field types for enhanced UX
        const supportedTypes = ['text', 'email', 'url', 'date', 'textarea'];
        
        // Restore form values from localStorage if they exist (after validation errors)
        restoreFormValues();
        
        // Add error highlighting based on current error notifications
        highlightErrorFields();
        
        // Add event listeners to remove error state when user starts typing
        supportedTypes.forEach(function(type) {
            const selector = type === 'textarea' ? 'textarea' : 'input[type="' + type + '"]';
            const inputs = document.querySelectorAll('.wf-field ' + selector);
            
            inputs.forEach(function(input) {
                // Remove error state when user starts editing
                input.addEventListener('input', function() {
                    const field = this.closest('.wf-field');
                    if (field && field.classList.contains('wf-field-error')) {
                        field.classList.remove('wf-field-error');
                        
                        // Hide persistent error notifications when user starts fixing issues
                        hidePersistentErrorNotifications();
                    }
                });
            });
        });
        
        // Add event listeners for file upload buttons to remove error state
        const uploadButtons = document.querySelectorAll('.wf-upload-media-button');
        uploadButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const field = this.closest('.wf-field');
                if (field && field.classList.contains('wf-field-error')) {
                    field.classList.remove('wf-field-error');
                    
                    // Hide persistent error notifications when user starts fixing issues
                    hidePersistentErrorNotifications();
                }
            });
        });
    }
    
    function storeFormValues() {
        const supportedTypes = ['text', 'email', 'url', 'date', 'textarea'];
        const formData = {};
        
        supportedTypes.forEach(function(type) {
            const selector = type === 'textarea' ? 'textarea' : 'input[type="' + type + '"]';
            const inputs = document.querySelectorAll('.wf-field ' + selector);
            
            inputs.forEach(function(input) {
                if (input.name) {
                    formData[input.name] = input.value;
                }
            });
        });
        
        if (Object.keys(formData).length > 0) {
            localStorage.setItem('wf_settings_form_data', JSON.stringify(formData));
        }
    }
    
    function restoreFormValues() {
        const storedData = localStorage.getItem('wf_settings_form_data');
        if (!storedData) return;
        
        try {
            const formData = JSON.parse(storedData);
            const supportedTypes = ['text', 'email', 'url', 'date', 'textarea'];
            
            supportedTypes.forEach(function(type) {
                const selector = type === 'textarea' ? 'textarea' : 'input[type="' + type + '"]';
                const inputs = document.querySelectorAll('.wf-field ' + selector);
                
                inputs.forEach(function(input) {
                    if (input.name && formData.hasOwnProperty(input.name)) {
                        // Only restore if the current value is empty (server-side preservation takes priority)
                        if (!input.value.trim()) {
                            input.value = formData[input.name];
                        }
                    }
                });
            });
            
            // Clear stored data after restoration
            localStorage.removeItem('wf_settings_form_data');
        } catch (e) {
            console.error('Error restoring form values:', e);
        }
    }
    
    function highlightErrorFields() {
        // Check if there are error notifications
        const errorNotifications = document.querySelectorAll('.wf-notification-error');
        if (errorNotifications.length === 0) return;
        
        // Parse error messages to identify which fields have errors
        errorNotifications.forEach(function(notification) {
            const errorText = notification.textContent || notification.innerText;
            
            // Look for field names in error messages and highlight corresponding fields
            const supportedTypes = ['text', 'email', 'url', 'date', 'textarea'];
            
            supportedTypes.forEach(function(type) {
                const selector = type === 'textarea' ? 'textarea' : 'input[type="' + type + '"]';
                const inputs = document.querySelectorAll('.wf-field ' + selector);
                
                inputs.forEach(function(input) {
                    const field = input.closest('.wf-field');
                    const label = field.querySelector('label');
                    
                    if (label) {
                        const fieldName = label.textContent.trim();
                        
                        // ONLY highlight if this specific field name appears in the error message
                        if (errorText.includes("'" + fieldName + "'")) {
                            field.classList.add('wf-field-error');
                        }
                    }
                });
            });
            
            // Handle file upload fields separately - look for fields containing file upload containers
            const fileUploadContainers = document.querySelectorAll('.wf-file-upload-container');
            fileUploadContainers.forEach(function(container) {
                const field = container.closest('.wf-field');
                const label = field.querySelector('label');
                
                if (label) {
                    const fieldName = label.textContent.trim();
                    
                    // ONLY highlight if this specific field name appears in the error message
                    if (errorText.includes("'" + fieldName + "'")) {
                        field.classList.add('wf-field-error');
                    }
                }
            });
        });
    }
    
    function hidePersistentErrorNotifications() {
        const persistentNotifications = document.querySelectorAll('.wf-notification-persistent');
        
        persistentNotifications.forEach(function(notification) {
            notification.classList.remove('wf-notification-persistent');
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(-10px)';
            setTimeout(function() {
                notification.style.display = 'none';
            }, 300);
        });
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }
});

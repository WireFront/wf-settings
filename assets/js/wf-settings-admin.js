// wf-settings-admin.js - Enhanced UI with notifications
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide notifications after 5 seconds
    const notifications = document.querySelectorAll('.wf-notification');
    notifications.forEach(function(notification) {
        setTimeout(function() {
            if (notification.style.display !== 'none') {
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(-10px)';
                setTimeout(function() {
                    notification.style.display = 'none';
                }, 300);
            }
        }, 5000);
    });

    // Smooth scroll to notifications if they exist
    const notificationContainer = document.querySelector('.wf-notifications');
    if (notificationContainer && notificationContainer.children.length > 0) {
        notificationContainer.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
    }

    // Enhanced form submission with loading state
    const form = document.querySelector('.wf-settings-form');
    const submitButton = document.querySelector('.wf-settings-form input[type="submit"]');
    
    if (form && submitButton) {
        form.addEventListener('submit', function() {
            submitButton.value = 'Saving...';
            submitButton.disabled = true;
            submitButton.style.opacity = '0.7';
        });
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
});

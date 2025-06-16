// wf-settings-admin.js - Minimal JS for enhanced UI (toggle, password, tags, etc.)
document.addEventListener('DOMContentLoaded', function() {
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

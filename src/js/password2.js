document.addEventListener('DOMContentLoaded', () => {
    // Element references (with safe optional chaining)
    const currentPwdInput = document.getElementById('current-password');
    const newPwdInput = document.getElementById('new-password');
    const confirmPwdInput = document.getElementById('confirm-password');
    const form = document.getElementById('password-form');
    const strengthFill = document.getElementById('strength-fill');
    const strengthText = document.getElementById('strength-text');
    const submitButton = document.querySelector('.btn-primary-alt');

    // Requirement indicators (could be either with id req-... or alternative class names)
    const requirementIds = ['req-length', 'req-uppercase', 'req-lowercase', 'req-number', 'req-special'];
    
    // Popup / alert management
    let popupAutoCloseTimeout = null;

    // ======================================================
    // Helpers
    // ======================================================
    function updateRequirements(password) {
        const reqs = [
            { id: 'req-length', passed: password.length >= 8 },
            { id: 'req-uppercase', passed: /[A-Z]/.test(password) },
            { id: 'req-lowercase', passed: /[a-z]/.test(password) },
            { id: 'req-number', passed: /[0-9]/.test(password) },
            { id: 'req-special', passed: /[!@#$%^&*]/.test(password) }
        ];
        reqs.forEach(r => {
            const el = document.getElementById(r.id);
            if (!el) return;
            if (r.passed) {
                el.classList.add('met');
                el.classList.remove('invalid');
            } else {
                el.classList.remove('met');
                el.classList.add('invalid');
            }
        });
    }

    function computeStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[!@#$%^&*]/.test(password)) strength++;
        return strength; // 0..5
    }

    function renderStrength(password) {
        if (!strengthFill || !strengthText) return;
        const strength = computeStrength(password);
        const percentage = (strength / 5) * 100;
        let label = '';
        let color = '';

        switch (strength) {
            case 0:
            case 1:
                label = 'Very Weak';
                color = '#ef4444'; // red
                break;
            case 2:
                label = 'Weak';
                color = '#f97316'; // orange
                break;
            case 3:
                label = 'Fair';
                color = '#eab308'; // yellow
                break;
            case 4:
                label = 'Good';
                color = '#22c55e'; // green
                break;
            case 5:
                label = 'Strong';
                color = '#16a34a'; // darker green
                break;
        }

        strengthFill.style.width = `${percentage}%`;
        strengthFill.style.backgroundColor = color;
        strengthText.textContent = label;
        strengthText.style.color = color;

        return strength >= 4; // threshold for acceptable
    }

    function passwordsMatch() {
        const newPassword = newPwdInput?.value || '';
        const confirmPassword = confirmPwdInput?.value || '';
        if (!confirmPwdInput) return false;
        if (!newPassword || !confirmPassword) {
            confirmPwdInput.classList.remove('error', 'success');
            return false;
        }
        if (newPassword === confirmPassword) {
            confirmPwdInput.classList.remove('error');
            confirmPwdInput.classList.add('success');
            return true;
        } else {
            confirmPwdInput.classList.remove('success');
            confirmPwdInput.classList.add('error');
            return false;
        }
    }

    function showPopup(message, type) {
        // remove existing
        const existing = document.querySelector('.popup-overlay');
        if (existing) {
            existing.remove();
            if (popupAutoCloseTimeout) clearTimeout(popupAutoCloseTimeout);
        }

        const overlay = document.createElement('div');
        overlay.className = 'popup-overlay';

        const popup = document.createElement('div');
        popup.className = `popup-box popup-${type}`;

        const icon = document.createElement('div');
        icon.className = 'popup-icon';
        icon.textContent = type === 'success' ? '✓' : '✕';

        const messageEl = document.createElement('div');
        messageEl.className = 'popup-message';
        messageEl.textContent = message;

        const closeBtn = document.createElement('button');
        closeBtn.className = 'popup-close';
        closeBtn.textContent = 'OK';
        closeBtn.addEventListener('click', closePopup);

        popup.appendChild(icon);
        popup.appendChild(messageEl);
        popup.appendChild(closeBtn);
        overlay.appendChild(popup);
        addPopupStyles();
        document.body.appendChild(overlay);

        popupAutoCloseTimeout = setTimeout(closePopup, 5000);
    }

    function showSuccess(message = 'Operation successful') {
        showPopup(message, 'success');
    }

    function showError(message = 'Something went wrong') {
        showPopup(message, 'error');
    }

    function closePopup() {
        const popup = document.querySelector('.popup-overlay');
        if (popup) {
            popup.classList.add('popup-fade-out');
            popup.addEventListener('animationend', () => popup.remove(), { once: true });
        }
        if (popupAutoCloseTimeout) clearTimeout(popupAutoCloseTimeout);
    }

    function addPopupStyles() {
        if (document.getElementById('popup-styles')) return;
        const style = document.createElement('style');
        style.id = 'popup-styles';
        style.textContent = `
.popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display:flex;
    align-items:center;
    justify-content:center;
    z-index:10000;
    animation: popup-fade-in 0.3s ease;
}
.popup-box {
    background:#fff;
    border-radius:12px;
    padding:30px;
    min-width:300px;
    max-width:90%;
    text-align:center;
    box-shadow:0 20px 40px rgba(0,0,0,0.15);
    animation: popup-scale-in 0.3s ease;
    position: relative;
}
.popup-icon {
    width:60px;
    height:60px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    margin:0 auto 20px;
    font-size:28px;
    font-weight:bold;
    color:#fff;
}
.popup-success .popup-icon {
    background:#22c55e;
}
.popup-error .popup-icon {
    background:#ef4444;
}
.popup-message {
    font-size:16px;
    color:#333;
    margin-bottom:25px;
    line-height:1.5;
}
.popup-close {
    background:#667eea;
    color:white;
    border:none;
    padding:12px 30px;
    border-radius:8px;
    font-size:14px;
    font-weight:500;
    cursor:pointer;
    transition:background 0.2s ease;
}
.popup-close:hover {
    background:#5a67d8;
}
.popup-error .popup-close {
    background:#ef4444;
}
.popup-error .popup-close:hover {
    background:#dc2626;
}
.popup-fade-out {
    animation: popup-fade-out 0.3s ease forwards;
}
@keyframes popup-fade-in {
    from { opacity:0; }
    to { opacity:1; }
}
@keyframes popup-scale-in {
    from { transform:scale(0.8); opacity:0; }
    to { transform:scale(1); opacity:1; }
}
@keyframes popup-fade-out {
    to { opacity:0; }
}
@media (max-width:480px) {
    .popup-box { margin:20px; min-width:auto; }
}
        `;
        document.head.appendChild(style);
    }

    function resetForm() {
        form?.reset();
        closePopup();
        if (strengthFill) strengthFill.style.width = '0%';
        if (strengthText) {
            strengthText.textContent = 'Password strength will appear here';
            strengthText.style.color = '';
        }
        requirementIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.classList.remove('met', 'invalid');
            }
        });
        if (confirmPwdInput) {
            confirmPwdInput.classList.remove('error', 'success');
        }
    }

    function togglePasswordVisibility(inputId, eyeOpenSelector, eyeClosedSelector) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const eyeOpen = input.parentElement?.querySelector(eyeOpenSelector);
        const eyeClosed = input.parentElement?.querySelector(eyeClosedSelector);
        if (input.type === 'password') {
            input.type = 'text';
            if (eyeOpen) eyeOpen.style.display = 'none';
            if (eyeClosed) eyeClosed.style.display = 'block';
        } else {
            input.type = 'password';
            if (eyeOpen) eyeOpen.style.display = 'block';
            if (eyeClosed) eyeClosed.style.display = 'none';
        }
    }

    // ======================================================
    // Event bindings
    // ======================================================
    newPwdInput?.addEventListener('input', () => {
        const pwd = newPwdInput.value;
        updateRequirements(pwd);
        renderStrength(pwd);
        passwordsMatch();
    });

    confirmPwdInput?.addEventListener('input', () => {
        passwordsMatch();
    });

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();

        const currentPassword = currentPwdInput?.value || '';
        const newPassword = newPwdInput?.value || '';
        const confirmPassword = confirmPwdInput?.value || '';

        // Basic presence validation
        if (!currentPassword || !newPassword || !confirmPassword) {
            showError('Please fill in all fields.');
            return;
        }

        // Strength check
        if (!renderStrength(newPassword)) {
            showError('Password is not strong enough.');
            return;
        }

        // Match check
        if (newPassword !== confirmPassword) {
            showError('New passwords do not match.');
            return;
        }

        // Prepare submission UI
        const originalText = submitButton?.textContent || '';
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="loading"><span class="spinner"></span>Updating...</span>';
        }

        try {
            // Simulate or perform real fetch to backend
            const formData = new FormData();
            formData.append('current_password', currentPassword);
            formData.append('new_password', newPassword);

            const response = await fetch('./inc/change_password.php', {
                method: 'POST',
                body: formData
            });

            let result = {};
            try {
                result = await response.json();
            } catch {
                showError('Invalid server response.');
                return;
            }

            if (result.success) {
                showSuccess(result.message || 'Password successfully updated!');
                resetForm();
            } else {
                const errorMessage = result.error || 'Failed to update password';
                showError(errorMessage);
            }
        } catch (err) {
            console.error('Password change error:', err);
            showError('Network error. Please check your connection and try again.');
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        }
    });

   

    // Expose toggle helpers globally if HTML uses inline onclick
    window.togglePasswordVisibility = togglePasswordVisibility;
});

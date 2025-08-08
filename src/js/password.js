// Password Management System - Optimized and Fixed
class PasswordManager {
    constructor() {
        this.init();
        this.popupAutoCloseTimeout = null;
    }

    init() {
        this.bindEvents();
        this.addPopupStyles();
    }

    bindEvents() {
        // Password input events
        const newPasswordInput = document.getElementById('new-password');
        const confirmPasswordInput = document.getElementById('confirm-password');
        const passwordForm = document.getElementById('password-form');

        if (newPasswordInput) {
            newPasswordInput.addEventListener('input', () => {
                const password = newPasswordInput.value;
                this.updateRequirements(password);
                this.checkPasswordStrength(password);
            });
        }

        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', () => {
                this.validatePasswords();
            });
        }

        if (passwordForm) {
            passwordForm.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }
    }

    // Fixed toggle password function
    togglePassword(inputId) {
        const input = document.getElementById(inputId);
        if (!input) {
            console.error(`Input element with ID '${inputId}' not found`);
            return;
        }

        const toggleButton = input.nextElementSibling;
        if (!toggleButton || !toggleButton.classList.contains('password-toggle-alt')) {
            console.error(`Toggle button not found for input '${inputId}'`);
            return;
        }

        const eyeOpen = toggleButton.querySelector('.eye-open');
        const eyeClosed = toggleButton.querySelector('.eye-closed');

        if (!eyeOpen || !eyeClosed) {
            console.error(`Eye icons not found for input '${inputId}'`);
            return;
        }

        if (input.type === 'password') {
            input.type = 'text';
            eyeOpen.style.display = 'none';
            eyeClosed.style.display = 'block';
        } else {
            input.type = 'password';
            eyeOpen.style.display = 'block';
            eyeClosed.style.display = 'none';
        }
    }

    // Password strength checker
    checkPasswordStrength(password) {
        const strengthFill = document.getElementById('strength-fill');
        const strengthText = document.getElementById('strength-text');

        if (!strengthFill || !strengthText) return;

        const requirements = this.getPasswordRequirements(password);
        const strength = Object.values(requirements).filter(Boolean).length;
        
        const { percentage, color, label } = this.getStrengthInfo(strength);

        strengthFill.style.width = percentage + '%';
        strengthFill.style.backgroundColor = color;
        strengthText.textContent = `Password strength: ${label}`;
        strengthText.style.color = color;

        return strength >= 4;
    }

    getPasswordRequirements(password) {
        return {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };
    }

    getStrengthInfo(strength) {
        const strengthMap = {
            0: { percentage: 0, color: '#e74c3c', label: 'Very Weak' },
            1: { percentage: 20, color: '#e74c3c', label: 'Very Weak' },
            2: { percentage: 40, color: '#f39c12', label: 'Weak' },
            3: { percentage: 60, color: '#f1c40f', label: 'Fair' },
            4: { percentage: 80, color: '#27ae60', label: 'Good' },
            5: { percentage: 100, color: '#16a34a', label: 'Strong' }
        };
        
        return strengthMap[strength] || strengthMap[0];
    }

    updateRequirements(password) {
        const requirements = this.getPasswordRequirements(password);
        
        Object.keys(requirements).forEach(req => {
            const element = document.getElementById(`req-${req}`);
            if (element) {
                if (requirements[req]) {
                    element.classList.add('met', 'valid');
                } else {
                    element.classList.remove('met', 'valid');
                }
            }
        });
    }

    validatePasswords() {
        const newPassword = document.getElementById('new-password')?.value || '';
        const confirmPassword = document.getElementById('confirm-password')?.value || '';
        const confirmInput = document.getElementById('confirm-password');

        if (!confirmInput) return false;

        if (newPassword && confirmPassword) {
            if (newPassword === confirmPassword) {
                confirmInput.classList.remove('error');
                confirmInput.classList.add('success');
                return true;
            } else {
                confirmInput.classList.remove('success');
                confirmInput.classList.add('error');
                return false;
            }
        }

        confirmInput.classList.remove('error', 'success');
        return false;
    }

    validatePassword(password) {
        const requirements = this.getPasswordRequirements(password);
        return Object.values(requirements).every(req => req);
    }

    async handleFormSubmit(e) {
        e.preventDefault();

        const currentPassword = document.getElementById('current-password')?.value || '';
        const newPassword = document.getElementById('new-password')?.value || '';
        const confirmPassword = document.getElementById('confirm-password')?.value || '';

        // Validation
        if (!this.validateForm(currentPassword, newPassword, confirmPassword)) {
            return;
        }

        await this.changePassword(currentPassword, newPassword);
    }

    validateForm(currentPassword, newPassword, confirmPassword) {
        if (!currentPassword || !newPassword || !confirmPassword) {
            this.showError('Please fill in all fields.');
            return false;
        }

        if (!this.validatePassword(newPassword)) {
            this.showError('Password does not meet minimum requirements.');
            return false;
        }

        if (newPassword !== confirmPassword) {
            this.showError('New password and confirmation do not match.');
            return false;
        }

        return true;
    }

    async changePassword(currentPassword, newPassword) {
        const submitButton = document.querySelector('.btn-primary-alt');
        if (!submitButton) {
            console.error('Submit button not found');
            return;
        }

        const originalText = submitButton.textContent;

        try {
            // Show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="loading"><span class="spinner"></span>Updating...</span>';

            this.hideAlerts();

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
                this.showError('Invalid server response.');
                return;
            }

            if (result.success) {
                this.showSuccess(result.message || 'Password successfully updated!');
                this.resetForm();
            } else {
                this.showError(result.error || 'Failed to update password');
            }

        } catch (error) {
            console.error('Password change error:', error);
            this.showError('Network error. Please check your connection and try again.');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    }

    showSuccess(message) {
        this.showPopup(message, 'success');
    }

    showError(message) {
        this.showPopup(message, 'error');
    }

    showPopup(message, type) {
        // Remove existing popup
        const existingPopup = document.querySelector('.popup-overlay');
        if (existingPopup) {
            existingPopup.classList.add('popup-fade-out');
            existingPopup.addEventListener('animationend', () => existingPopup.remove());
        }

        // Create new popup
        const overlay = document.createElement('div');
        overlay.className = 'popup-overlay';

        const popup = document.createElement('div');
        popup.className = `popup-box popup-${type}`;

        const icon = document.createElement('div');
        icon.className = 'popup-icon';
        icon.innerHTML = type === 'success' ? '✓' : '✕';

        const messageEl = document.createElement('div');
        messageEl.className = 'popup-message';
        messageEl.textContent = message;

        const closeBtn = document.createElement('button');
        closeBtn.className = 'popup-close';
        closeBtn.textContent = 'OK';
        closeBtn.onclick = () => this.closePopup();

        popup.appendChild(icon);
        popup.appendChild(messageEl);
        popup.appendChild(closeBtn);
        overlay.appendChild(popup);

        setTimeout(() => document.body.appendChild(overlay), 300);

        // Auto-close after 5 seconds
        if (this.popupAutoCloseTimeout) {
            clearTimeout(this.popupAutoCloseTimeout);
        }
        this.popupAutoCloseTimeout = setTimeout(() => this.closePopup(), 5000);
    }

    closePopup() {
        const popup = document.querySelector('.popup-overlay');
        if (popup) {
            popup.classList.add('popup-fade-out');
            popup.addEventListener('animationend', () => popup.remove());
        }
    }

    hideAlerts() {
        this.closePopup();
        
        // Hide inline alerts if they exist
        const successAlert = document.getElementById('success-alert');
        const errorAlert = document.getElementById('error-alert');
        
        if (successAlert) successAlert.classList.add('alert-hidden-alt');
        if (errorAlert) errorAlert.classList.add('alert-hidden-alt');
    }

    resetForm() {
        const form = document.getElementById('password-form');
        if (form) form.reset();
        
        this.hideAlerts();

        // Reset strength indicator
        const strengthFill = document.getElementById('strength-fill');
        const strengthText = document.getElementById('strength-text');
        
        if (strengthFill) strengthFill.style.width = '0%';
        if (strengthText) strengthText.textContent = 'Password strength will appear here';

        // Reset requirements
        document.querySelectorAll('.requirement-item-alt').forEach(item => {
            item.classList.remove('met', 'valid');
        });

        // Reset password visibility
        const passwordInputs = ['current-password', 'new-password', 'confirm-password'];
        passwordInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input && input.type === 'text') {
                input.type = 'password';
                const toggleButton = input.nextElementSibling;
                if (toggleButton) {
                    const eyeOpen = toggleButton.querySelector('.eye-open');
                    const eyeClosed = toggleButton.querySelector('.eye-closed');
                    if (eyeOpen) eyeOpen.style.display = 'block';
                    if (eyeClosed) eyeClosed.style.display = 'none';
                }
            }
        });
    }

    addPopupStyles() {
        if (document.querySelector('#popup-styles')) return;

        const style = document.createElement('style');
        style.id = 'popup-styles';
        style.textContent = `
            .popup-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
                animation: popup-fade-in 0.3s ease;
            }
            
            .popup-box {
                background: white;
                border-radius: 12px;
                padding: 30px;
                min-width: 300px;
                max-width: 90%;
                text-align: center;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
                animation: popup-scale-in 0.3s ease;
            }
            
            .popup-icon {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 20px;
                font-size: 28px;
                font-weight: bold;
                color: white;
            }
            
            .popup-success .popup-icon {
                background: #22c55e;
            }
            
            .popup-error .popup-icon {
                background: #ef4444;
            }
            
            .popup-message {
                font-size: 16px;
                color: #333;
                margin-bottom: 25px;
                line-height: 1.5;
            }
            
            .popup-close {
                background: #667eea;
                color: white;
                border: none;
                padding: 12px 30px;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                transition: background 0.2s ease;
            }
            
            .popup-close:hover {
                background: #5a67d8;
            }
            
            .popup-error .popup-close {
                background: #ef4444;
            }
            
            .popup-error .popup-close:hover {
                background: #dc2626;
            }
            
            .popup-fade-out {
                animation: popup-fade-out 0.3s ease forwards;
            }
            
            .loading {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .spinner {
                width: 16px;
                height: 16px;
                border: 2px solid #ffffff;
                border-top: 2px solid transparent;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }
            
            @keyframes popup-fade-in {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            @keyframes popup-scale-in {
                from { transform: scale(0.8); opacity: 0; }
                to { transform: scale(1); opacity: 1; }
            }
            
            @keyframes popup-fade-out {
                to { opacity: 0; }
            }
            
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
            
            @media (max-width: 480px) {
                .popup-box {
                    margin: 20px;
                    min-width: auto;
                }
            }
        `;

        document.head.appendChild(style);
    }
}


const passwordManager = new PasswordManager();

function togglePassword(inputId) {
    passwordManager.togglePassword(inputId);
}

function resetForm() {
    passwordManager.resetForm();
}
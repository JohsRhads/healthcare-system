// assets/js/main.js
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // Enhanced confirmation for status changes
    const statusButtons = document.querySelectorAll('.btn-status');
    statusButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Extract patient name from the closest row or card
            let patientName = '';
            const row = this.closest('tr[data-patient-id]');
            const card = this.closest('.patient-card');
            
            if (row) {
                // Get patient name from desktop view
                const nameElement = row.querySelector('td:nth-child(2) strong');
                if (nameElement) {
                    patientName = nameElement.textContent.trim();
                }
            } else if (card) {
                // Get patient name from mobile view
                const nameElement = card.querySelector('h4');
                if (nameElement) {
                    patientName = nameElement.textContent.trim();
                }
            }
            
            // Determine action type from button classes or text
            let action = '';
            if (this.classList.contains('btn-success')) action = 'Done';
            else if (this.classList.contains('btn-warning')) action = 'Pending';
            else if (this.classList.contains('btn-secondary')) action = 'Archived';
            else if (this.classList.contains('btn-info')) action = 'Rescheduled';
            
            // Show appropriate confirmation message
            const messages = {
                'Done': `Mark "${patientName || 'this patient'}" as completed?`,
                'Pending': `Set "${patientName || 'this patient'}" as pending?`,
                'Archived': `Archive "${patientName || 'this patient'}"?\n\nThis will move the patient to archived records and hide from active lists.\n\nYou can view archived patients by filtering for "Archived" status.`,
                'Rescheduled': `Mark "${patientName || 'this patient'}" for rescheduling?`
            };
            
            const message = messages[action] || `Confirm ${action} for "${patientName || 'this patient'}"?`;
            
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
            
            // Show processing feedback
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.disabled = true;
            
            // Re-enable after 2 seconds if navigation doesn't happen
            setTimeout(() => {
                this.innerHTML = originalHTML;
                this.disabled = false;
            }, 2000);
        });
    });

    // Form validation enhancement
    const forms = document.querySelectorAll('form[novalidate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                    
                    // Show error message
                    let errorMsg = field.nextElementSibling;
                    if (!errorMsg || !errorMsg.classList.contains('invalid-feedback')) {
                        errorMsg = document.createElement('div');
                        errorMsg.className = 'invalid-feedback';
                        errorMsg.textContent = 'This field is required';
                        field.parentNode.appendChild(errorMsg);
                    }
                } else {
                    field.classList.remove('is-invalid');
                    const errorMsg = field.parentNode.querySelector('.invalid-feedback');
                    if (errorMsg) errorMsg.remove();
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showToast('Please fill in all required fields correctly.', 'error');
            }
        });
    });

    // Date picker restrictions
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        const today = new Date().toISOString().split('T')[0];
        input.min = today;
        
        // Set max date to 1 year from today
        const maxDate = new Date();
        maxDate.setFullYear(maxDate.getFullYear() + 1);
        input.max = maxDate.toISOString().split('T')[0];
    });

    // Phone number formatting
    const phoneInputs = document.querySelectorAll('input[name="phone_number"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                // Format as +1 (XXX) XXX-XXXX
                if (value.length <= 10) {
                    value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
                }
            }
            e.target.value = value;
        });
    });

    // Table row selection with patient details view
    const tableRows = document.querySelectorAll('table tbody tr[data-patient-id]');
    tableRows.forEach(row => {
        row.addEventListener('click', function(e) {
            if (!e.target.closest('a') && !e.target.closest('button')) {
                // Get patient ID
                const patientId = this.getAttribute('data-patient-id');
                
                // Show patient details (you can implement a modal here)
                showPatientDetails(patientId);
            }
        });
    });

    // Real-time character counter for textareas
    const textareas = document.querySelectorAll('textarea[maxlength]');
    textareas.forEach(textarea => {
        const maxLength = parseInt(textarea.getAttribute('maxlength'));
        const counter = document.createElement('div');
        counter.className = 'text-muted text-end mt-1';
        counter.style.fontSize = '0.75rem';
        counter.textContent = `0/${maxLength} characters`;
        textarea.parentNode.appendChild(counter);
        
        textarea.addEventListener('input', function() {
            const remaining = maxLength - this.value.length;
            counter.textContent = `${this.value.length}/${maxLength} characters`;
            
            if (remaining < 20) {
                counter.style.color = remaining < 0 ? '#ef4444' : '#f59e0b';
            } else {
                counter.style.color = '#64748b';
            }
        });
    });

    // Print functionality enhancement
    const printButtons = document.querySelectorAll('.btn-print, [onclick*="window.print"]');
    printButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            showToast('Preparing print view...', 'info');
            setTimeout(() => {
                window.print();
            }, 500);
        });
    });
    
    // Archive filter reminder
    const statusFilter = document.querySelector('select[name="status"]');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            if (this.value === 'Archived') {
                showToast('Showing archived patients. These records are hidden from active lists.', 'info', 6000);
            }
        });
    }
});

// Toast notification function
function showToast(message, type = 'info', duration = 5000) {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast');
    existingToasts.forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-${getToastIcon(type)}"></i>
            <span>${message}</span>
        </div>
        <button class="toast-close">&times;</button>
    `;
    
    // Add styles
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${getToastColor(type)};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        z-index: 9999;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        animation: slideIn 0.3s ease;
        max-width: 400px;
        min-width: 300px;
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after duration
    setTimeout(() => {
        if (toast.parentNode) {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }
    }, duration);
    
    // Close button
    toast.querySelector('.toast-close').addEventListener('click', () => {
        toast.remove();
    });
    
    // Add animation keyframes if not present
    if (!document.querySelector('#toast-styles')) {
        const style = document.createElement('style');
        style.id = 'toast-styles';
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }
}

function getToastIcon(type) {
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-triangle',
        'warning': 'exclamation-circle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

function getToastColor(type) {
    const colors = {
        'success': '#10b981',
        'error': '#ef4444',
        'warning': '#f59e0b',
        'info': '#3b82f6'
    };
    return colors[type] || '#3b82f6';
}

// Function to show patient details (placeholder for modal implementation)
function showPatientDetails(patientId) {
    // This function can be expanded to show a modal with patient details
    // For now, it just navigates to the edit page
    if (confirm('View patient details in edit mode?')) {
        window.location.href = `edit_patient.php?id=${patientId}`;
    }
}

// Password strength checker (for future use)
function checkPasswordStrength(password) {
    const strength = {
        0: "Very Weak",
        1: "Weak",
        2: "Fair",
        3: "Good",
        4: "Strong"
    };
    
    let score = 0;
    
    // Length check
    if (password.length >= 8) score++;
    if (password.length >= 12) score++;
    
    // Complexity checks
    if (/[a-z]/.test(password)) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;
    
    return {
        score: Math.min(score, 4),
        text: strength[Math.min(score, 4)]
    };
}

// Auto-logout after inactivity
let inactivityTimer;
const INACTIVITY_TIMEOUT = 15 * 60 * 1000; // 15 minutes

function resetInactivityTimer() {
    clearTimeout(inactivityTimer);
    inactivityTimer = setTimeout(logoutDueToInactivity, INACTIVITY_TIMEOUT);
}

function logoutDueToInactivity() {
    if (confirm('You have been inactive for 15 minutes. For security, you will be logged out.')) {
        window.location.href = 'logout.php?reason=inactivity';
    } else {
        resetInactivityTimer();
    }
}

// Set up inactivity tracking on admin pages
if (window.location.pathname.includes('dashboard.php') || 
    window.location.pathname.includes('patients.php') ||
    window.location.pathname.includes('edit_patient.php')) {
    
    document.addEventListener('DOMContentLoaded', function() {
        resetInactivityTimer();
        
        // Reset timer on user activity
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
            document.addEventListener(event, resetInactivityTimer);
        });
    });
}

// Enhanced form submission protection
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        // Prevent double submission
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            }
        });
    });
});

// Archive specific functions
function confirmArchive(patientName) {
    return confirm(`Archive "${patientName}"?\n\nThis will move the patient to archived records and hide from active lists.\n\nYou can view archived patients by filtering for "Archived" status.`);
}

// Enhanced status change with better UX
function changePatientStatus(patientId, status, patientName = '') {
    const messages = {
        'Done': `Mark "${patientName || 'this patient'}" as completed?`,
        'Pending': `Set "${patientName || 'this patient'}" as pending?`,
        'Archived': `Archive "${patientName || 'this patient'}"?\n\nThis will move to archived records.\n\nView archived patients using the status filter.`,
        'Rescheduled': `Mark "${patientName || 'this patient'}" for rescheduling?`
    };
    
    if (confirm(messages[status] || `Confirm ${status} status?`)) {
        window.location.href = `patients.php?update_status=true&id=${patientId}&status=${status}`;
    }
}
// Initialize tooltips and popovers
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Helper function to show alerts
    function showAlert(type, message) {
        const alertContainer = document.getElementById('alert-container');
        if (alertContainer) {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.appendChild(alert);

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }
    }

    // Initialize delete confirmation modals
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
        button.addEventListener('click', function(e) {
            const modalId = this.getAttribute('data-bs-target');
            const modal = new bootstrap.Modal(document.querySelector(modalId));
            modal.show();
        });
    });

    // Teacher replacement form handling
    const replaceForm = document.getElementById('teacher-replace-form');
    if (replaceForm) {
        replaceForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(replaceForm);
            const submitButton = replaceForm.querySelector('button[type="submit"]');
            const loadingSpinner = document.getElementById('loading-spinner');
            
            // Disable submit button and show loading spinner
            submitButton.disabled = true;
            loadingSpinner.classList.remove('d-none');
            
            fetch(replaceForm.getAttribute('action'), {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                } else {
                    showAlert('danger', data.error);
                }
            })
            .catch(error => {
                showAlert('danger', 'An error occurred while processing your request');
                console.error('Error:', error);
            })
            .finally(() => {
                submitButton.disabled = false;
                loadingSpinner.classList.add('d-none');
            });
        });
    }

    // Teacher assignment form handling
    const assignForm = document.getElementById('teacher-assign-form');
    if (assignForm) {
        assignForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(assignForm);
            const submitButton = assignForm.querySelector('button[type="submit"]');
            const loadingSpinner = document.getElementById('loading-spinner');
            
            // Validate form
            const days = document.querySelectorAll('input[name="days[]"]:checked');
            if (days.length === 0) {
                showAlert('danger', 'Please select at least one day');
                return;
            }
            
            // Disable submit button and show loading spinner
            submitButton.disabled = true;
            loadingSpinner.classList.remove('d-none');
            
            fetch(assignForm.getAttribute('action'), {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                } else {
                    showAlert('danger', data.error);
                }
            })
            .catch(error => {
                showAlert('danger', 'An error occurred while processing your request');
                console.error('Error:', error);
            })
            .finally(() => {
                submitButton.disabled = false;
                loadingSpinner.classList.add('d-none');
            });
        });
    }

    // Teacher search and filter
    const teacherSearch = document.getElementById('teacher-search');
    const teacherCards = document.querySelectorAll('.teacher-card');
    
    if (teacherSearch) {
        teacherSearch.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            
            teacherCards.forEach(card => {
                const teacherName = card.querySelector('.teacher-name').textContent.toLowerCase();
                const teacherSkills = card.querySelector('.teacher-skills').textContent.toLowerCase();
                
                if (teacherName.includes(searchTerm) || teacherSkills.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});

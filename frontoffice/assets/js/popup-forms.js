// Popup Forms System - Professional UX/UI JavaScript

// Global Popup Functions
function openPopup(popupId) {
    document.getElementById(popupId).style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closePopup(popupId) {
    document.getElementById(popupId).style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close popup when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('popup-overlay')) {
        e.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
});

// Profile Functions
function openProfileEdit() {
    loadProfileData();
    openPopup('profileEditPopup');
}

function loadProfileData() {
    fetch('ajax/get_profile.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const form = document.getElementById('profileEditForm');
                form.nom.value = data.profile.nom || '';
                form.prenom.value = data.profile.prenom || '';
                form.telephone.value = data.profile.telephone || '';
                form.date_n.value = data.profile.date_n || '';
                form.adresse.value = data.profile.adresse || '';
                form.bio.value = data.profile.bio || '';
                loadCities();
                loadDomains();
            }
        })
        .catch(error => {
            console.error('Error loading profile:', error);
        });
}

function saveProfile() {
    const form = document.getElementById('profileEditForm');
    const formData = new FormData(form);
    
    showLoading('profileEditPopup');
    
    fetch('ajax/update_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading('profileEditPopup');
        if (data.success) {
            showMessage('profileMessage', 'Profil mis à jour avec succès!', 'success');
            setTimeout(() => {
                closePopup('profileEditPopup');
                location.reload();
            }, 2000);
        } else {
            showMessage('profileMessage', data.message || 'Erreur lors de la mise à jour', 'error');
        }
    })
    .catch(error => {
        hideLoading('profileEditPopup');
        showMessage('profileMessage', 'Erreur de connexion', 'error');
    });
}

// Job Application Functions
function openJobApplication(jobId, jobTitle) {
    document.getElementById('applicationJobId').value = jobId;
    document.querySelector('#jobApplicationPopup .popup-header h3').innerHTML = 
        `<i class="fas fa-paper-plane me-2"></i>Postuler à: ${jobTitle}`;
    openPopup('jobApplicationPopup');
}

function submitApplication() {
    const form = document.getElementById('jobApplicationForm');
    const formData = new FormData(form);
    
    showLoading('jobApplicationPopup');
    
    fetch('ajax/submit_application.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading('jobApplicationPopup');
        if (data.success) {
            showMessage('applicationMessage', 'Candidature envoyée avec succès!', 'success');
            setTimeout(() => {
                closePopup('jobApplicationPopup');
                form.reset();
            }, 2000);
        } else {
            showMessage('applicationMessage', data.message || 'Erreur lors de l\'envoi', 'error');
        }
    })
    .catch(error => {
        hideLoading('jobApplicationPopup');
        showMessage('applicationMessage', 'Erreur de connexion', 'error');
    });
}

// Job Alert Functions
function openJobAlert() {
    loadDomains();
    loadCities();
    loadContrats();
    openPopup('jobAlertPopup');
}

function createJobAlert() {
    const form = document.getElementById('jobAlertForm');
    const formData = new FormData(form);
    
    showLoading('jobAlertPopup');
    
    fetch('ajax/create_job_alert.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading('jobAlertPopup');
        if (data.success) {
            showMessage('alertMessage', 'Alerte créée avec succès!', 'success');
            setTimeout(() => {
                closePopup('jobAlertPopup');
                form.reset();
            }, 2000);
        } else {
            showMessage('alertMessage', data.message || 'Erreur lors de la création', 'error');
        }
    })
    .catch(error => {
        hideLoading('jobAlertPopup');
        showMessage('alertMessage', 'Erreur de connexion', 'error');
    });
}

// Contact Form Functions
function openContactForm() {
    openPopup('contactFormPopup');
}

function submitContact() {
    const form = document.getElementById('contactForm');
    const formData = new FormData(form);
    
    showLoading('contactFormPopup');
    
    fetch('ajax/submit_contact.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading('contactFormPopup');
        if (data.success) {
            showMessage('contactMessage', 'Message envoyé avec succès!', 'success');
            setTimeout(() => {
                closePopup('contactFormPopup');
                form.reset();
            }, 2000);
        } else {
            showMessage('contactMessage', data.message || 'Erreur lors de l\'envoi', 'error');
        }
    })
    .catch(error => {
        hideLoading('contactFormPopup');
        showMessage('contactMessage', 'Erreur de connexion', 'error');
    });
}

// Utility Functions
function showMessage(elementId, message, type) {
    const element = document.getElementById(elementId);
    element.textContent = message;
    element.className = `popup-message ${type}`;
    element.style.display = 'block';
}

function showLoading(popupId) {
    const popup = document.getElementById(popupId);
    const loading = popup.querySelector('.popup-loading');
    if (loading) {
        loading.style.display = 'block';
    }
}

function hideLoading(popupId) {
    const popup = document.getElementById(popupId);
    const loading = popup.querySelector('.popup-loading');
    if (loading) {
        loading.style.display = 'none';
    }
}

// Load Data Functions
function loadCities() {
    fetch('ajax/get_cities.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const selects = document.querySelectorAll('select[name="ville_id"]');
                selects.forEach(select => {
                    select.innerHTML = '<option value="">Sélectionner une ville</option>';
                    data.cities.forEach(city => {
                        select.innerHTML += `<option value="${city.id}">${city.nom}</option>`;
                    });
                });
            }
        });
}

function loadDomains() {
    fetch('ajax/get_domains.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const selects = document.querySelectorAll('select[name="domaine_id"]');
                selects.forEach(select => {
                    select.innerHTML = '<option value="">Sélectionner un domaine</option>';
                    data.domains.forEach(domain => {
                        select.innerHTML += `<option value="${domain.id}">${domain.nom}</option>`;
                    });
                });
            }
        });
}

function loadContrats() {
    fetch('ajax/get_contrats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const selects = document.querySelectorAll('select[name="contrat_id"]');
                selects.forEach(select => {
                    select.innerHTML = '<option value="">Sélectionner un contrat</option>';
                    data.contrats.forEach(contrat => {
                        select.innerHTML += `<option value="${contrat.id}">${contrat.nom}</option>`;
                    });
                });
            }
        });
}

// File Upload Handlers
document.addEventListener('DOMContentLoaded', function() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const container = this.closest('.file-upload-container');
            
            if (file) {
                container.innerHTML = `
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <p class="mb-2">${file.name}</p>
                    <small class="text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                `;
            }
        });
    });
    
    const uploadContainers = document.querySelectorAll('.file-upload-container');
    uploadContainers.forEach(container => {
        container.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        
        container.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });
        
        container.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            const file = e.dataTransfer.files[0];
            const input = this.querySelector('input[type="file"]');
            if (input && file) {
                input.files = e.dataTransfer.files;
                input.dispatchEvent(new Event('change'));
            }
        });
    });
});

// Professional Section Functions
function createProfessionalSection(title, icon, content, actions = []) {
    const section = document.createElement('div');
    section.className = 'professional-section';
    
    let actionsHtml = '';
    if (actions.length > 0) {
        actionsHtml = `
            <div class="action-buttons">
                ${actions.map(action => `
                    <button class="btn-action btn-action-${action.type}" onclick="${action.onclick}">
                        <i class="${action.icon}"></i>${action.text}
                    </button>
                `).join('')}
            </div>
        `;
    }
    
    section.innerHTML = `
        <h3><i class="${icon}"></i>${title}</h3>
        <div class="section-content">${content}</div>
        ${actionsHtml}
    `;
    
    return section;
}

function createStatsCard(number, label, icon, color = 'primary') {
    return `
        <div class="stats-card">
            <div class="stats-icon color-scheme-${color}">
                <i class="${icon}"></i>
            </div>
            <div class="stats-number">${number}</div>
            <div class="stats-label">${label}</div>
        </div>
    `;
}

function createEnhancedCard(title, content, actions = []) {
    let actionsHtml = '';
    if (actions.length > 0) {
        actionsHtml = `
            <div class="enhanced-card-actions">
                ${actions.map(action => `
                    <button class="btn-action btn-action-${action.type}" onclick="${action.onclick}">
                        <i class="${action.icon}"></i>${action.text}
                    </button>
                `).join('')}
            </div>
        `;
    }
    
    return `
        <div class="enhanced-card">
            <div class="enhanced-card-header">
                <h4 class="enhanced-card-title">${title}</h4>
                ${actionsHtml}
            </div>
            <div class="card-content">${content}</div>
        </div>
    `;
}

// Notification Functions
function showNotification(message, type = 'info', duration = 3000) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
            <span>${message}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, duration);
}

// Form Validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Enhanced Search Functions
function performEnhancedSearch(searchData) {
    const searchParams = new URLSearchParams(searchData);
    window.location.href = `enhanced_search.php?${searchParams.toString()}`;
}

// Save Job Functions
function saveJob(jobId) {
    fetch('ajax/save_job.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ job_id: jobId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Emploi sauvegardé avec succès!', 'success');
        } else {
            showNotification(data.message || 'Erreur lors de la sauvegarde', 'error');
        }
    })
    .catch(error => {
        showNotification('Erreur de connexion', 'error');
    });
}

// Initialize all popup functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Add smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

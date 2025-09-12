<?php
// Popup Forms System - Professional UX/UI Design
// This file contains all popup forms for the front-office pages
?>

<!-- Include CSS and JS -->
<link rel="stylesheet" href="frontoffice/assets/css/popup-forms.css">
<script src="frontoffice/assets/js/popup-forms.js" defer></script>

<!-- Profile Edit Popup -->
<div id="profileEditPopup" class="popup-overlay">
    <div class="popup-container" style="width: 600px;">
        <div class="popup-header">
            <h3><i class="fas fa-user-edit me-2"></i>Modifier le Profil</h3>
            <button type="button" class="popup-close" onclick="closePopup('profileEditPopup')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="popup-body">
            <div class="popup-message" id="profileMessage"></div>
            <div class="popup-loading">
                <div class="spinner"></div>
                <p>Chargement...</p>
            </div>
            <form id="profileEditForm" class="popup-form">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Nom *</label>
                            <input type="text" class="form-control" name="nom" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Prénom *</label>
                            <input type="text" class="form-control" name="prenom" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" name="telephone">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Date de Naissance</label>
                            <input type="date" class="form-control" name="date_n">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Adresse</label>
                    <textarea class="form-control" name="adresse" rows="3"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Ville</label>
                            <select class="form-select" name="ville_id">
                                <option value="">Sélectionner une ville</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Domaine</label>
                            <select class="form-select" name="domaine_id">
                                <option value="">Sélectionner un domaine</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Bio</label>
                    <textarea class="form-control" name="bio" rows="4" placeholder="Parlez-nous de vous..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Photo de Profil</label>
                    <div class="file-upload-container" onclick="document.getElementById('profilePhoto').click()">
                        <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                        <p class="mb-2">Cliquez pour sélectionner une photo</p>
                        <small class="text-muted">JPG, PNG (Max 2MB)</small>
                        <input type="file" id="profilePhoto" name="photo" accept="image/*" style="display: none;">
                    </div>
                </div>
            </form>
        </div>
        <div class="popup-actions">
            <button type="button" class="btn-popup btn-popup-secondary" onclick="closePopup('profileEditPopup')">
                <i class="fas fa-times me-2"></i>Annuler
            </button>
            <button type="button" class="btn-popup btn-popup-primary" onclick="saveProfile()">
                <i class="fas fa-save me-2"></i>Enregistrer
            </button>
        </div>
    </div>
</div>

<!-- Job Application Popup -->
<div id="jobApplicationPopup" class="popup-overlay">
    <div class="popup-container" style="width: 700px;">
        <div class="popup-header">
            <h3><i class="fas fa-paper-plane me-2"></i>Postuler à l'Offre</h3>
            <button type="button" class="popup-close" onclick="closePopup('jobApplicationPopup')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="popup-body">
            <div class="popup-message" id="applicationMessage"></div>
            <div class="popup-loading">
                <div class="spinner"></div>
                <p>Envoi en cours...</p>
            </div>
            <form id="jobApplicationForm" class="popup-form">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                <input type="hidden" name="job_id" id="applicationJobId">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Nom Complet *</label>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Téléphone *</label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Ville</label>
                            <select class="form-select" name="city">
                                <option value="">Sélectionner une ville</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Lettre de Motivation *</label>
                    <textarea class="form-control" name="cover_letter" rows="6" 
                              placeholder="Expliquez pourquoi vous êtes le candidat idéal pour ce poste..." required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">CV *</label>
                    <div class="file-upload-container" onclick="document.getElementById('cvFile').click()">
                        <i class="fas fa-file-pdf fa-3x text-primary mb-3"></i>
                        <p class="mb-2">Cliquez pour sélectionner votre CV</p>
                        <small class="text-muted">PDF, DOC, DOCX (Max 5MB)</small>
                        <input type="file" id="cvFile" name="cv" accept=".pdf,.doc,.docx" style="display: none;" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Portfolio (Optionnel)</label>
                    <div class="file-upload-container" onclick="document.getElementById('portfolioFile').click()">
                        <i class="fas fa-folder-open fa-3x text-warning mb-3"></i>
                        <p class="mb-2">Cliquez pour ajouter votre portfolio</p>
                        <small class="text-muted">PDF, ZIP (Max 10MB)</small>
                        <input type="file" id="portfolioFile" name="portfolio" accept=".pdf,.zip" style="display: none;">
                    </div>
                </div>
            </form>
        </div>
        <div class="popup-actions">
            <button type="button" class="btn-popup btn-popup-secondary" onclick="closePopup('jobApplicationPopup')">
                <i class="fas fa-times me-2"></i>Annuler
            </button>
            <button type="button" class="btn-popup btn-popup-primary" onclick="submitApplication()">
                <i class="fas fa-paper-plane me-2"></i>Envoyer la Candidature
            </button>
        </div>
    </div>
</div>

<!-- Job Alert Popup -->
<div id="jobAlertPopup" class="popup-overlay">
    <div class="popup-container" style="width: 500px;">
        <div class="popup-header">
            <h3><i class="fas fa-bell me-2"></i>Créer une Alerte Emploi</h3>
            <button type="button" class="popup-close" onclick="closePopup('jobAlertPopup')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="popup-body">
            <div class="popup-message" id="alertMessage"></div>
            <div class="popup-loading">
                <div class="spinner"></div>
                <p>Création en cours...</p>
            </div>
            <form id="jobAlertForm" class="popup-form">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                <div class="form-group">
                    <label class="form-label">Nom de l'Alerte *</label>
                    <input type="text" class="form-control" name="alert_name" placeholder="Ex: Développeur Web Casablanca" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Mots-clés</label>
                    <input type="text" class="form-control" name="keywords" placeholder="Ex: PHP, JavaScript, React">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Domaine</label>
                            <select class="form-select" name="domaine_id">
                                <option value="">Tous les domaines</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Ville</label>
                            <select class="form-select" name="ville_id">
                                <option value="">Toutes les villes</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Type de Contrat</label>
                            <select class="form-select" name="contrat_id">
                                <option value="">Tous les contrats</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Fréquence</label>
                            <select class="form-select" name="frequency" required>
                                <option value="daily">Quotidienne</option>
                                <option value="weekly">Hebdomadaire</option>
                                <option value="monthly">Mensuelle</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="popup-actions">
            <button type="button" class="btn-popup btn-popup-secondary" onclick="closePopup('jobAlertPopup')">
                <i class="fas fa-times me-2"></i>Annuler
            </button>
            <button type="button" class="btn-popup btn-popup-primary" onclick="createJobAlert()">
                <i class="fas fa-bell me-2"></i>Créer l'Alerte
            </button>
        </div>
    </div>
</div>

<!-- Contact Form Popup -->
<div id="contactFormPopup" class="popup-overlay">
    <div class="popup-container" style="width: 600px;">
        <div class="popup-header">
            <h3><i class="fas fa-envelope me-2"></i>Nous Contacter</h3>
            <button type="button" class="popup-close" onclick="closePopup('contactFormPopup')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="popup-body">
            <div class="popup-message" id="contactMessage"></div>
            <div class="popup-loading">
                <div class="spinner"></div>
                <p>Envoi en cours...</p>
            </div>
            <form id="contactForm" class="popup-form">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Nom *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Sujet *</label>
                    <select class="form-select" name="subject" required>
                        <option value="">Sélectionner un sujet</option>
                        <option value="general">Question Générale</option>
                        <option value="support">Support Technique</option>
                        <option value="partnership">Partenariat</option>
                        <option value="feedback">Retour d'Expérience</option>
                        <option value="other">Autre</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Message *</label>
                    <textarea class="form-control" name="message" rows="6" 
                              placeholder="Décrivez votre demande..." required></textarea>
                </div>
            </form>
        </div>
        <div class="popup-actions">
            <button type="button" class="btn-popup btn-popup-secondary" onclick="closePopup('contactFormPopup')">
                <i class="fas fa-times me-2"></i>Annuler
            </button>
            <button type="button" class="btn-popup btn-popup-primary" onclick="submitContact()">
                <i class="fas fa-paper-plane me-2"></i>Envoyer
            </button>
        </div>
    </div>
</div>

<?php
/**
 * Enhanced Job Search Page
 * Integrates aggregated jobs with multilingual support
 */

// Include necessary files
include 'include/sess.php';
include 'include/connexion.php';
include 'include/MultilingualSupport.php';
include 'frontoffice/include/header2.php';

// Initialize multilingual support
$multilingual = new MultilingualSupport($db);

// Get search parameters
$searchQuery = $_GET['q'] ?? '';
$location = $_GET['location'] ?? '';
$domaine = $_GET['domaine'] ?? '';
$jobType = $_GET['job_type'] ?? '';
$experience = $_GET['experience'] ?? '';
$education = $_GET['education'] ?? '';
$remoteWork = $_GET['remote_work'] ?? '';
$salaryMin = $_GET['salary_min'] ?? '';
$salaryMax = $_GET['salary_max'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$perPage = 20;

// Build search query
$whereConditions = [];
$params = [];

// Search in both annonces and aggregated_jobs
$searchFields = [
    'annonces' => ['titre', 'description', 'company_name'],
    'aggregated_jobs' => ['title', 'description', 'company_name']
];

if (!empty($searchQuery)) {
    $searchConditions = [];
    foreach ($searchFields as $table => $fields) {
        foreach ($fields as $field) {
            $searchConditions[] = "($table.$field LIKE ?)";
            $params[] = "%$searchQuery%";
        }
    }
    $whereConditions[] = "(" . implode(' OR ', $searchConditions) . ")";
}

if (!empty($location)) {
    $whereConditions[] = "(annonces.ville_id = ? OR aggregated_jobs.location LIKE ?)";
    $params[] = $location;
    $params[] = "%$location%";
}

if (!empty($domaine)) {
    $whereConditions[] = "annonces.domaine_id = ?";
    $params[] = $domaine;
}

if (!empty($jobType)) {
    $whereConditions[] = "(annonces.job_type = ? OR aggregated_jobs.job_type = ?)";
    $params[] = $jobType;
    $params[] = $jobType;
}

if (!empty($experience)) {
    $whereConditions[] = "(annonces.experience_level = ? OR aggregated_jobs.experience_level = ?)";
    $params[] = $experience;
    $params[] = $experience;
}

if (!empty($education)) {
    $whereConditions[] = "(annonces.education_level = ? OR aggregated_jobs.education_level = ?)";
    $params[] = $education;
    $params[] = $education;
}

if (!empty($remoteWork)) {
    $whereConditions[] = "(annonces.remote_work = ? OR aggregated_jobs.remote_work = ?)";
    $params[] = $remoteWork;
    $params[] = $remoteWork;
}

if (!empty($salaryMin)) {
    $whereConditions[] = "(annonces.salary_min >= ? OR aggregated_jobs.salary_min >= ?)";
    $params[] = $salaryMin;
    $params[] = $salaryMin;
}

if (!empty($salaryMax)) {
    $whereConditions[] = "(annonces.salary_max <= ? OR aggregated_jobs.salary_max <= ?)";
    $params[] = $salaryMax;
    $params[] = $salaryMax;
}

// Build the main query
$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

$query = "
    SELECT 
        'annonce' as source_type,
        a.id,
        a.titre as title,
        a.description,
        a.company_name,
        v.nom as location,
        d.nom as domaine_name,
        a.salary_min,
        a.salary_max,
        a.job_type,
        a.experience_level,
        a.education_level,
        a.remote_work,
        a.date_publication as posted_date,
        a.views_count,
        a.applications_count,
        NULL as external_url,
        NULL as source_name,
        a.featured,
        a.urgent
    FROM annonces a
    LEFT JOIN villes v ON a.ville_id = v.id
    LEFT JOIN domaines d ON a.domaine_id = d.id
    WHERE a.status = 'active'
    
    UNION ALL
    
    SELECT 
        'aggregated' as source_type,
        aj.id,
        aj.title,
        aj.description,
        aj.company_name,
        aj.location,
        NULL as domaine_name,
        aj.salary_min,
        aj.salary_max,
        aj.job_type,
        aj.experience_level,
        aj.education_level,
        aj.remote_work,
        aj.posted_date,
        0 as views_count,
        0 as applications_count,
        aj.external_url,
        js.name as source_name,
        0 as featured,
        0 as urgent
    FROM aggregated_jobs aj
    JOIN job_sources js ON aj.source_id = js.id
    WHERE aj.status = 'active'
    
    $whereClause
    
    ORDER BY featured DESC, urgent DESC, posted_date DESC
    LIMIT " . (($page - 1) * $perPage) . ", $perPage
";

// Execute query
$jobs = $db->fetchAll($query, $params);

// Get total count for pagination
$countQuery = "
    SELECT COUNT(*) as total FROM (
        SELECT a.id FROM annonces a WHERE a.status = 'active'
        UNION ALL
        SELECT aj.id FROM aggregated_jobs aj WHERE aj.status = 'active'
    ) as combined_jobs
    $whereClause
";

$totalJobs = $db->fetch($countQuery, $params)['total'];
$totalPages = ceil($totalJobs / $perPage);

// Get filter options
$domaines = $db->fetchAll("SELECT * FROM domaines WHERE status = 'active' ORDER BY nom");
$villes = $db->fetchAll("SELECT * FROM villes WHERE status = 'active' ORDER BY nom");

$page_title = $multilingual->t('job_search');
?>

<div class="container-fluid py-5">
    <div class="container">
        <!-- Search Header -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="text-center">
                    <h1 class="display-4 mb-3">
                        <i class="fas fa-search me-3"></i>
                        <?= $multilingual->t('job_search') ?>
                    </h1>
                    <p class="lead text-muted">
                        <?= $multilingual->t('search_description') ?>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Search Form -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-4">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-4">
                                <label for="q" class="form-label">
                                    <i class="fas fa-search me-2"></i>
                                    <?= $multilingual->t('job_title') ?>
                                </label>
                                <input type="text" class="form-control form-control-lg" id="q" name="q" 
                                       value="<?= htmlspecialchars($searchQuery) ?>" 
                                       placeholder="<?= $multilingual->t('search_placeholder') ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="location" class="form-label">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    <?= $multilingual->t('job_location') ?>
                                </label>
                                <select class="form-select form-select-lg" id="location" name="location">
                                    <option value=""><?= $multilingual->t('filter_all') ?></option>
                                    <?php foreach ($villes as $ville): ?>
                                    <option value="<?= $ville['id'] ?>" <?= $location == $ville['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($multilingual->getLocalizedVille($ville)['nom']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="domaine" class="form-label">
                                    <i class="fas fa-briefcase me-2"></i>
                                    <?= $multilingual->t('filter_category') ?>
                                </label>
                                <select class="form-select form-select-lg" id="domaine" name="domaine">
                                    <option value=""><?= $multilingual->t('filter_all') ?></option>
                                    <?php foreach ($domaines as $domaine_item): ?>
                                    <option value="<?= $domaine_item['id'] ?>" <?= $domaine == $domaine_item['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($multilingual->getLocalizedDomaine($domaine_item)['nom']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="job_type" class="form-label">
                                    <i class="fas fa-clock me-2"></i>
                                    <?= $multilingual->t('job_type') ?>
                                </label>
                                <select class="form-select" id="job_type" name="job_type">
                                    <option value=""><?= $multilingual->t('filter_all') ?></option>
                                    <option value="full-time" <?= $jobType === 'full-time' ? 'selected' : '' ?>>
                                        <?= $multilingual->t('job_type_full_time') ?>
                                    </option>
                                    <option value="part-time" <?= $jobType === 'part-time' ? 'selected' : '' ?>>
                                        <?= $multilingual->t('job_type_part_time') ?>
                                    </option>
                                    <option value="internship" <?= $jobType === 'internship' ? 'selected' : '' ?>>
                                        <?= $multilingual->t('job_type_internship') ?>
                                    </option>
                                    <option value="freelance" <?= $jobType === 'freelance' ? 'selected' : '' ?>>
                                        <?= $multilingual->t('job_type_freelance') ?>
                                    </option>
                                    <option value="contract" <?= $jobType === 'contract' ? 'selected' : '' ?>>
                                        <?= $multilingual->t('job_type_contract') ?>
                                    </option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="experience" class="form-label">
                                    <i class="fas fa-user-tie me-2"></i>
                                    <?= $multilingual->t('job_experience') ?>
                                </label>
                                <select class="form-select" id="experience" name="experience">
                                    <option value=""><?= $multilingual->t('filter_all') ?></option>
                                    <option value="entry" <?= $experience === 'entry' ? 'selected' : '' ?>>
                                        <?= $multilingual->t('exp_entry') ?>
                                    </option>
                                    <option value="junior" <?= $experience === 'junior' ? 'selected' : '' ?>>
                                        <?= $multilingual->t('exp_junior') ?>
                                    </option>
                                    <option value="mid" <?= $experience === 'mid' ? 'selected' : '' ?>>
                                        <?= $multilingual->t('exp_mid') ?>
                                    </option>
                                    <option value="senior" <?= $experience === 'senior' ? 'selected' : '' ?>>
                                        <?= $multilingual->t('exp_senior') ?>
                                    </option>
                                    <option value="executive" <?= $experience === 'executive' ? 'selected' : '' ?>>
                                        <?= $multilingual->t('exp_executive') ?>
                                    </option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="remote_work" class="form-label">
                                    <i class="fas fa-home me-2"></i>
                                    <?= $multilingual->t('filter_remote') ?>
                                </label>
                                <select class="form-select" id="remote_work" name="remote_work">
                                    <option value=""><?= $multilingual->t('filter_all') ?></option>
                                    <option value="on-site" <?= $remoteWork === 'on-site' ? 'selected' : '' ?>>
                                        <?= $multilingual->t('remote_on_site') ?>
                                    </option>
                                    <option value="remote" <?= $remoteWork === 'remote' ? 'selected' : '' ?>>
                                        <?= $multilingual->t('remote_remote') ?>
                                    </option>
                                    <option value="hybrid" <?= $remoteWork === 'hybrid' ? 'selected' : '' ?>>
                                        <?= $multilingual->t('remote_hybrid') ?>
                                    </option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="salary_min" class="form-label">
                                    <i class="fas fa-money-bill-wave me-2"></i>
                                    <?= $multilingual->t('filter_salary') ?> (MAD)
                                </label>
                                <input type="number" class="form-control" id="salary_min" name="salary_min" 
                                       value="<?= htmlspecialchars($salaryMin) ?>" placeholder="Min">
                            </div>
                            
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-search me-2"></i>
                                        <?= $multilingual->t('btn_search') ?>
                                    </button>
                                    <a href="?" class="btn btn-outline-secondary btn-lg">
                                        <i class="fas fa-times me-2"></i>
                                        <?= $multilingual->t('btn_cancel') ?>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Search Results -->
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>
                        <?= $multilingual->t('search_results') ?> 
                        <span class="badge bg-primary"><?= number_format($totalJobs) ?></span>
                    </h3>
                    <div class="d-flex gap-2">
                        <select class="form-select" style="width: auto;" onchange="changePerPage(this.value)">
                            <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10 <?= $multilingual->t('per_page') ?></option>
                            <option value="20" <?= $perPage == 20 ? 'selected' : '' ?>>20 <?= $multilingual->t('per_page') ?></option>
                            <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50 <?= $multilingual->t('per_page') ?></option>
                        </select>
                    </div>
                </div>
                
                <?php if (empty($jobs)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4><?= $multilingual->t('msg_no_results') ?></h4>
                    <p class="text-muted"><?= $multilingual->t('try_different_search') ?></p>
                </div>
                <?php else: ?>
                
                <!-- Job Listings -->
                <div class="row">
                    <?php foreach ($jobs as $job): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100 shadow-sm border-0 job-card <?= $job['featured'] ? 'featured' : '' ?> <?= $job['urgent'] ? 'urgent' : '' ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-2">
                                            <a href="<?= $job['source_type'] === 'aggregated' ? $job['external_url'] : 'annoncedetaile.php?id=' . $job['id'] ?>" 
                                               class="text-decoration-none" target="<?= $job['source_type'] === 'aggregated' ? '_blank' : '_self' ?>">
                                                <?= htmlspecialchars($job['title']) ?>
                                            </a>
                                        </h5>
                                        <h6 class="text-muted mb-2">
                                            <i class="fas fa-building me-2"></i>
                                            <?= htmlspecialchars($job['company_name']) ?>
                                        </h6>
                                    </div>
                                    <div class="text-end">
                                        <?php if ($job['featured']): ?>
                                        <span class="badge bg-warning mb-1">
                                            <i class="fas fa-star"></i> Featured
                                        </span><br>
                                        <?php endif; ?>
                                        <?php if ($job['urgent']): ?>
                                        <span class="badge bg-danger mb-1">
                                            <i class="fas fa-exclamation"></i> Urgent
                                        </span><br>
                                        <?php endif; ?>
                                        <?php if ($job['source_type'] === 'aggregated'): ?>
                                        <span class="badge bg-info">
                                            <i class="fas fa-external-link-alt"></i> <?= htmlspecialchars($job['source_name']) ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <p class="card-text text-muted mb-3">
                                    <?= htmlspecialchars(substr($job['description'], 0, 150)) ?>...
                                </p>
                                
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?= htmlspecialchars($job['location']) ?>
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= $multilingual->timeAgo($job['posted_date']) ?>
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex gap-2">
                                        <?php if ($job['job_type']): ?>
                                        <span class="badge bg-secondary">
                                            <?= $multilingual->t('job_type_' . str_replace('-', '_', $job['job_type'])) ?>
                                        </span>
                                        <?php endif; ?>
                                        <?php if ($job['experience_level']): ?>
                                        <span class="badge bg-info">
                                            <?= $multilingual->t('exp_' . $job['experience_level']) ?>
                                        </span>
                                        <?php endif; ?>
                                        <?php if ($job['remote_work']): ?>
                                        <span class="badge bg-success">
                                            <?= $multilingual->t('remote_' . str_replace('-', '_', $job['remote_work'])) ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <?php if ($job['source_type'] === 'annonce'): ?>
                                        <a href="annoncedetaile.php?id=<?= $job['id'] ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>
                                            <?= $multilingual->t('btn_view') ?>
                                        </a>
                                        <?php else: ?>
                                        <a href="<?= $job['external_url'] ?>" class="btn btn-primary btn-sm" target="_blank">
                                            <i class="fas fa-external-link-alt me-1"></i>
                                            <?= $multilingual->t('btn_apply') ?>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav aria-label="Job search pagination" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.job-card {
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.job-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}

.job-card.featured {
    border-left-color: var(--emploidb-warning);
    background: linear-gradient(135deg, #fff9e6 0%, #ffffff 100%);
}

.job-card.urgent {
    border-left-color: var(--emploidb-error);
    background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
}

.pagination .page-link {
    color: var(--emploidb-primary);
    border-color: var(--emploidb-neutral-300);
}

.pagination .page-item.active .page-link {
    background-color: var(--emploidb-primary);
    border-color: var(--emploidb-primary);
}

.pagination .page-link:hover {
    background-color: var(--emploidb-primary-50);
    border-color: var(--emploidb-primary);
}
</style>

<script>
function changePerPage(perPage) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page'); // Reset to first page
    window.location.href = url.toString();
}
</script>

<?php include 'frontoffice/include/footer2.php'; ?>



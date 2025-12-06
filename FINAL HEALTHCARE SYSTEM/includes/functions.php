<?php
// includes/functions.php
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function getStatusBadge($status) {
    $badges = [
        'Pending' => '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>',
        'Done' => '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Done</span>',
        'Archived' => '<span class="badge badge-secondary"><i class="fas fa-archive"></i> Archived</span>',
        'Rescheduled' => '<span class="badge badge-info"><i class="fas fa-calendar-alt"></i> Rescheduled</span>'
    ];
    return $badges[$status] ?? '<span class="badge badge-light">Unknown</span>';
}

function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

function showAlert($type, $message) {
    $icons = [
        'success' => 'check-circle',
        'error' => 'exclamation-triangle',
        'warning' => 'exclamation-circle',
        'info' => 'info-circle'
    ];
    
    $icon = $icons[$type] ?? 'info-circle';
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                <i class="fas fa-' . $icon . ' me-2"></i>
                ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
}

function validatePhone($phone) {
    // Simple phone validation
    $phone = preg_replace('/\D/', '', $phone);
    return strlen($phone) >= 10;
}

function getAgeRange($age) {
    if ($age < 18) return 'Child';
    if ($age < 65) return 'Adult';
    return 'Senior';
}
?>
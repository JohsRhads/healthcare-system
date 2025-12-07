<?php
// patients.php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth($db);
$auth->requireLogin();

$message = '';
$messageType = '';

// Handle status updates
if (isset($_GET['update_status'])) {
    $patient_id = intval($_GET['id']);
    $status = sanitize($_GET['status']);
    
    $query = "UPDATE patients SET status = :status WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $patient_id);
    
    if ($stmt->execute()) {
        $message = "Status updated successfully!";
        $messageType = 'success';
    } else {
        $message = "Failed to update status!";
        $messageType = 'error';
    }
}

// Handle search and filters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$gender_filter = $_GET['gender'] ?? '';

// Build query with filters
$query = "SELECT * FROM patients WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (full_name LIKE :search OR phone_number LIKE :search OR illness_diagnosis LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($status_filter)) {
    $query .= " AND status = :status";
    $params[':status'] = $status_filter;
}

if (!empty($gender_filter)) {
    $query .= " AND gender = :gender";
    $params[':gender'] = $gender_filter;
}

$query .= " ORDER BY created_at DESC";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalPatients = count($patients);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Management - Healthcare System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .filters {
            background: var(--white);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
        }
        
        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .filter-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }
        
        .patient-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow);
            display: none;
        }
        
        .patient-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--gray-light);
        }
        
        .patient-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .info-label {
            font-size: 0.875rem;
            color: var(--gray);
            font-weight: 500;
        }
        
        .info-value {
            font-weight: 500;
        }
        
        .patient-notes {
            background: var(--light);
            padding: 1rem;
            border-radius: var(--radius);
            margin-top: 1rem;
            font-size: 0.875rem;
        }
        
        .btn-status {
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                text-align: center;
            }
            
            .filter-row {
                grid-template-columns: 1fr;
            }
            
            .filter-actions {
                justify-content: center;
            }
            
            .patient-info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <nav class="navbar">
                <a href="dashboard.php" class="logo">
                    <i class="fas fa-heartbeat"></i> Healthcare System
                </a>
                <div class="nav-links">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="register.php">Add Patient</a>
                    <a href="patients.php" class="nav-link active">Patient Records</a>
                    <a href="logout.php" class="btn btn-danger btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </nav>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <div>
                <h1 class="card-title">
                    <i class="fas fa-users"></i> Patient Management
                </h1>
                <p><?php echo $totalPatients; ?> patient(s) found</p>
            </div>
            <div>
                <a href="register.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Add New Patient
                </a>
              
            </div>
        </div>
        
        <?php if ($message): ?>
            <?php echo showAlert($messageType, $message); ?>
        <?php endif; ?>
        
        <div class="filters fade-in">
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="filter-group">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by name, phone, or diagnosis..."
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Done" <?php echo $status_filter == 'Done' ? 'selected' : ''; ?>>Done</option>
                            <option value="Archived" <?php echo $status_filter == 'Archived' ? 'selected' : ''; ?>>Archived</option>
                            <option value="Rescheduled" <?php echo $status_filter == 'Rescheduled' ? 'selected' : ''; ?>>Rescheduled</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-control">
                            <option value="">All Genders</option>
                            <option value="Male" <?php echo $gender_filter == 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $gender_filter == 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo $gender_filter == 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <a href="patients.php" class="btn btn-outline">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
        
        <?php if ($totalPatients > 0): ?>
            <!-- Mobile View -->
            <div class="mobile-view">
                <?php foreach ($patients as $patient): ?>
                <div class="patient-card">
                    <div class="patient-card-header">
                        <div>
                            <h4 style="margin: 0;"><?php echo htmlspecialchars($patient['full_name']); ?></h4>
                            <small class="text-muted">ID: <?php echo $patient['id']; ?></small>
                        </div>
                        <div>
                            <?php echo getStatusBadge($patient['status']); ?>
                        </div>
                    </div>
                    
                    <div class="patient-info-grid">
                        <div class="info-item">
                            <span class="info-label">Age / Gender</span>
                            <span class="info-value"><?php echo $patient['age']; ?> / <?php echo $patient['gender']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Phone</span>
                            <span class="info-value"><?php echo htmlspecialchars($patient['phone_number']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Appointment Date</span>
                            <span class="info-value"><?php echo formatDate($patient['appointment_date']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Diagnosis</span>
                            <span class="info-value"><?php echo htmlspecialchars($patient['illness_diagnosis']); ?></span>
                        </div>
                    </div>
                    
                    <?php if (!empty($patient['symptoms'])): ?>
                        <div class="info-item">
                            <span class="info-label">Symptoms</span>
                            <span class="info-value"><?php echo htmlspecialchars($patient['symptoms']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($patient['notes'])): ?>
                        <div class="patient-notes">
                            <strong>Notes:</strong> <?php echo htmlspecialchars($patient['notes']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="patient-actions" style="margin-top: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <a href="patients.php?update_status=true&id=<?php echo $patient['id']; ?>&status=Done" 
                           class="btn btn-sm btn-success btn-status"
                           onclick="return confirm('Mark <?php echo htmlspecialchars(addslashes($patient['full_name'])); ?> as completed?')">
                            <i class="fas fa-check"></i> Done
                        </a>
                        <a href="patients.php?update_status=true&id=<?php echo $patient['id']; ?>&status=Pending" 
                           class="btn btn-sm btn-warning btn-status"
                           onclick="return confirm('Set <?php echo htmlspecialchars(addslashes($patient['full_name'])); ?> as pending?')">
                            <i class="fas fa-clock"></i> Pending
                        </a>
                        <a href="patients.php?update_status=true&id=<?php echo $patient['id']; ?>&status=Archived" 
                           class="btn btn-sm btn-secondary btn-status"
                           onclick="return confirm('Archive <?php echo htmlspecialchars(addslashes($patient['full_name'])); ?>? This will hide from active lists.')">
                            <i class="fas fa-archive"></i> Archive
                        </a>
                        <a href="patients.php?update_status=true&id=<?php echo $patient['id']; ?>&status=Rescheduled" 
                           class="btn btn-sm btn-info btn-status"
                           onclick="return confirm('Mark <?php echo htmlspecialchars(addslashes($patient['full_name'])); ?> for rescheduling?')">
                            <i class="fas fa-calendar-alt"></i> Reschedule
                        </a>
                        <a href="edit_patient.php?id=<?php echo $patient['id']; ?>" 
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Desktop View -->
            <div class="table-container fade-in">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient Name</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>Phone</th>
                            <th>Diagnosis</th>
                            <th>Appointment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $patient): ?>
                        <tr data-patient-id="<?php echo $patient['id']; ?>">
                            <td><?php echo $patient['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($patient['full_name']); ?></strong>
                                <?php if (!empty($patient['notes'])): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars(substr($patient['notes'], 0, 30)); ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $patient['age']; ?></td>
                            <td><?php echo $patient['gender']; ?></td>
                            <td><?php echo htmlspecialchars($patient['phone_number']); ?></td>
                            <td><?php echo htmlspecialchars($patient['illness_diagnosis']); ?></td>
                            <td><?php echo formatDate($patient['appointment_date']); ?></td>
                            <td>
                                <?php echo getStatusBadge($patient['status']); ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                    <a href="patients.php?update_status=true&id=<?php echo $patient['id']; ?>&status=Done" 
                                       class="btn btn-sm btn-success btn-status"
                                       onclick="return confirm('Mark <?php echo htmlspecialchars(addslashes($patient['full_name'])); ?> as completed?')">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <a href="patients.php?update_status=true&id=<?php echo $patient['id']; ?>&status=Pending" 
                                       class="btn btn-sm btn-warning btn-status"
                                       onclick="return confirm('Set <?php echo htmlspecialchars(addslashes($patient['full_name'])); ?> as pending?')">
                                        <i class="fas fa-clock"></i>
                                    </a>
                                    <a href="patients.php?update_status=true&id=<?php echo $patient['id']; ?>&status=Archived" 
                                       class="btn btn-sm btn-secondary btn-status"
                                       onclick="return confirm('Archive <?php echo htmlspecialchars(addslashes($patient['full_name'])); ?>? This will hide from active lists.')">
                                        <i class="fas fa-archive"></i>
                                    </a>
                                    <a href="patients.php?update_status=true&id=<?php echo $patient['id']; ?>&status=Rescheduled" 
                                       class="btn btn-sm btn-info btn-status"
                                       onclick="return confirm('Mark <?php echo htmlspecialchars(addslashes($patient['full_name'])); ?> for rescheduling?')">
                                        <i class="fas fa-calendar-alt"></i>
                                    </a>
                                    <a href="edit_patient.php?id=<?php echo $patient['id']; ?>" 
                                       class="btn btn-sm btn-primary"
                                       title="Edit Patient">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="card text-center">
                <div class="no-data">
                    <i class="fas fa-user-slash fa-3x"></i>
                    <h3>No patients found</h3>
                    <p>Try adjusting your search filters or add a new patient.</p>
                    <a href="register.php" class="btn btn-primary mt-2">
                        <i class="fas fa-user-plus"></i> Add New Patient
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="footer">
        <div class="container">
            <p>© <?php echo date('Y'); ?> Healthcare Appointment System • Patient Records</p>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Toggle between mobile and desktop views
        function toggleView() {
            const mobileView = document.querySelector('.mobile-view');
            const desktopView = document.querySelector('.table-container');
            
            if (window.innerWidth < 768) {
                mobileView.style.display = 'block';
                desktopView.style.display = 'none';
            } else {
                mobileView.style.display = 'none';
                desktopView.style.display = 'block';
            }
        }
        
        // Initial check
        toggleView();
        
        // Check on resize
        window.addEventListener('resize', toggleView);
        
        // Patient row click for details
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('tr[data-patient-id]');
            rows.forEach(row => {
                row.addEventListener('click', function(e) {
                    if (!e.target.closest('.btn')) {
                        const patientId = this.getAttribute('data-patient-id');
                        // Could implement a modal view here
                        console.log('View patient:', patientId);
                    }
                });
            });
        });
    </script>
</body>
</html>
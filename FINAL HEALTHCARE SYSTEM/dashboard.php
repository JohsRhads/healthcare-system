<?php
// dashboard.php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth($db);
$auth->requireLogin();

$adminInfo = $auth->getAdminInfo();
$adminName = htmlspecialchars($adminInfo['name']);

try {
    $query = "SELECT 
        COUNT(*) as total_patients,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as done,
        SUM(CASE WHEN status = 'Archived' THEN 1 ELSE 0 END) as archived,
        SUM(CASE WHEN status = 'Rescheduled' THEN 1 ELSE 0 END) as rescheduled,
        SUM(CASE WHEN gender = 'Male' THEN 1 ELSE 0 END) as male,
        SUM(CASE WHEN gender = 'Female' THEN 1 ELSE 0 END) as female,
        AVG(age) as avg_age
        FROM patients";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$stats) {
        $stats = [
            'total_patients' => 0,
            'pending' => 0,
            'done' => 0,
            'archived' => 0,
            'rescheduled' => 0,
            'male' => 0,
            'female' => 0,
            'avg_age' => 0
        ];
    }
    
    $recentQuery = "SELECT * FROM patients ORDER BY created_at DESC LIMIT 5";
    $recentStmt = $db->prepare($recentQuery);
    $recentStmt->execute();
    $recentPatients = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get appointments for today
    $todayQuery = "SELECT COUNT(*) as today_appointments FROM patients WHERE DATE(appointment_date) = CURDATE()";
    $todayStmt = $db->prepare($todayQuery);
    $todayStmt->execute();
    $todayStats = $todayStmt->fetch(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $stats = [
        'total_patients' => 0,
        'pending' => 0,
        'done' => 0,
        'archived' => 0,
        'rescheduled' => 0,
        'male' => 0,
        'female' => 0,
        'avg_age' => 0
    ];
    $recentPatients = [];
    $todayStats = ['today_appointments' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Healthcare System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .dashboard-page {
            background: var(--light);
            min-height: 100vh;
        }
        
        .welcome-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 2.5rem;
            border-radius: var(--radius-lg);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-lg);
        }
        
        .welcome-content h2 {
            color: white;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }
        
        .welcome-content p {
            opacity: 0.9;
            margin: 0;
            font-size: 1rem;
        }
        
        .main-action-button {
            background: white;
            color: var(--primary);
            padding: 0.875rem 1.75rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .main-action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
            border-top: 4px solid var(--primary);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .stat-card.pending { border-color: var(--warning); }
        .stat-card.done { border-color: var(--success); }
        .stat-card.archived { border-color: var(--gray); }
        .stat-card.rescheduled { border-color: var(--info); }
        
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }
        
        .stat-card.pending .stat-icon { color: var(--warning); }
        .stat-card.done .stat-icon { color: var(--success); }
        .stat-card.archived .stat-icon { color: var(--gray); }
        .stat-card.rescheduled .stat-icon { color: var(--info); }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0.5rem 0;
            line-height: 1;
        }
        
        .stat-label {
            color: var(--gray);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        
        .quick-actions {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: var(--shadow);
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .action-item {
            text-align: center;
            padding: 1.5rem;
            background: var(--light);
            border-radius: var(--radius);
            transition: var(--transition);
            text-decoration: none;
            color: var(--dark);
            border: 2px solid transparent;
        }
        
        .action-item:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
            border-color: var(--primary);
        }
        
        .action-item:hover .action-icon {
            color: white;
        }
        
        .action-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }
        
        .action-item h4 {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
        }
        
        .recent-section {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: var(--shadow);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-light);
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .view-all {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
        }
        
        .view-all:hover {
            gap: 0.75rem;
        }
        
        .patient-row {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--gray-light);
            transition: var(--transition);
        }
        
        .patient-row:hover {
            background: rgba(37, 99, 235, 0.05);
        }
        
        .patient-row:last-child {
            border-bottom: none;
        }
        
        .patient-avatar {
            width: 40px;
            height: 40px;
            background: var(--light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            margin-right: 1rem;
            font-weight: 600;
        }
        
        .patient-info {
            flex: 1;
        }
        
        .patient-name {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }
        
        .patient-details {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: var(--gray);
            flex-wrap: wrap;
        }
        
        .patient-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .action-btn {
            padding: 0.5rem;
            border-radius: var(--radius);
            background: var(--light);
            color: var(--dark);
            text-decoration: none;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
        }
        
        .action-btn:hover {
            background: var(--primary);
            color: white;
        }
        
        .no-data {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
        }
        
        .no-data i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        
        .summary-card {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        
        .summary-card h4 {
            margin-bottom: 1rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .summary-list {
            list-style: none;
            padding: 0;
        }
        
        .summary-list li {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .summary-list li:last-child {
            border-bottom: none;
        }
        
        @media (max-width: 768px) {
            .welcome-header {
                flex-direction: column;
                gap: 1.5rem;
                text-align: center;
            }
            
            .patient-row {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .patient-info {
                text-align: center;
            }
            
            .patient-details {
                justify-content: center;
            }
            
            .patient-actions {
                justify-content: center;
            }
        }
    </style>
</head>
<body class="dashboard-page">
    <div class="header">
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="logo">
                    <i class="fas fa-heartbeat"></i>
                    <div class="logo-text">
                        <span class="logo-main">HealthCare Pro</span>
                        <span class="logo-tagline">Admin Dashboard</span>
                    </div>
                </a>
                <div class="nav-links">
                    <div class="user-info">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo $adminName; ?></span>
                    </div>
                    <a href="logout.php" class="btn btn-danger btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </nav>
        </div>
    </div>

    <div class="container">
        <div class="welcome-header fade-in">
            <div class="welcome-content">
                <h2>Welcome, <?php echo $adminName; ?>! 👋</h2>
                <p>Manage your healthcare system efficiently from one dashboard.</p>
            </div>
            <a href="register.php" class="main-action-button">
                <i class="fas fa-user-plus"></i> Add New Patient
            </a>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="stats-summary fade-in">
            <div class="summary-card">
                <h4><i class="fas fa-chart-pie"></i> Patient Status Overview</h4>
                <ul class="summary-list">
                    <li>Total Patients: <strong><?php echo $stats['total_patients']; ?></strong></li>
                    <li>Today's Appointments: <strong><?php echo $todayStats['today_appointments']; ?></strong></li>
                    <li>Average Age: <strong><?php echo round($stats['avg_age'], 1); ?> years</strong></li>
                </ul>
            </div>
            
            <div class="summary-card">
                <h4><i class="fas fa-venus-mars"></i> Gender Distribution</h4>
                <ul class="summary-list">
                    <li>Male: <strong><?php echo $stats['male']; ?></strong></li>
                    <li>Female: <strong><?php echo $stats['female']; ?></strong></li>
                    <li>Other: <strong><?php echo $stats['total_patients'] - $stats['male'] - $stats['female']; ?></strong></li>
                </ul>
            </div>
        </div>
        
        <h2 class="mb-3">Appointment Status</h2>
        
        <div class="dashboard-stats">
            <div class="stat-card pending">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?php echo $stats['pending']; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            
            <div class="stat-card done">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value"><?php echo $stats['done']; ?></div>
                <div class="stat-label">Completed</div>
            </div>
            
            <div class="stat-card archived">
                <div class="stat-icon">
                    <i class="fas fa-archive"></i>
                </div>
                <div class="stat-value"><?php echo $stats['archived']; ?></div>
                <div class="stat-label">Archived</div>
            </div>
            
            <div class="stat-card rescheduled">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-value"><?php echo $stats['rescheduled']; ?></div>
                <div class="stat-label">Rescheduled</div>
            </div>
        </div>
        
        <div class="quick-actions fade-in">
            <h3 class="mb-3">Quick Actions</h3>
            <div class="actions-grid">
                <a href="register.php" class="action-item">
                    <div class="action-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h4>Add Patient</h4>
                </a>
                
                <a href="patients.php" class="action-item">
                    <div class="action-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4>View All Patients</h4>
                </a>
                
                <a href="#" class="action-item">
                    <div class="action-icon">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <h4>Manage Schedule</h4>
                </a>
                
                <a href="#" class="action-item">
                    <div class="action-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h4>Generate Reports</h4>
                </a>
                
                <a href="#" class="action-item">
                    <div class="action-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h4>Notifications</h4>
                </a>
                
               
            </div>
        </div>
        
        <div class="recent-section fade-in">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-history"></i> Recent Patients
                </h3>
                <a href="patients.php" class="view-all">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <?php if (!empty($recentPatients)): ?>
                <div class="patient-list">
                    <?php foreach ($recentPatients as $patient): ?>
                    <div class="patient-row">
                        <div class="patient-avatar">
                            <?php echo strtoupper(substr($patient['full_name'], 0, 1)); ?>
                        </div>
                        <div class="patient-info">
                            <div class="patient-name"><?php echo htmlspecialchars($patient['full_name']); ?></div>
                            <div class="patient-details">
                                <span><i class="fas fa-user"></i> Age: <?php echo $patient['age']; ?></span>
                                <span><i class="fas fa-venus-mars"></i> <?php echo $patient['gender']; ?></span>
                                <span><i class="fas fa-calendar"></i> <?php echo formatDate($patient['appointment_date']); ?></span>
                                <span><i class="fas fa-stethoscope"></i> <?php echo htmlspecialchars(substr($patient['illness_diagnosis'], 0, 30)); ?>...</span>
                            </div>
                        </div>
                        <div class="patient-status">
                            <?php echo getStatusBadge($patient['status']); ?>
                        </div>
                        <div class="patient-actions">
                            <a href="patients.php?view=<?php echo $patient['id']; ?>" class="action-btn" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="patients.php?update_status=true&id=<?php echo $patient['id']; ?>&status=Done" class="action-btn" title="Mark as Done">
                                <i class="fas fa-check"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-user-slash"></i>
                    <h4>No patients found</h4>
                    <p>Start by adding your first patient</p>
                    <a href="register.php" class="btn btn-primary mt-2">
                        <i class="fas fa-user-plus"></i> Add First Patient
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="footer">
        <div class="container">
            <p>© <?php echo date('Y'); ?> Healthcare Appointment System • Admin Dashboard</p>
            <p class="text-muted mt-1">Last updated: <?php echo date('F j, Y, g:i a'); ?></p>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animate numbers
            const statValues = document.querySelectorAll('.stat-value');
            statValues.forEach(stat => {
                const finalValue = parseInt(stat.textContent);
                let currentValue = 0;
                const increment = finalValue / 50;
                const timer = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= finalValue) {
                        stat.textContent = finalValue;
                        clearInterval(timer);
                    } else {
                        stat.textContent = Math.floor(currentValue);
                    }
                }, 30);
            });
            
            // Auto-refresh dashboard every 5 minutes
            setTimeout(() => {
                location.reload();
            }, 5 * 60 * 1000);
        });
    </script>
</body>
</html>
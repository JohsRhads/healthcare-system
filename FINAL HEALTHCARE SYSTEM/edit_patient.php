<?php
// edit_patient.php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth($db);
$auth->requireLogin();

$message = '';
$messageType = '';

// Get patient data
$patientId = intval($_GET['id'] ?? 0);
$patient = null;

if ($patientId > 0) {
    $query = "SELECT * FROM patients WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $patientId);
    $stmt->execute();
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$patient) {
        header("Location: patients.php");
        exit();
    }
} else {
    header("Location: patients.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $age = sanitize($_POST['age']);
    $gender = sanitize($_POST['gender']);
    $phone_number = sanitize($_POST['phone_number']);
    $appointment_date = sanitize($_POST['appointment_date']);
    $illness_diagnosis = sanitize($_POST['illness_diagnosis']);
    $symptoms = sanitize($_POST['symptoms'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    $status = sanitize($_POST['status'] ?? 'Pending');
    
    // Validate age
    if ($age < 0 || $age > 120) {
        $message = "Age must be between 0 and 120.";
        $messageType = 'error';
    } else {
        $query = "UPDATE patients SET 
            full_name = :full_name,
            age = :age,
            gender = :gender,
            phone_number = :phone_number,
            appointment_date = :appointment_date,
            illness_diagnosis = :illness_diagnosis,
            symptoms = :symptoms,
            notes = :notes,
            status = :status,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = :id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':age', $age);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':appointment_date', $appointment_date);
        $stmt->bindParam(':illness_diagnosis', $illness_diagnosis);
        $stmt->bindParam(':symptoms', $symptoms);
        $stmt->bindParam(':notes', $notes);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $patientId);
        
        if ($stmt->execute()) {
            $message = "Patient updated successfully!";
            $messageType = 'success';
            
            // Update local patient data for form
            $patient['full_name'] = $full_name;
            $patient['age'] = $age;
            $patient['gender'] = $gender;
            $patient['phone_number'] = $phone_number;
            $patient['appointment_date'] = $appointment_date;
            $patient['illness_diagnosis'] = $illness_diagnosis;
            $patient['symptoms'] = $symptoms;
            $patient['notes'] = $notes;
            $patient['status'] = $status;
        } else {
            $message = "Error updating patient.";
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Patient - Healthcare System</title>
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
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--gray-light);
        }
        
        .patient-info-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--light);
            border-radius: var(--radius);
            font-size: 0.875rem;
            color: var(--gray-dark);
            margin-top: 0.5rem;
        }
        
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            padding: 2.5rem;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--gray-light);
            flex-wrap: wrap;
        }
        
        .required-mark {
            color: var(--danger);
        }
        
        .form-note {
            font-size: 0.875rem;
            color: var(--gray);
            margin-top: 0.25rem;
            display: block;
        }
        
        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 600;
            margin-left: 1rem;
        }
        
        .status-pending { background: var(--warning); color: white; }
        .status-done { background: var(--success); color: white; }
        .status-archived { background: var(--gray); color: white; }
        .status-rescheduled { background: var(--info); color: white; }
        
        @media (max-width: 768px) {
            .form-container {
                padding: 1.5rem;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .form-actions .btn {
                width: 100%;
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
                    <a href="patients.php">Patient Records</a>
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
                    <i class="fas fa-user-edit"></i> Edit Patient
                    <span class="status-indicator status-<?php echo strtolower($patient['status']); ?>">
                        <?php echo $patient['status']; ?>
                    </span>
                </h1>
                <p>Update patient information</p>
                <div class="patient-info-badge">
                    <i class="fas fa-id-card"></i>
                    <span>Patient ID: <?php echo $patient['id']; ?></span>
                    <span style="margin-left: 1rem;">•</span>
                    <i class="fas fa-calendar"></i>
                    <span>Registered: <?php echo date('M j, Y', strtotime($patient['created_at'])); ?></span>
                </div>
            </div>
            <div>
                <a href="patients.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Patients
                </a>
            </div>
        </div>
        
        <?php if ($message): ?>
            <?php echo showAlert($messageType, $message); ?>
        <?php endif; ?>
        
        <div class="form-container fade-in">
            <form method="POST" action="" novalidate>
                <div class="form-group">
                    <label class="form-label">
                        Full Name <span class="required-mark">*</span>
                    </label>
                    <input type="text" name="full_name" class="form-control" 
                           value="<?php echo htmlspecialchars($patient['full_name']); ?>" required
                           placeholder="Enter patient's full name">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            Age <span class="required-mark">*</span>
                        </label>
                        <input type="number" name="age" class="form-control" min="0" max="120"
                               value="<?php echo $patient['age']; ?>" required
                               placeholder="Age">
                        <span class="form-note">Must be between 0 and 120</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Gender <span class="required-mark">*</span>
                        </label>
                        <select name="gender" class="form-control" required>
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo $patient['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $patient['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo $patient['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                            <option value="Prefer not to say" <?php echo $patient['gender'] == 'Prefer not to say' ? 'selected' : ''; ?>>Prefer not to say</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            Phone Number <span class="required-mark">*</span>
                        </label>
                        <input type="tel" name="phone_number" class="form-control" 
                               value="<?php echo htmlspecialchars($patient['phone_number']); ?>" required
                               placeholder="(123) 456-7890">
                        <span class="form-note">Format: (123) 456-7890</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Appointment Date <span class="required-mark">*</span>
                        </label>
                        <input type="date" name="appointment_date" class="form-control" 
                               value="<?php echo $patient['appointment_date']; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        Illness/Diagnosis <span class="required-mark">*</span>
                    </label>
                    <input type="text" name="illness_diagnosis" class="form-control" 
                           value="<?php echo htmlspecialchars($patient['illness_diagnosis']); ?>" required
                           placeholder="Enter diagnosis or illness">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Symptoms (Optional)</label>
                    <textarea name="symptoms" class="form-control" rows="3" 
                              placeholder="Describe symptoms" maxlength="500"><?php echo htmlspecialchars($patient['symptoms']); ?></textarea>
                    <span class="form-note">Maximum 500 characters</span>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea name="notes" class="form-control" rows="2" 
                              placeholder="Additional notes" maxlength="300"><?php echo htmlspecialchars($patient['notes']); ?></textarea>
                    <span class="form-note">Maximum 300 characters</span>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="Pending" <?php echo $patient['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Done" <?php echo $patient['status'] == 'Done' ? 'selected' : ''; ?>>Done</option>
                        <option value="Archived" <?php echo $patient['status'] == 'Archived' ? 'selected' : ''; ?>>Archived</option>
                        <option value="Rescheduled" <?php echo $patient['status'] == 'Rescheduled' ? 'selected' : ''; ?>>Rescheduled</option>
                    </select>
                    <span class="form-note">
                        <i class="fas fa-info-circle"></i> 
                        Archived patients are hidden from active lists but can be viewed using the status filter
                    </span>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <button type="reset" class="btn btn-outline btn-lg">
                        <i class="fas fa-redo"></i> Reset Form
                    </button>
                    <a href="patients.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Quick Actions Section -->
        <div class="card mt-4">
            <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
            <div style="display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap;">
                <a href="patients.php?update_status=true&id=<?php echo $patient['id']; ?>&status=Done" 
                   class="btn btn-success"
                   onclick="return confirm('Mark <?php echo htmlspecialchars(addslashes($patient['full_name'])); ?> as completed?')">
                    <i class="fas fa-check"></i> Mark as Done
                </a>
                <a href="patients.php?update_status=true&id=<?php echo $patient['id']; ?>&status=Archived" 
                   class="btn btn-secondary"
                   onclick="return confirm('Archive <?php echo htmlspecialchars(addslashes($patient['full_name'])); ?>? This will hide from active lists.')">
                    <i class="fas fa-archive"></i> Archive Patient
                </a>
                <a href="patients.php" 
                   class="btn btn-outline">
                    <i class="fas fa-list"></i> View All Patients
                </a>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <div class="container">
            <p>© <?php echo date('Y'); ?> Healthcare Appointment System • Patient Editor</p>
            <p class="text-muted mt-1">
                Last modified: <?php echo !empty($patient['updated_at']) ? date('M j, Y g:i A', strtotime($patient['updated_at'])) : 'Never'; ?>
            </p>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set min date for appointment date
            const today = new Date().toISOString().split('T')[0];
            const dateInput = document.querySelector('input[name="appointment_date"]');
            dateInput.min = today;
            
            // Set max date to 1 year from today
            const maxDate = new Date();
            maxDate.setFullYear(maxDate.getFullYear() + 1);
            dateInput.max = maxDate.toISOString().split('T')[0];
            
            // Phone number formatting
            const phoneInput = document.querySelector('input[name="phone_number"]');
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 0 && value.length <= 10) {
                    value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
                }
                e.target.value = value;
            });
            
            // Form validation
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const ageInput = document.querySelector('input[name="age"]');
                const age = parseInt(ageInput.value);
                
                if (age < 0 || age > 120) {
                    e.preventDefault();
                    alert('Age must be between 0 and 120.');
                    ageInput.focus();
                    return false;
                }
                
                // Show processing state
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                }
                
                return true;
            });
            
            // Focus on first input
            document.querySelector('input[name="full_name"]').focus();
        });
    </script>
</body>
</html>
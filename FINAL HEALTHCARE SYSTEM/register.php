<?php
// register.php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth($db);
$isAdmin = $auth->isLoggedIn();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $age = sanitize($_POST['age']);
    $gender = sanitize($_POST['gender']);
    $phone_number = sanitize($_POST['phone_number']);
    $appointment_date = sanitize($_POST['appointment_date']);
    $illness_diagnosis = sanitize($_POST['illness_diagnosis']);
    $symptoms = sanitize($_POST['symptoms'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    
    if (empty($full_name) || empty($age) || empty($gender) || empty($phone_number) || empty($appointment_date) || empty($illness_diagnosis)) {
        $message = "Please fill in all required fields!";
        $messageType = 'error';
    } else {
        $query = "INSERT INTO patients (full_name, age, gender, phone_number, appointment_date, illness_diagnosis, symptoms, notes) 
                  VALUES (:full_name, :age, :gender, :phone_number, :appointment_date, :illness_diagnosis, :symptoms, :notes)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':age', $age);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':appointment_date', $appointment_date);
        $stmt->bindParam(':illness_diagnosis', $illness_diagnosis);
        $stmt->bindParam(':symptoms', $symptoms);
        $stmt->bindParam(':notes', $notes);
        
        if ($stmt->execute()) {
            $message = "Patient registered successfully!";
            $messageType = 'success';
            $_POST = array();
        } else {
            $message = "Error registering patient. Please try again.";
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
    <title><?php echo $isAdmin ? 'Add New Patient' : 'Register as Patient'; ?> - Healthcare System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .register-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--gray-light);
        }
        
        .form-header h1 {
            color: var(--dark);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }
        
        .form-header p {
            color: var(--gray);
            font-size: 1.1rem;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 2rem;
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            transition: var(--transition);
        }
        
        .back-button:hover {
            background: var(--primary-light);
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--gray-light);
        }
        
        .required-mark {
            color: var(--danger);
        }
        
        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
            }
            
            .form-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body class="register-page">
    <div class="container">
        <a href="<?php echo $isAdmin ? 'dashboard.php' : 'index.php'; ?>" class="back-button">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        
        <div class="form-container fade-in">
            <div class="form-header">
                <h1>
                    <i class="fas fa-user-plus"></i> 
                    <?php echo $isAdmin ? 'Add New Patient' : 'Patient Registration'; ?>
                </h1>
                <p><?php echo $isAdmin ? 'Admin Mode - Add new patient to the system' : 'Fill in your details to register as a patient'; ?></p>
            </div>
            
            <?php if ($message): ?>
                <?php echo showAlert($messageType, $message); ?>
            <?php endif; ?>
            
            <form method="POST" action="" novalidate>
                <div class="form-group">
                    <label class="form-label">
                        Full Name <span class="required-mark">*</span>
                    </label>
                    <input type="text" name="full_name" class="form-control" 
                           value="<?php echo $_POST['full_name'] ?? ''; ?>" 
                           placeholder="Enter patient's full name" required maxlength="100">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            Age <span class="required-mark">*</span>
                        </label>
                        <input type="number" name="age" class="form-control" min="0" max="120"
                               value="<?php echo $_POST['age'] ?? ''; ?>" 
                               placeholder="Age" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Gender <span class="required-mark">*</span>
                        </label>
                        <select name="gender" class="form-control" required>
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo ($_POST['gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($_POST['gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($_POST['gender'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                            <option value="Prefer not to say" <?php echo ($_POST['gender'] ?? '') == 'Prefer not to say' ? 'selected' : ''; ?>>Prefer not to say</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        Phone Number <span class="required-mark">*</span>
                    </label>
                    <input type="tel" name="phone_number" class="form-control" 
                           value="<?php echo $_POST['phone_number'] ?? ''; ?>" 
                           placeholder="(123) 456-7890" required pattern="\([0-9]{3}\) [0-9]{3}-[0-9]{4}">
                    <small class="text-muted">Format: (123) 456-7890</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        Appointment Date <span class="required-mark">*</span>
                    </label>
                    <input type="date" name="appointment_date" class="form-control" 
                           value="<?php echo $_POST['appointment_date'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        Illness/Diagnosis <span class="required-mark">*</span>
                    </label>
                    <input type="text" name="illness_diagnosis" class="form-control" 
                           value="<?php echo $_POST['illness_diagnosis'] ?? ''; ?>" 
                           placeholder="Enter diagnosis or illness" required maxlength="200">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Symptoms (Optional)</label>
                    <textarea name="symptoms" class="form-control" rows="3" 
                              placeholder="Describe symptoms" maxlength="500"><?php echo $_POST['symptoms'] ?? ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea name="notes" class="form-control" rows="2" 
                              placeholder="Additional notes" maxlength="300"><?php echo $_POST['notes'] ?? ''; ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-user-plus"></i> 
                        <?php echo $isAdmin ? 'Add Patient' : 'Register as Patient'; ?>
                    </button>
                    
                    <button type="reset" class="btn btn-outline btn-lg">
                        <i class="fas fa-redo"></i> Reset Form
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="footer">
        <div class="container">
            <p>© <?php echo date('Y'); ?> Healthcare Appointment System • Patient Registration</p>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const dateInput = document.querySelector('input[name="appointment_date"]');
            dateInput.min = today;
            
            // Focus on first input
            document.querySelector('input[name="full_name"]').focus();
        });
    </script>
</body>
</html>
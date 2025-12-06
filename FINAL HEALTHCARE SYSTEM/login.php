<?php
// login.php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth($db);
$error = '';

// If already logged in, redirect to dashboard
if ($auth->isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = sanitize($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password!";
    } elseif ($auth->login($username, $password)) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Healthcare System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .login-box {
            width: 100%;
            max-width: 400px;
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            padding: 2.5rem;
            text-align: center;
        }
        
        .login-brand {
            margin-bottom: 2rem;
        }
        
        .login-brand .logo {
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .login-brand h1 {
            color: var(--dark);
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .login-brand p {
            color: var(--gray);
            font-size: 0.875rem;
        }
        
        .input-group {
            display: flex;
            border-radius: var(--radius);
            overflow: hidden;
            border: 2px solid var(--gray-light);
            transition: var(--transition);
        }
        
        .input-group:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }
        
        .input-group-text {
            background: var(--light);
            padding: 0 1rem;
            display: flex;
            align-items: center;
            color: var(--gray);
            border-right: 1px solid var(--gray-light);
        }
        
        .input-group .form-control {
            border: none;
            border-radius: 0;
        }
        
        .toggle-password {
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            padding: 0 1rem;
            display: flex;
            align-items: center;
        }
        
        .toggle-password:hover {
            color: var(--primary);
        }
        
        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            font-size: 0.875rem;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .forgot-password {
            color: var(--primary);
            text-decoration: none;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
        }
        
        .security-note {
            margin-top: 2rem;
            padding: 1rem;
            background: #f0f9ff;
            border-radius: var(--radius);
            border-left: 4px solid var(--info);
            text-align: left;
            font-size: 0.875rem;
            color: var(--gray-dark);
        }
        
        .security-note h4 {
            color: var(--info);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-box fade-in">
        <div class="login-brand">
            <a href="index.php" class="logo">
                <i class="fas fa-heartbeat"></i>
                <div class="logo-text">
                    <span class="logo-main">HealthCare Pro</span>
                </div>
            </a>
            <h1>Admin Login Portal</h1>
            <p>Secure access to healthcare management system</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="loginForm" autocomplete="off">
            <div class="form-group">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" name="username" class="form-control" 
                           placeholder="Enter your username" required 
                           autocomplete="username">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="password" id="password" class="form-control" 
                           placeholder="Enter your password" required 
                           autocomplete="current-password">
                    <button type="button" class="toggle-password" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
          
            
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-sign-in-alt"></i> Login to Dashboard
                </button>
            </div>
            
            <div class="text-center mt-3">
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>
        </form>
        
        <div class="security-note">
            <h4><i class="fas fa-shield-alt"></i> Security Notice</h4>
            <p>• Never share your login credentials</p>
            <p>• Always log out after your session</p>
            <p>• Report any suspicious activity</p>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.innerHTML = type === 'password' ? 
                    '<i class="fas fa-eye"></i>' : 
                    '<i class="fas fa-eye-slash"></i>';
            });
            
            // Form validation
            const form = document.getElementById('loginForm');
            form.addEventListener('submit', function(e) {
                const username = form.username.value.trim();
                const password = form.password.value.trim();
                
                if (!username || !password) {
                    e.preventDefault();
                    alert('Please enter both username and password.');
                    return false;
                }
                
                // Clear any error messages
                const errorAlert = document.querySelector('.alert-danger');
                if (errorAlert) {
                    errorAlert.style.transition = 'opacity 0.5s';
                    errorAlert.style.opacity = '0';
                    setTimeout(() => errorAlert.remove(), 500);
                }
                
                return true;
            });
            
            // Clear form on page load (prevent browser autofill)
            setTimeout(() => {
                form.username.value = '';
                form.password.value = '';
            }, 100);
            
            // Prevent paste in username field (optional security)
            document.querySelector('input[name="username"]').addEventListener('paste', function(e) {
                e.preventDefault();
                alert('Pasting is not allowed in the username field for security reasons.');
            });
            
            // Focus on username field
            document.querySelector('input[name="username"]').focus();
        });
    </script>
</body>
</html>
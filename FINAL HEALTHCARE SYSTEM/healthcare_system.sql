CREATE DATABASE IF NOT EXISTS healthcare_system;
USE healthcare_system;

-- Patients table
CREATE TABLE IF NOT EXISTS patients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    gender ENUM('Male', 'Female', 'Other', 'Prefer not to say') NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    appointment_date DATE NOT NULL,
    illness_diagnosis TEXT NOT NULL,
    symptoms TEXT,
    notes TEXT,
    status ENUM('Pending', 'Done', 'Archived', 'Rescheduled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (password: admin123)
INSERT INTO admin_users (username, password_hash, name, email) 
VALUES ('admin', '$2y$10$YourHashedPasswordHere', 'System Administrator', 'admin@healthcare.com');

-- Create some sample data for testing
INSERT INTO patients (full_name, age, gender, phone_number, appointment_date, illness_diagnosis, symptoms, status) 
VALUES 
('John Smith', 35, 'Male', '+1234567890', DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'Hypertension', 'Headaches, dizziness', 'Pending'),
('Maria Garcia', 42, 'Female', '+1234567891', DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'Diabetes Type 2', 'Increased thirst, fatigue', 'Pending'),
('Robert Johnson', 58, 'Male', '+1234567892', CURDATE(), 'Arthritis', 'Joint pain, stiffness', 'Done'),
('Sarah Wilson', 29, 'Female', '+1234567893', DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'Migraine', 'Severe headaches, nausea', 'Pending'),
('Michael Brown', 65, 'Male', '+1234567894', DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'Asthma', 'Shortness of breath, wheezing', 'Pending');
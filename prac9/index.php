<?php
// Start session to handle error messages
session_start();

// Get any error messages or form data from session
$errors = isset($_SESSION['form_errors']) ? $_SESSION['form_errors'] : [];
$formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];

// Clear session data after retrieving
unset($_SESSION['form_errors']);
unset($_SESSION['form_data']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practice 9 - Form Data Submission</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #001BB7 0%, #0046FF 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 3rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 600px;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            color: #001BB7;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #666;
            font-size: 1.1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
            font-size: 1rem;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #E9E9E9;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: white;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #0046FF;
            box-shadow: 0 0 0 3px rgba(0, 70, 255, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .btn {
            background: linear-gradient(135deg, #FF8040 0%, #0046FF 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 128, 64, 0.3);
        }

        .btn-secondary {
            background: #E9E9E9;
            color: #001BB7;
            margin-top: 1rem;
        }

        .btn-secondary:hover {
            background: #d0d0d0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .required {
            color: #FF8040;
        }

        .form-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #E9E9E9;
        }

        .nav-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1rem;
        }

        .nav-links a {
            color: #0046FF;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #FF8040;
        }

        .error-message {
            background: #ffebee;
            border: 1px solid #f44336;
            color: #c62828;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .error-message ul {
            margin: 0;
            padding-left: 1.5rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 2rem;
                margin: 1rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 2rem;
            }

            .nav-links {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-file-alt"></i> Form Data Submission</h1>
            <p>Practice 9 - PHP Form Processing with File Storage</p>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="error-message">
            <h4><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</h4>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form action="process_form.php" method="POST" id="dataForm">
            <div class="form-group">
                <label for="full_name">Full Name <span class="required">*</span></label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($formData['full_name'] ?? ''); ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name <span class="required">*</span></label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($formData['first_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name <span class="required">*</span></label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($formData['last_name'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address <span class="required">*</span></label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" min="1" max="120" value="<?php echo htmlspecialchars($formData['age'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender">
                    <option value="">Select Gender</option>
                    <option value="Male" <?php echo (isset($formData['gender']) && $formData['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo (isset($formData['gender']) && $formData['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo (isset($formData['gender']) && $formData['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                    <option value="Prefer not to say" <?php echo (isset($formData['gender']) && $formData['gender'] === 'Prefer not to say') ? 'selected' : ''; ?>>Prefer not to say</option>
                </select>
            </div>

            <div class="form-group">
                <label for="occupation">Occupation</label>
                <input type="text" id="occupation" name="occupation" value="<?php echo htmlspecialchars($formData['occupation'] ?? ''); ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($formData['city'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($formData['country'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="website">Website/Social Media</label>
                <input type="url" id="website" name="website" placeholder="https://example.com" value="<?php echo htmlspecialchars($formData['website'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="interests">Interests/Hobbies</label>
                <textarea id="interests" name="interests" placeholder="Tell us about your interests and hobbies..."><?php echo htmlspecialchars($formData['interests'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="comments">Additional Comments</label>
                <textarea id="comments" name="comments" placeholder="Any additional information you'd like to share..."><?php echo htmlspecialchars($formData['comments'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn">
                <i class="fas fa-paper-plane"></i> Submit Form Data
            </button>
        </form>

        <div class="form-footer">
            <p>All data will be securely stored and processed</p>
            <div class="nav-links">
                <a href="view_data.php"><i class="fas fa-eye"></i> View Submitted Data</a>
                <a href="download_csv.php"><i class="fas fa-download"></i> Download CSV</a>
                <a href="demo_trace.php"><i class="fas fa-list"></i> Form Submission Trace</a>
            </div>
        </div>
    </div>

    <script>
        // Client-side form validation
        document.getElementById('dataForm').addEventListener('submit', function(e) {
            const requiredFields = document.querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#FF8040';
                    isValid = false;
                } else {
                    field.style.borderColor = '#E9E9E9';
                }
            });

            // Email validation
            const email = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email.value && !emailRegex.test(email.value)) {
                email.style.borderColor = '#FF8040';
                isValid = false;
                alert('Please enter a valid email address');
                e.preventDefault();
                return;
            }

            if (!isValid) {
                alert('Please fill in all required fields');
                e.preventDefault();
            }
        });

        // Remove error styling on input
        document.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('input', function() {
                this.style.borderColor = '#E9E9E9';
            });
        });
    </script>
</body>
</html>
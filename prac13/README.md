# Practice 13 - Secure User Registration/Login System

## Overview
This project implements a comprehensive secure authentication system with advanced security features, input validation, password hashing, and SQL injection prevention as specified in the problem definition.

## Features Implemented

### 🔒 Security Features
- **Password Hashing**: Uses PHP's `password_hash()` with PASSWORD_DEFAULT algorithm
- **Input Sanitization**: All user inputs are sanitized using `htmlspecialchars()` and `trim()`
- **SQL Injection Prevention**: Uses PDO prepared statements for all database queries
- **CSRF Protection**: Implements CSRF tokens for all forms
- **Account Lockout**: Locks accounts after 5 failed login attempts for 30 minutes
- **Session Security**: Regenerates session IDs after login

### 📝 Validation (Both Client & Server Side)
- **Email Validation**: Uses PHP's `filter_var()` and JavaScript regex
- **Password Strength**: Enforces 8+ characters, uppercase, lowercase, numbers, special characters
- **Username Validation**: Alphanumeric and underscore only, minimum 3 characters
- **Real-time Validation**: JavaScript provides instant feedback on form fields

### 🎨 User Interface
- **Responsive Design**: Works on desktop and mobile devices
- **Color Palette**: Uses the specified colors:
  - `#E8E4E1` - Light beige background
  - `#F9C49A` - Light orange accent
  - `#EC823A` - Orange primary
  - `#7C3C21` - Dark brown text/borders
- **Form Toggle**: Smooth transition between login and registration forms
- **AJAX Forms**: No page reloads during form submission
- **CAPTCHA**: Simple CAPTCHA implementation for bot protection

### 🚀 AJAX Implementation
- **Vanilla JavaScript**: No jQuery dependencies
- **JSON Responses**: Clean API responses with success/error states
- **Real-time Feedback**: Instant error messages and form validation
- **Loading States**: Visual feedback during form submission

## File Structure
```
prac13/
├── config.php          # Database config, security functions, CSRF protection
├── index.php           # Main landing page with login/registration forms
├── auth.php            # AJAX authentication handler
├── dashboard.php       # User dashboard after successful login
├── profile.php         # User profile management
├── security.php        # Security settings and password change
├── logout.php          # Session cleanup and logout
├── css/
│   └── style.css       # Responsive styles with specified color palette
└── js/
    └── script.js       # Client-side validation and AJAX functionality
```

## Security Measures Implemented

### 1. Password Security ✅
- Secure hashing with `password_hash()`
- Strong password requirements enforced
- Password verification with `password_verify()`

### 2. Input Validation ✅
- Server-side validation for all inputs
- Client-side validation for better UX
- Email format validation
- Username uniqueness checks

### 3. SQL Injection Prevention ✅
- PDO prepared statements exclusively
- No dynamic SQL query construction
- Parameter binding for all user inputs

### 4. Additional Security Features ✅
- CSRF token protection
- Session management with regeneration
- Account lockout mechanism
- Login attempt tracking
- Secure logout with session cleanup

## Database Schema
The system automatically creates a `users` table with the following structure:
- `id` - Auto-increment primary key
- `username` - Unique username (VARCHAR 50)
- `email` - Unique email address (VARCHAR 100)
- `password_hash` - Hashed password (VARCHAR 255)
- `full_name` - User's full name (VARCHAR 100)
- `created_at` - Account creation timestamp
- `last_login` - Last successful login timestamp
- `login_attempts` - Failed login attempt counter
- `account_locked` - Boolean lock status
- `locked_until` - Lock expiration timestamp

## Installation & Setup

1. **Database Configuration**:
   - Update database credentials in `config.php`
   - The system will automatically create the database and tables

2. **Web Server**:
   - Place files in your web server directory (e.g., MAMP htdocs)
   - Ensure PHP 7.4+ and MySQL are available

3. **Permissions**:
   - Ensure web server has read/write access to the directory

## Usage

1. **Registration**:
   - Fill out the registration form with required information
   - Password must meet strength requirements
   - Complete CAPTCHA verification
   - Unique username and email required

2. **Login**:
   - Use username or email to login
   - Complete CAPTCHA verification
   - Account locks after 5 failed attempts

3. **Dashboard**:
   - View account information and security status
   - Access profile and security settings

4. **Profile Management**:
   - Update full name and email address
   - Username cannot be changed for security

5. **Security Settings**:
   - Change password with current password verification
   - View security status and login attempts

## Key Questions Addressed

### 1. Is password_hash() used correctly? ✅
- Uses `PASSWORD_DEFAULT` for future compatibility
- Proper verification with `password_verify()`
- No custom salt needed (handled automatically)

### 2. Are form inputs validated on both ends? ✅
- **Client-side**: JavaScript validation for immediate feedback
- **Server-side**: PHP validation for security and data integrity
- Email format validation on both sides
- Password strength requirements enforced

### 3. Are SQL injections prevented? ✅
- Exclusive use of PDO prepared statements
- All user inputs are parameter-bound
- No dynamic SQL query construction
- Database connection uses proper error handling

## Supplementary Features

### CAPTCHA Implementation ✅
- JavaScript-generated CAPTCHA for bot protection
- Refresh functionality for new CAPTCHA
- Server-side validation (basic implementation)

### Advanced Security Features
- Account lockout mechanism
- Login attempt tracking
- Session security with ID regeneration
- CSRF protection on all forms
- Secure logout functionality

## Learning Outcomes Achieved
- ✅ Implemented secure backend logic
- ✅ Applied proper validation and sanitization techniques
- ✅ Prevented common security vulnerabilities
- ✅ Created responsive, user-friendly interface
- ✅ Used AJAX for seamless user experience
- ✅ Applied modern web security best practices

## Testing
The system includes comprehensive validation and error handling. Test cases include:
- Valid and invalid registration attempts
- Login with correct/incorrect credentials
- Account lockout functionality
- CSRF token validation
- SQL injection attempts (safely handled)
- XSS attempts (properly sanitized)
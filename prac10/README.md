# Secure Login System - Practice 10

A comprehensive PHP authentication system with advanced session management, role-based access control, and enterprise-level security features.

## Features

### 🔐 Core Security Features
- **Password Hashing**: bcrypt with configurable cost factor
- **Session Management**: Secure session handling with timeout
- **SQL Injection Prevention**: Prepared statements throughout
- **Session Fixation Protection**: Automatic session regeneration
- **Brute Force Protection**: Account lockout after failed attempts
- **Remember Me**: Secure token-based persistent login
- **Role-Based Access**: Admin and user roles with different permissions

### 🛡️ Advanced Security
- **Session Timeout**: Automatic logout after 30 minutes of inactivity
- **Activity Tracking**: Comprehensive login attempt logging
- **IP Address Logging**: Track login locations for security
- **User Agent Tracking**: Detect suspicious login patterns
- **Secure Cookies**: HTTPOnly and Secure cookie settings
- **Database Cleanup**: Automatic removal of expired sessions and tokens

### 📊 Dashboard Features
- **Real-time Session Info**: Live session duration and timeout counters
- **Admin Panel**: User statistics and login attempt monitoring
- **Session Management**: View and manage active sessions
- **Activity History**: Detailed login and activity logs
- **Auto-refresh**: Keep session active with background updates

## File Structure

```
prac10/
├── index.php              # Landing page with color palette
├── config.php             # Database configuration and security functions
├── login.php              # User login with brute force protection
├── register.php           # User registration with validation
├── dashboard.php          # Protected dashboard with role-based access
├── logout.php             # Secure logout functionality
├── forgot-password.php    # Password reset (demo implementation)
└── refresh_session.php    # AJAX endpoint for session extension
```

## Color Palette

The system uses a modern color scheme:
- **Primary Blue**: `#001BB7`
- **Secondary Blue**: `#0046FF`
- **Orange Accent**: `#FF8040`
- **Light Gray**: `#E9E9E9`

## Demo Accounts

The system comes with pre-configured demo accounts:

| Username | Password | Role  | Description |
|----------|----------|-------|-------------|
| admin    | admin123 | admin | Administrator with full access |
| user     | user123  | user  | Standard user account |
| demo     | demo123  | user  | Demo user account |

## Installation

1. **Database Setup**: The system automatically creates the required database and tables on first run
2. **Configuration**: Update database credentials in `config.php` if needed
3. **Web Server**: Place files in your web server's document root
4. **Access**: Navigate to `index.php` to start using the system

## Security Configuration

Key security settings in `config.php`:

```php
define('SESSION_TIMEOUT', 1800);        // 30 minutes
define('MAX_LOGIN_ATTEMPTS', 5);        // Lockout threshold
define('LOCKOUT_DURATION', 900);        // 15 minutes lockout
define('HASH_COST', 12);               // bcrypt cost factor
define('REMEMBER_ME_DURATION', 604800); // 7 days
```

## Database Schema

### Users Table
- User credentials and profile information
- Role-based access control
- Login attempt tracking
- Account lockout management

### Sessions Table
- Active session tracking
- IP address and user agent logging
- Last activity timestamps
- Session security monitoring

### Login Attempts Table
- Comprehensive attempt logging
- Success/failure tracking
- IP and user agent recording
- Security analysis data

### Remember Tokens Table
- Secure persistent login tokens
- Token expiration management
- User association tracking

## Key Questions Addressed

✅ **Are sessions securely started/stopped?**
- Sessions use secure settings (HTTPOnly, Secure flags)
- Automatic session regeneration prevents fixation
- Proper session cleanup on logout
- Database-backed session tracking

✅ **Are users redirected after login?**
- Automatic redirection to intended pages
- Dashboard access for authenticated users
- Proper handling of deep-link redirects
- Role-based page access control

✅ **Is session persistence maintained?**
- 30-minute session timeout with activity tracking
- "Remember Me" functionality with secure tokens
- Auto-refresh to maintain active sessions
- Real-time session status monitoring

## Browser Compatibility

- Modern browsers with JavaScript enabled
- Mobile-responsive design
- Progressive enhancement for better UX
- Fallback functionality for older browsers

## Security Best Practices Implemented

1. **Input Validation**: All user inputs are validated and sanitized
2. **Output Encoding**: HTML entities are escaped to prevent XSS
3. **CSRF Protection**: Forms include proper token validation concepts
4. **Information Disclosure**: Error messages don't reveal sensitive information
5. **Password Policy**: Enforced password strength requirements
6. **Session Security**: Comprehensive session management and monitoring
7. **Database Security**: Prepared statements prevent SQL injection
8. **Logging**: Comprehensive audit trail for security analysis

## Advanced Extensions Available

- **Session Timeout**: Configurable timeout with warning notifications
- **Last Login Tracker**: Display user's last login information
- **Role-Based Access**: Admin panel with user management features
- **Activity Monitoring**: Real-time session and login tracking
- **Security Dashboard**: Administrative oversight of system security

## Testing

The system includes comprehensive error handling and logging for easy testing and debugging:

- Failed login attempt tracking
- Session timeout simulation
- Role-based access testing
- Security feature validation
- Real-time monitoring capabilities

---

**Built with PHP, MySQL, and modern web standards for maximum security and user experience.**
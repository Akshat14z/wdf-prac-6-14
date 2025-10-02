# Practice 14 - Role-Based Admin Dashboard

## Project Overview
A comprehensive role-based admin dashboard system with user management capabilities, security features, and audit logging.

## 🎨 Color Palette
- **Primary**: #D9E4DD (Soft Green)
- **Secondary**: #FBF7F0 (Warm White)
- **Accent**: #CDC9C3 (Light Gray)
- **Text**: #555555 (Dark Gray)

## ✨ Key Features

### 🔐 Role-Based Access Control
- **Super Admin**: Full system access
- **Admin**: User management and dashboard access
- **Moderator**: Limited user management
- **User**: Regular user access (no admin panel)

### 👥 User Management
- ✅ Dynamic user listing from database
- ✅ AJAX-powered status changes (Active/Inactive/Suspended)
- ✅ Role management with hierarchy validation
- ✅ User deletion with confirmation
- ✅ Search and filtering functionality
- ✅ Pagination for large datasets

### 📊 Admin Dashboard
- Real-time statistics and user counts
- Role distribution analytics
- Recent activity monitoring
- Quick action buttons
- System information display

### 🛡️ Security Features
- CSRF protection on all forms
- SQL injection prevention with prepared statements
- Password strength validation
- Session management and timeout
- Activity logging and audit trails
- Secure password hashing (bcrypt)

## 📁 File Structure
```
prac14/
├── config.php          # Database connection and core classes
├── index.php           # Main admin dashboard
├── login.php           # Admin login portal
├── users.php           # User management interface
├── profile.php         # Admin profile settings
├── logout.php          # Secure logout
├── css/
│   └── style.css       # Admin dashboard styling
└── README.md           # This file
```

## 🔧 Database Schema

### Tables Created Automatically:
1. **users** - User accounts with roles and status
2. **user_sessions** - Session management
3. **activity_log** - Admin activity tracking
4. **login_attempts** - Security monitoring
5. **admin_audit_log** - Admin-specific audit trail

## 🚀 Getting Started

### Prerequisites
- PHP 8.0+ with PDO support
- MySQL 5.7+
- Web server (Apache/Nginx)

### Installation
1. Place files in your web server directory
2. Access `login.php` in your browser
3. Database tables will be created automatically
4. Use demo admin accounts to log in

### Demo Admin Accounts
```
Username: superadmin  | Password: Super@123  | Role: Super Admin
Username: admin       | Password: Admin@123  | Role: Admin
Username: moderator   | Password: Mod@123    | Role: Moderator
```

## 📋 Practice 14 Requirements Verification

### ✅ Core Questions Addressed:
1. **"Are users listed dynamically from the DB?"**
   - YES - Users are fetched from database with real-time updates
   - AJAX loading for seamless experience
   - Search and filter functionality

2. **"Are delete/update actions working?"**
   - YES - AJAX-powered user management
   - Status updates (Active/Inactive/Suspended)
   - Role changes with validation
   - User deletion with confirmation
   - Profile updates for admins

3. **"Is access restricted to the admin?"**
   - YES - Role-based access control
   - Only admin+ roles can access admin panel
   - Different permission levels for different roles
   - Session validation on every page

### 🛡️ Security Implementation:
- **SQL Injection Prevention**: All database queries use prepared statements
- **CSRF Protection**: Tokens generated and validated on all forms
- **Password Security**: bcrypt hashing with configurable cost
- **Session Security**: Secure session management with timeouts
- **Activity Logging**: Comprehensive audit trails for all admin actions
- **Input Validation**: Server-side validation and sanitization

### 📊 Advanced Features:
- **Dashboard Analytics**: Real-time user statistics
- **Activity Monitoring**: Recent actions and system logs
- **Profile Management**: Admin profile updates with security
- **Password Strength**: Real-time password validation
- **Mobile Responsive**: Fully responsive design
- **AJAX Integration**: Smooth user experience without page reloads

## 🎯 Key Functionalities

### User Management Interface
- **Dynamic Table**: Real-time user data from database
- **Status Management**: Toggle user status with visual feedback
- **Role Assignment**: Change user roles with hierarchy validation
- **Search & Filter**: Find users by name, email, role, or status
- **Bulk Actions**: Future-ready for bulk operations
- **Responsive Design**: Works on all device sizes

### Admin Dashboard
- **Statistics Cards**: User counts, role distribution
- **Activity Feed**: Recent admin actions
- **System Info**: PHP/MySQL version, server details
- **Quick Actions**: Direct links to common tasks
- **Role-Based Navigation**: Different menus for different roles

### Security & Audit
- **Login Tracking**: Monitor failed login attempts
- **Activity Logging**: Track all admin actions
- **Session Management**: Secure session handling
- **CSRF Protection**: Prevent cross-site request forgery
- **Password Policies**: Enforce strong passwords

## 🌐 Browser Support
- Modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile responsive design
- Progressive enhancement for older browsers

## 📱 Mobile Features
- Responsive table design
- Touch-friendly interface
- Optimized for mobile admin tasks
- Collapsible navigation menu

---

**Note**: This admin dashboard demonstrates best practices for role-based access control, security implementation, and user experience design. All requirements from Practice 14 have been successfully implemented and tested.
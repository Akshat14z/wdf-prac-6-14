# Event Management CRUD System - Practice 12

A complete PHP-based Event Management System demonstrating full CRUD (Create, Read, Update, Delete) operations with MySQL database integration.

## Features

### Core CRUD Operations
- ✅ **Create**: Add new events with comprehensive details
- ✅ **Read**: View all events, individual event details, and filtered lists
- ✅ **Update**: Edit existing event information
- ✅ **Delete**: Remove events with confirmation prompts

### Event Status Management
- ✅ **Open/Closed Status**: Toggle event registration status
- ✅ **Status Filtering**: Filter events by open/closed status
- ✅ **Visual Indicators**: Color-coded status badges

### Advanced Features
- 🔍 **Search Functionality**: Search by title, description, location, or organizer
- 📊 **Dashboard Statistics**: Real-time counts and metrics
- 📱 **Responsive Design**: Mobile-friendly interface
- 🎨 **Custom Color Palette**: Cohesive design using specified colors
- ⚡ **Interactive UI**: JavaScript enhancements and smooth transitions

## Color Palette

The application uses a carefully chosen color scheme:
- **Primary**: `#E2A16F` - Warm orange for primary elements
- **Secondary**: `#FFF0DD` - Light cream for backgrounds
- **Tertiary**: `#D1D3D4` - Light gray for borders and subtle elements
- **Accent**: `#86B0BD` - Soft blue for accents and highlights

## Technology Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+ (via MAMP)
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Icons**: Font Awesome 6.0
- **Architecture**: MVC-inspired structure with separate manager classes

## File Structure

```
prac12/
├── config.php                 # Database configuration and connection
├── EventManager.php           # Event CRUD operations class
├── index.php                  # Dashboard with statistics
├── events.php                 # List all events
├── create_event.php           # Create new event form
├── edit_event.php             # Edit existing event
├── view_event.php             # View single event details
├── delete_event.php           # Delete event handler
├── search.php                 # Search and filter events
├── events_dump.sql            # Database schema and sample data
├── css/
│   └── style.css              # Main stylesheet with color palette
├── js/
│   └── script.js              # JavaScript functionality
└── README.md                  # This file
```

## Database Schema

### Events Table
```sql
CREATE TABLE events (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    location VARCHAR(200) NOT NULL,
    capacity INT(11) DEFAULT 0,
    status ENUM('open','closed') DEFAULT 'open',
    organizer VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Installation & Setup

### Prerequisites
- MAMP (or equivalent PHP/MySQL environment)
- PHP 8.0 or higher
- MySQL 8.0 or higher

### Setup Instructions

1. **Start MAMP Services**
   - Start Apache and MySQL servers
   - Ensure default ports (Apache: 80, MySQL: 8889)

2. **Import Database**
   ```bash
   # Via phpMyAdmin or command line:
   mysql -u root -p < events_dump.sql
   ```

3. **Configure Database**
   - Update `config.php` if needed (default settings work with MAMP)
   - Database will be created automatically on first run

4. **Access Application**
   - Open: `http://localhost/prac12/`
   - Dashboard loads with sample data

## Key Features Demonstration

### 1. Create Operation
- **Form Validation**: Client-side and server-side validation
- **Date Validation**: Prevents past dates
- **Capacity Limits**: Reasonable capacity constraints
- **Status Selection**: Open/closed registration status

### 2. Read Operations
- **Dashboard View**: Statistics and recent events
- **Complete List**: All events with pagination-ready structure
- **Individual View**: Detailed event information
- **Filtered Views**: Upcoming, open, and closed events

### 3. Update Operation
- **Pre-populated Forms**: Current data loaded for editing
- **Validation**: Same validation as create operation
- **Success Feedback**: Clear confirmation messages
- **Navigation**: Easy return to event details

### 4. Delete Operation
- **Confirmation Dialog**: JavaScript confirmation prompt
- **Safe Deletion**: Proper error handling
- **Feedback**: Success/error message display
- **Redirect**: Return to events list

### 5. Search & Filter
- **Multi-field Search**: Title, description, location, organizer
- **Highlighting**: Search terms highlighted in results
- **Quick Filters**: One-click status and date filters
- **Real-time UI**: Smooth search experience

## User Interface Features

### Dashboard
- **Statistics Cards**: Total, upcoming, open, and closed event counts
- **Recent Events**: Latest 5 events with quick actions
- **Quick Actions**: Direct links to main functions
- **Upcoming Preview**: Next 3 upcoming events

### Navigation
- **Consistent Menu**: Present on all pages
- **Active States**: Current page highlighted
- **Responsive**: Mobile-friendly navigation

### Forms
- **Modern Design**: Clean, intuitive form layouts
- **Validation**: Real-time validation feedback
- **Help Text**: Guidance for form completion
- **Accessibility**: Proper labels and ARIA attributes

### Data Display
- **Responsive Tables**: Mobile-friendly data presentation
- **Action Buttons**: Intuitive CRUD operation buttons
- **Status Indicators**: Visual status representation
- **Hover Effects**: Interactive feedback

## Success/Failure Messages

The application provides comprehensive feedback:
- ✅ **Success Messages**: Green alerts for successful operations
- ❌ **Error Messages**: Red alerts for failures
- ℹ️ **Info Messages**: Blue alerts for informational content
- ⚠️ **Warning Messages**: Yellow alerts for caution

## Key Questions Addressed

1. **Are adding, update, and delete functionalities correct?**
   - ✅ Yes, all CRUD operations implemented with proper validation and error handling

2. **Is UI linked with DB correctly?**
   - ✅ Yes, all data operations use proper PDO prepared statements

3. **Are success/failure messages shown?**
   - ✅ Yes, comprehensive feedback system implemented

## Advanced Features Implemented

### Supplementary Problem: Event Status (Open/Closed)
- ✅ Status field added to database schema
- ✅ Status management in all CRUD operations
- ✅ Visual status indicators with badges
- ✅ Filter events by status
- ✅ Status change functionality

### User Experience Enhancements
- 🎨 **Custom Color Palette**: Implemented as specified
- 📱 **Responsive Design**: Works on all device sizes
- ⚡ **Interactive Elements**: Hover effects, animations
- 🔍 **Advanced Search**: Multi-field search with highlighting
- 📊 **Dashboard Analytics**: Real-time statistics
- 🚀 **Performance**: Optimized queries and indexing

## Browser Support

- ✅ Chrome/Chromium (90+)
- ✅ Firefox (88+)
- ✅ Safari (14+)
- ✅ Edge (90+)

## Learning Outcomes Achieved

- ✅ **Complete CRUD Module**: All operations implemented and tested
- ✅ **PHP/MySQL Integration**: Proper database connectivity and operations
- ✅ **User Interface Design**: Modern, responsive design
- ✅ **Error Handling**: Comprehensive validation and error management
- ✅ **Security Practices**: SQL injection prevention with prepared statements

## Future Enhancements

Potential improvements for advanced implementations:
- User authentication and authorization
- Event image upload functionality  
- AJAX-based operations for better UX
- Export functionality (CSV, PDF)
- Event calendar view
- Email notifications
- Event registration system

---

**Built with ❤️ using PHP, MySQL, HTML5, CSS3, and JavaScript**  
**Practice 12 - Complete CRUD Event Management System**
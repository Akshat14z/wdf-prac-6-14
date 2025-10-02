# Student Data Management System - Practice 11

## Problem Definition Fixed ✅

This practice focuses on implementing a student data management system using PHP and MySQL, addressing the key requirements:

### Core Requirements Met:
1. **Store and retrieve student data from MySQL DB** ✅
2. **Correct SQL queries (INSERT, SELECT, DELETE)** ✅ 
3. **Normalized database schema** ✅
4. **Search by name functionality** ✅

### Key Questions Answered:
1. ✅ Are SQL queries correct? - Yes, using prepared statements
2. ✅ Are insert, select, and delete working? - All operations implemented
3. ✅ Is the DB schema normalized? - Yes, separate departments table with foreign keys

### Files Fixed/Created:

#### Core Implementation (Simplified for Learning):
- `simple_config.php` - Simplified database configuration with normalized schema
- `simple_index.php` - Main interface demonstrating all requirements
- `students.sql` - SQL dump for testing

#### Advanced Implementation (Original):
- `config.php` - Comprehensive database setup with full normalization
- `index.php` - Advanced dashboard with statistics
- `students.php` - Complete student management interface
- `add_student.php` - Form for adding new students
- `search.php` - Advanced search functionality

## Database Schema (Normalized)

### Tables:
1. **departments** - Stores department information separately
   - id (Primary Key)
   - name (Unique)
   - code (Unique)
   - created_at

2. **students** - Main student data with foreign key reference
   - id (Primary Key)
   - student_id (Unique)
   - first_name, last_name
   - email (Unique)
   - phone
   - department_id (Foreign Key → departments.id)
   - admission_date
   - created_at

### SQL Operations Demonstrated:

#### INSERT Query:
```sql
INSERT INTO students (student_id, first_name, last_name, email, phone, department_id, admission_date) 
VALUES (?, ?, ?, ?, ?, ?, ?)
```

#### SELECT Query with JOIN:
```sql
SELECT s.*, d.name as department_name, d.code as department_code 
FROM students s 
JOIN departments d ON s.department_id = d.id 
ORDER BY s.last_name, s.first_name
```

#### DELETE Query:
```sql
DELETE FROM students WHERE id = ?
```

#### Search by Name Query:
```sql
SELECT s.*, d.name as department_name 
FROM students s 
JOIN departments d ON s.department_id = d.id 
WHERE s.first_name LIKE ? OR s.last_name LIKE ?
```

## How to Use:

1. **Simple Version**: Access `simple_index.php` for basic functionality demonstration
2. **Advanced Version**: Access `index.php` for full-featured application
3. **SQL Testing**: Import `students.sql` for direct database testing

## Key Features:
- ✅ Normalized database design
- ✅ Prepared statements (SQL injection protection)
- ✅ Complete CRUD operations
- ✅ Search functionality
- ✅ Clean, responsive interface
- ✅ Error handling and validation

## Learning Outcomes:
- Created a data-driven page with proper database connectivity
- Implemented correct SQL queries for all basic operations
- Designed normalized database schema following best practices
- Added search functionality as per supplementary requirements
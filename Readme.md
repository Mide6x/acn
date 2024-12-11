# Staff Request Management System Setup Guide

## Prerequisites
- XAMPP (with PHP 7.4+ and MySQL)
- Web browser
- Git (optional)

## Installation Steps

1. Install XAMPP from https://www.apachefriends.org/

2. Clone or download this repository to your XAMPP htdocs folder:
   ```
   C:\xampp\htdocs\acnnew\
   ```

3. Create a new MySQL database named `acnnew`

4. Import the database schema from `database/acnnew.sql`

5. Configure database connection in `include/config.php`:
   ```php
   $host = "localhost";
   $username = "root"; 
   $password = "";
   $database = "acnnew";
   ```

6. Start Apache and MySQL services in XAMPP Control Panel

7. Access the application at:
   ```
   http://localhost/acnnew/
   ```

## Known Issues & To-Do

### Department Unit Lead
1. Add Station button not functional in DeptUnitLead view
2. Edit draft request functionality not working
3. Submit button saves to draft - can only submit through edit request modal

### Head of Department (HOD)
1. Submit button not functional in main view - can only submit through request details modal
2. Edit draft functionality not working

### HR
1. Save as draft button not functional
2. Edit request functionality not working

## User Roles

The system supports the following user roles: 
- Department Unit Lead
- Head of Department
- HR Staff
- Head of HR
- CFO
- CEO

To switch between roles for testing, modify the active user configuration in: /Applications/XAMPP/xamppfiles/htdocs/acnnew/include/user_config.php
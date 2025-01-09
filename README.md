# Isdhis Gym Management System

A comprehensive web-based gym management system built with PHP and MySQL, designed to streamline gym operations and enhance member experience.

## 🚀 Features

### User Management
- Secure authentication system with login and registration
- Role-based access control (Admin and Manager roles)
- Profile management with customizable profile pictures
- Session management and secure password handling

### Member Management
- Complete member profile management
- Membership status tracking
- Join date and expiry date monitoring
- Active/Inactive status management

### Trainer Management
- Trainer profiles with specializations
- Contact information management
- Availability status tracking
- Expertise categorization

### Package Management
- Flexible membership package creation
- Duration and pricing management
- Package status control
- Detailed package descriptions

## 🛠️ Technology Stack

### Backend
- **PHP 7.4+** - Server-side scripting
- **MySQL 5.7+** - Database management
- **PDO** - Database connection and security
- **Session Management** - User authentication and state management

### Frontend
- **HTML5** - Structure and content
- **CSS3** - Styling and animations
- **Bootstrap 5** - Responsive design framework
- **JavaScript** - Client-side functionality
- **Font Awesome** - Icons and visual elements

### Security Features
- Password hashing using PHP's password_hash()
- PDO prepared statements for SQL injection prevention
- Session-based authentication
- Input validation and sanitization
- CSRF protection

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP/WAMP/MAMP or any PHP development environment
- Web browser (Chrome, Firefox, Safari, Edge)

## 🔧 Installation

1. **Clone the Repository**
   ```bash
   git clone [repository-url]
   ```

2. **Database Setup**
   - Import the SQL file from `sql/create_tables.sql`
   - Database will be automatically created when accessing the application

3. **Configuration**
   - Navigate to `database.php`
   - Update database credentials if needed:
     ```php
     $host = 'localhost';
     $dbname = 'gym_mng';
     $username = 'root';
     $password = '';
     ```

4. **Server Setup**
   - Place the project in your web server's root directory
   - For XAMPP: `C:/xampp/htdocs/`
   - For WAMP: `C:/wamp/www/`

5. **Access the Application**
   - Start your local server (Apache and MySQL)
   - Open your browser and navigate to:
     ```
     http://localhost/aiproject/
     ```

## 📁 Project Structure

```
aiproject/
├── sql/
│   └── create_tables.sql
├── uploads/
│   └── profiles/
├── index.php
├── login.php
├── signup.php
├── dashboard.php
├── database.php
├── logout.php
└── README.md
```

## 🔐 Database Schema

### Users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    profile_image VARCHAR(255) DEFAULT 'profile.png',
    role ENUM('admin', 'manager') DEFAULT 'manager',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)
```

### Trainers Table
```sql
CREATE TABLE trainers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

### Packages Table
```sql
CREATE TABLE packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    duration INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

### Members Table
```sql
CREATE TABLE members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    package_id INT,
    join_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

## 🔒 Security Considerations

1. **Password Security**
   - Passwords are hashed using PHP's password_hash()
   - Minimum password requirements enforced
   - Secure password reset functionality

2. **Database Security**
   - PDO prepared statements
   - Input validation and sanitization
   - Protection against SQL injection

3. **Session Security**
   - Secure session handling
   - Session timeout management
   - Protection against session hijacking

4. **Access Control**
   - Role-based access control
   - Protected routes and resources
   - Proper authentication checks

## 🤝 Contributing

1. Fork the repository
2. Create a new branch
3. Make your changes
4. Submit a pull request

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👥 Authors

- Ahmed Abdi Hassan - Initial work and maintenance

## 📧 Support

For support, email axmedinhowalal4@gmail.com or create an issue in the repository.

# Electronic Health Record (EHR) System

A comprehensive digital health record system designed for school clinics to manage student and faculty health records, appointments, medical consultations, and inventory tracking.

## Features

- **User Authentication**
  - Secure login for students, faculty, and administrators
  - OTP verification for enhanced security
  - Password reset functionality

- **Student Management**
  - Registration and profile management
  - Medical history tracking
  - Health vitals recording (height, weight, blood pressure, temperature, heart rate)

- **Faculty Management**
  - Faculty registration and information management
  - Access to appropriate health records

- **Medical Records**
  - Detailed medical records management
  - Illness and medication tracking
  - Consultation history

- **Appointment System**
  - Online appointment scheduling
  - Calendar integration
  - Appointment tracking and management

- **Inventory Management**
  - Medical supplies tracking
  - Low stock alerts
  - Usage statistics

- **Reporting**
  - Customizable reports
  - Health statistics
  - Treatment analytics

- **QR Code Integration**
  - QR code generation for quick access to records
  - QR code scanning functionality

## Technical Stack

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Email Service**: PHPMailer
- **Containerization**: Docker

## System Requirements

- PHP 7.4 or 8.0+
- MySQL Database
- Web Server (Apache/Nginx)
- XAMPP/WAMP/MAMP for local development

## Installation

1. Clone the repository
2. Set up a local server environment (XAMPP recommended)
3. Import the database schema from the SQL file
4. Configure database connection in PHP files
5. Run the application on your local server

### Database Configuration

Update the database connection details in the PHP files:

```php
$conn = mysqli_connect("localhost", "root", "", "ehrdb");
```

### Docker Setup (Optional)

For Docker deployment:

```bash
docker-compose up -d
```

## Usage

1. Access the system through the login page
2. Authenticate with your credentials
3. Navigate the dashboard for your role-specific features
4. Manage health records, appointments, and other functionalities

## Security Features

- Password hashing
- Session management
- OTP authentication
- Input sanitization

## License

This project is proprietary software.

## Contact

For support or inquiries, please contact the system administrator. 
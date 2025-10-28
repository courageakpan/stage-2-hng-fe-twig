# TicketPro - Ticket Management System

A modern, responsive ticket management system built with PHP and Twig templating engine.

## Features

- **User Authentication**: Secure login and registration system
- **Ticket Management**: Create, view, and manage support tickets
- **Dashboard**: Real-time statistics and overview
- **Responsive Design**: Mobile-friendly interface using Tailwind CSS
- **Role-based Access**: Admin, Agent, and User roles
- **Priority Levels**: Critical, High, Medium, Low priority tickets
- **Status Tracking**: Open, In Progress, Closed, Resolved statuses

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer
- Web server (Apache/Nginx)

## Installation

### 1. Clone the repository
```bash
git clone <repository-url>
cd ticket_management_twig
```

### 2. Install dependencies
```bash
composer install
```

### 3. Configure database
Edit `config/database.php` with your database credentials:
```php
private $host = 'localhost';
private $db_name = 'ticket_management';
private $username = 'root';
private $password = '';
```

### 4. Run the setup script
```bash
php setup.php
```
This will:
- Create the database
- Create necessary tables
- Insert sample data

### 5. Configure web server
Point your web server to the project root directory.

For Apache, ensure `.htaccess` is enabled and `mod_rewrite` is loaded.

## Usage

### Access the application
Open your browser and navigate to `http://localhost/ticket_management_twig/`

### Default login credentials
- **Admin**: john@example.com / password
- **Agent**: jane@example.com / password  
- **User**: tom@example.com / password

## Project Structure

```
ticket_management_twig/
├── config/
│   └── database.php          # Database configuration
├── models/
│   ├── Ticket.php            # Ticket model
│   └── User.php              # User model
├── templates/
│   └── index.twig            # Main template
├── vendor/                   # Composer dependencies
├── composer.json             # Composer configuration
├── index.php                 # Main application entry point
├── setup.php                 # Database setup script
└── README.md                 # This file
```

## API Endpoints

The application uses URL parameters for routing:

- `?page=landing` - Landing page
- `?page=login` - Login form
- `?page=signup` - Registration form
- `?page=dashboard` - Dashboard (requires login)
- `?page=tickets` - Ticket management (requires login)
- `?page=tickets&action=create` - Create new ticket (POST)

## Database Schema

### Users Table
- `id` - Primary key
- `name` - User full name
- `email` - User email (unique)
- `password` - Hashed password
- `role` - User role (admin, agent, user)
- `created_at` - Registration timestamp

### Tickets Table
- `id` - Primary key
- `ticket_id` - Unique ticket identifier
- `subject` - Ticket subject
- `description` - Ticket description
- `priority` - Priority level
- `status` - Current status
- `assigned_to` - Assigned agent ID
- `created_by` - Creator user ID
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

### Ticket Comments Table
- `id` - Primary key
- `ticket_id` - Related ticket ID
- `user_id` - Comment author ID
- `comment` - Comment content
- `created_at` - Comment timestamp

## Security Features

- Password hashing using PHP's `password_hash()`
- Session-based authentication
- SQL injection prevention with prepared statements
- Input validation and sanitization

## Customization

### Adding New Features
1. Create new model classes in the `models/` directory
2. Add new routes in `index.php`
3. Update templates in `templates/` directory

### Styling
The application uses Tailwind CSS. You can customize the appearance by modifying the classes in `templates/index.twig`.

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check MySQL is running
   - Verify database credentials in `config/database.php`
   - Ensure user has CREATE DATABASE privileges

2. **Composer Issues**
   - Run `composer install` to install dependencies
   - Check PHP version compatibility

3. **Permission Issues**
   - Ensure web server has write permissions for the project directory
   - Check file ownership

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is open-source and available under the [MIT License](LICENSE).

## Support

For support and questions, please open an issue in the repository.
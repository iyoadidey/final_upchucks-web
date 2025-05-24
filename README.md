# Upchucks - Student Marketplace

A web-based marketplace platform for students to buy and sell products and services, specifically designed for the Technological Institute of the Philippines community.

## Features

- User authentication with TIP email verification
- Product listing and browsing
- Shopping cart functionality
- User profile management
- Secure checkout process

## Local Development Setup

1. Install XAMPP (or similar local server stack)
2. Clone this repository to your `htdocs` folder
3. Start Apache and MySQL services
4. Create a database named `upchucks_db`
5. Import the database schema (if provided)
6. Configure `backend/config.php` for local development

## Production Deployment

1. Set up a web hosting service with PHP and MySQL support
2. Configure your domain and SSL certificate
3. Set up the production database
4. Configure environment variables:
   - DB_HOST
   - DB_NAME
   - DB_USER
   - DB_PASS
5. Update `backend/config.php` to use production settings
6. Deploy files to your web server

## Security Considerations

- All database credentials are stored in environment variables
- TIP email verification ensures only authorized users can register
- Passwords are securely hashed
- Session management is implemented
- Input validation and sanitization are in place

## File Structure

```
├── backend/
│   ├── config.php
│   ├── login.php
│   ├── register.php
│   └── check_session.php
├── css/
│   └── styles.css
├── js/
│   └── script.js
├── images/
├── index.html
├── signin.html
├── website.html
└── README.md
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.
# final_upchucks-web

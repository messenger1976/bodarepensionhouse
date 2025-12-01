# Bodare Pension House

A comprehensive booking management system for a pension house built with PHP (CodeIgniter framework).

## Features

- **Booking Management**: Full booking lifecycle management
- **Room Management**: Manage room inventory and availability
- **Customer Dashboard**: Customer account and booking history
- **Admin Panel**: Comprehensive admin control panel with granular permissions
- **Payment Integration**: Booking confirmation and checkout system
- **API Endpoints**: RESTful API for integrations
- **Mobile Responsive**: Works on desktop and mobile devices
- **Progressive Web App**: PWA support with service workers

## Project Structure

```
├── admin/                    # Admin panel and API endpoints
│   ├── application/         # CodeIgniter application folder
│   ├── system/              # CodeIgniter system files
│   ├── sql/                 # Database migration scripts
│   └── img/                 # Admin assets
├── docs/                    # Documentation
├── system/                  # CodeIgniter system directory
├── video/                   # Video assets
├── index.php                # Main entry point
├── rooms.php                # Room listing page
├── booking-api.js           # Booking API client
└── ...                      # Other frontend pages and assets
```

## Requirements

- PHP 5.6+
- MySQL 5.7+
- Apache/Nginx with mod_rewrite
- Composer (for PHP dependencies)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd bodarepensionhouse
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Database Setup**
   - Create a new MySQL database
   - Import the database schema from `admin/sql/database_schema_extended.sql`
   - Run additional migration scripts as needed from `admin/sql/`

4. **Configuration**
   - Copy admin configuration files to appropriate locations
   - Update database credentials in CodeIgniter config
   - Set up proper file permissions

5. **Access the Application**
   - Customer website: `http://localhost/bodarepensionhouse/`
   - Admin panel: `http://localhost/bodarepensionhouse/admin/`

## API Documentation

See `admin/API_ENDPOINTS_SUMMARY.md` for complete API endpoint documentation.

## Documentation

- [Setup Guide](docs/SETUP_GUIDE.md)
- [Booking System README](docs/BOOKING_SYSTEM_README.md)
- [Admin Panel Complete Guide](docs/ADMIN_PANEL_COMPLETE.md)
- [Granular Permissions](docs/GRANULAR_PERMISSIONS_README.md)
- [Module Generator Guide](docs/MODULE_GENERATOR_GUIDE.md)

## Troubleshooting

For troubleshooting and common issues, see:
- [Troubleshooting Guide](docs/TROUBLESHOOTING_DASHBOARD.md)
- [Production Troubleshooting](admin/PRODUCTION_TROUBLESHOOTING.md)

## License

This project is proprietary. All rights reserved.

## Support

For support and inquiries, please contact the development team.

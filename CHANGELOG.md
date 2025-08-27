# Changelog

All notable changes to Next Gold will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Complete Laravel 11 application structure
- PostgreSQL database with optimized schema
- Redis caching and queue system
- User authentication with username/email login
- Role-based access control (Admin, Operator, Viewer)
- Gold price monitoring with configurable drivers
- Document management with PDF generation
- Inventory management with categories and materials
- Client management system
- Archive functionality
- Comprehensive settings management
- Setup wizard for first-time installation
- Backup and restore with HMAC signing
- System monitoring and health checks
- Responsive UI with TailwindCSS and Alpine.js
- Italian localization support
- Production deployment scripts for Ubuntu 24.04
- Quality assurance tools (Pint, PHPStan, PHPUnit)
- Complete documentation

### Technical Features
- Real-time gold price updates
- Document numbering with concurrency handling
- Status-based inventory flow
- Audit logging for all actions
- API rate limiting and security headers
- Queue processing for background tasks
- Database optimization with proper indexing
- Service layer architecture
- Repository pattern implementation
- Comprehensive error handling
- Logging and monitoring integration

## [1.0.0] - 2024-01-01

### Added
- Initial release of Next Gold management system
- Basic user authentication
- Core dashboard functionality
- Database setup and migrations

### Security
- Initial security implementation
- Basic input validation
- CSRF protection

---

## Types of Changes
- `Added` for new features
- `Changed` for changes in existing functionality
- `Deprecated` for soon-to-be removed features
- `Removed` for now removed features
- `Fixed` for any bug fixes
- `Security` in case of vulnerabilities

## Version Format
This project uses [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Given a version number MAJOR.MINOR.PATCH, increment the:
- MAJOR version when you make incompatible API changes
- MINOR version when you add functionality in a backwards compatible manner
- PATCH version when you make backwards compatible bug fixes

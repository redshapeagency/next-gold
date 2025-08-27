# Contributing to Next Gold

Thank you for your interest in contributing to Next Gold! We welcome contributions from the community.

## How to Contribute

### 1. Fork the Repository
Fork the repository on GitHub and clone your fork locally.

### 2. Create a Feature Branch
```bash
git checkout -b feature/your-feature-name
```

### 3. Make Your Changes
- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation as needed
- Ensure all tests pass

### 4. Commit Your Changes
```bash
git commit -m "Add your descriptive commit message"
```

### 5. Push and Create Pull Request
```bash
git push origin feature/your-feature-name
```
Then create a pull request on GitHub.

## Development Setup

### Prerequisites
- PHP 8.4
- Composer
- Node.js 20
- PostgreSQL 16
- Redis 7

### Installation
```bash
# Clone the repository
git clone https://github.com/your-username/next-gold.git
cd next-gold

# Install dependencies
composer install
npm install

# Set up environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate
php artisan db:seed

# Build assets
npm run build
```

## Code Standards

### PHP
- Follow PSR-12 coding standards
- Use type hints where possible
- Write descriptive variable and method names
- Add PHPDoc comments for public methods

### JavaScript
- Use ES6+ features
- Follow the existing code style
- Add comments for complex logic

### Testing
- Write unit tests for new features
- Write feature tests for user-facing functionality
- Ensure all tests pass before submitting

## Reporting Issues

When reporting issues, please include:
- A clear description of the issue
- Steps to reproduce
- Expected behavior
- Actual behavior
- Environment information (PHP version, OS, etc.)

## License

By contributing to Next Gold, you agree that your contributions will be licensed under the MIT License.

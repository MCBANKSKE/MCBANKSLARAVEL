# MCBANKS Laravel Developer Setup Guide

## Overview

This comprehensive guide will help you set up the MCBANKS Laravel development environment from scratch. Whether you're a new developer joining the team or setting up a fresh machine, this guide covers everything you need to get started.

## Prerequisites

### System Requirements

- **Operating System**: Windows 10+, macOS 10.15+, or Ubuntu 18.04+
- **PHP**: 8.2 or higher
- **Composer**: 2.0 or higher
- **Node.js**: 18.0 or higher
- **npm**: 8.0 or higher
- **Git**: 2.30 or higher
- **Database**: MySQL 8.0+ or MariaDB 10.3+

### Required Software

#### 1. PHP Installation

**Windows:**
```bash
# Using Chocolatey
choco install php

# Or download from https://www.php.net/downloads.php
# Add PHP to your PATH
```

**macOS:**
```bash
# Using Homebrew
brew install php@8.2

# Add to PATH
echo 'export PATH="/usr/local/opt/php@8.2/bin:$PATH"' >> ~/.zshrc
```

**Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath
```

#### 2. Composer Installation

```bash
# Windows
choco install composer

# macOS
brew install composer

# Ubuntu
sudo apt install composer

# Or download from https://getcomposer.org/download/
```

#### 3. Node.js Installation

```bash
# Windows
choco install nodejs

# macOS
brew install node

# Ubuntu
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

#### 4. Database Setup

**MySQL (Windows):**
```bash
# Using Chocolatey
choco install mysql

# Or download from https://dev.mysql.com/downloads/mysql/
```

**MySQL (macOS):**
```bash
brew install mysql
brew services start mysql
```

**MySQL (Ubuntu):**
```bash
sudo apt install mysql-server
sudo systemctl start mysql
sudo systemctl enable mysql
```

**Create Database:**
```sql
CREATE DATABASE mcbankslaravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mcbanks'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON mcbankslaravel.* TO 'mcbanks'@'localhost';
FLUSH PRIVILEGES;
```

#### 5. Git Configuration

```bash
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

## Project Setup

### 1. Clone the Repository

```bash
# Clone the repository
git clone https://github.com/MCBANKSKE/MCBANKSLARAVEL.git

# Navigate to project directory
cd MCBANKSLARAVEL
```

### 2. Install Dependencies

#### PHP Dependencies

```bash
# Install Composer dependencies
composer install

# If you encounter memory issues, use:
composer install --memory-limit=2G
```

#### Node.js Dependencies

```bash
# Install npm packages
npm install
```

### 3. Environment Configuration

#### Copy Environment File

```bash
# Copy example environment file
cp .env.example .env
```

#### Configure Environment Variables

Edit `.env` file with your local settings:

```env
# Application
APP_NAME="MCBANKS LARAVEL"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mcbankslaravel
DB_USERNAME=mcbanks
DB_PASSWORD=your_password

# Cache & Session
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Mail
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="${APP_NAME}@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Social Authentication (optional for development)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
GITHUB_REDIRECT_URI="${APP_URL}/auth/github/callback"

# Admin User (for development)
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=password
ADMIN_NAME="Admin User"
```

#### Generate Application Key

```bash
php artisan key:generate
```

### 4. Database Setup

#### Run Migrations

```bash
# Run all migrations
php artisan migrate

# If you want to reset and re-migrate:
php artisan migrate:fresh
```

#### Seed Database

```bash
# Seed with sample data
php artisan db:seed

# Or run specific seeders
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=CountriesTableSeeder
php artisan db:seed --class=StatesTableSeeder
php artisan db:seed --class=CitiesTableChunkOneSeeder
php artisan db:seed --class=CitiesTableChunkTwoSeeder
php artisan db:seed --class=CitiesTableChunkThreeSeeder
php artisan db:seed --class=CitiesTableChunkFourSeeder
php artisan db:seed --class=CitiesTableChunkFiveSeeder
php artisan db:seed --class=CountySeeder
php artisan db:seed --class=SubCountySeeder
```

### 5. Frontend Assets

#### Build Assets

```bash
# Build for production
npm run build

# Or watch for development
npm run dev
```

## Development Workflow

### 1. Start Development Server

#### Laravel Development Server

```bash
# Start Laravel server
php artisan serve

# Or specify host and port
php artisan serve --host=127.0.0.1 --port=8000
```

#### Queue Worker (for background jobs)

```bash
# Start queue worker
php artisan queue:work

# Or listen for new jobs
php artisan queue:listen
```

#### Watch Assets (in separate terminal)

```bash
# Watch for asset changes
npm run dev
```

#### Log Viewer (optional, in separate terminal)

```bash
# View real-time logs
php artisan pail
```

### 2. Using the Automated Setup Script

The project includes a composer script for automated setup:

```bash
# Run complete setup
composer run setup
```

This script will:
- Install PHP dependencies
- Create `.env` file from example
- Generate application key
- Run database migrations
- Seed database
- Install Node.js dependencies
- Build frontend assets

### 3. Development Commands

#### Quick Development Setup

```bash
# Start all services concurrently
composer run dev
```

This command runs:
- Laravel development server
- Queue worker
- Log viewer
- Vite frontend build

#### Testing

```bash
# Run all tests
composer run test

# Or manually
php artisan test

# Run specific test
php artisan test --filter UserTest

# Generate code coverage report
php artisan test --coverage
```

#### Code Quality

```bash
# Format code with Laravel Pint
vendor/bin/pint

# Check for style issues
vendor/bin/pint --dry-run
```

#### Database Operations

```bash
# Create new migration
php artisan make:migration create_new_table

# Create new model
php artisan make:model NewModel

# Create new controller
php artisan make:controller NewController

# Create new Livewire component
php artisan make:livewire new-component

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

## IDE Configuration

### 1. VS Code Setup

#### Recommended Extensions

Install these VS Code extensions for optimal development:

```json
{
  "recommendations": [
    "bmewburn.vscode-intelephense-client",
    "onecentlin.laravel-blade",
    "ambar.laravel-http-goto",
    "ryannaddy.laravel-artisan",
    "formulahendry.auto-rename-tag",
    "bradlc.vscode-tailwindcss",
    "esbenp.prettier-vscode",
    "ms-vscode.vscode-json",
    "redhat.vscode-yaml"
  ]
}
```

#### VS Code Settings

Create `.vscode/settings.json`:

```json
{
  "php.executablePath": "/usr/local/bin/php",
  "intelephense.environment.phpVersion": "8.2",
  "intelephense.files.maxSize": 5000000,
  "files.associations": {
    "*.blade.php": "blade"
  },
  "emmet.includeLanguages": {
    "blade": "html"
  },
  "tailwindCSS.includeLanguages": {
    "blade": "html",
    "php": "html"
  },
  "editor.formatOnSave": true,
  "editor.defaultFormatter": "esbenp.prettier-vscode",
  "[php]": {
    "editor.defaultFormatter": "bmewburn.vscode-intelephense-client"
  }
}
```

#### VS Code Tasks

Create `.vscode/tasks.json`:

```json
{
  "version": "2.0.0",
  "tasks": [
    {
      "label": "Serve Laravel",
      "type": "shell",
      "command": "php",
      "args": ["artisan", "serve"],
      "group": "build",
      "presentation": {
        "reveal": "always",
        "panel": "new"
      }
    },
    {
      "label": "Run Tests",
      "type": "shell",
      "command": "php",
      "args": ["artisan", "test"],
      "group": "test"
    },
    {
      "label": "NPM Dev",
      "type": "shell",
      "command": "npm",
      "args": ["run", "dev"],
      "group": "build"
    }
  ]
}
```

### 2. PhpStorm Setup

#### Laravel Plugin

1. Go to `File > Settings > Plugins`
2. Install `Laravel Plugin` by Evgenii Frolov
3. Enable the plugin and configure your project path

#### Code Style

1. Go to `File > Settings > Editor > Code Style > PHP`
2. Import the project's `.editorconfig` settings
3. Configure PSR-12 as the code style standard

#### Database Integration

1. Go to `File > Settings > Database`
2. Add your MySQL database connection
3. Configure the database to match your `.env` settings

## Development Best Practices

### 1. Git Workflow

#### Branch Strategy

```bash
# Create feature branch
git checkout -b feature/new-feature

# Commit changes
git add .
git commit -m "feat: add new feature description"

# Push to remote
git push origin feature/new-feature

# Create pull request
# (through GitHub/GitLab interface)
```

#### Commit Message Convention

Follow conventional commits:

```
feat: add user avatar upload functionality
fix: resolve login validation issue
docs: update API documentation
style: format code with Pint
refactor: extract user service class
test: add unit tests for profile model
chore: update dependencies
```

### 2. Code Organization

#### File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   └── Profile/
│   ├── Middleware/
│   └── Requests/
├── Models/
├── Livewire/
│   ├── Auth/
│   └── Profile/
├── Services/
├── Rules/
└── Notifications/
```

#### Naming Conventions

- **Controllers**: `UserController`, `ProfileController`
- **Models**: `User`, `UserProfile`
- **Livewire Components**: `UserProfile`, `AvatarUpload`
- **Services**: `UserService`, `AvatarService`
- **Requests**: `UpdateProfileRequest`, `UploadAvatarRequest`

### 3. Testing Strategy

#### Test Structure

```
tests/
├── Feature/
│   ├── Auth/
│   │   ├── LoginTest.php
│   │   └── RegisterTest.php
│   ├── Profile/
│   │   ├── ProfileManagementTest.php
│   │   └── AvatarUploadTest.php
│   └── Api/
│       ├── UserApiTest.php
│       └── ProfileApiTest.php
├── Unit/
│   ├── Models/
│   │   ├── UserTest.php
│   │   └── ProfileTest.php
│   └── Services/
│       ├── UserServiceTest.php
│       └── AvatarServiceTest.php
└── TestCase.php
```

#### Writing Tests

```php
<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123'
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
```

### 4. Debugging

#### Debugging Tools

**Laravel Debugbar:**
```bash
composer require --dev barryvdh/laravel-debugbar
```

**Clockwork:**
```bash
composer require --dev itsgoingd/clockwork
```

#### Debugging Techniques

```php
// Dump and die
dd($variable);

// Dump without dying
dump($variable);

// Log to file
Log::info('Debug message', ['data' => $variable]);

// Debug queries
DB::enableQueryLog();
// ... run queries
dd(DB::getQueryLog());
```

### 5. Performance Optimization

#### Database Optimization

```php
// Use eager loading
$users = User::with('profile', 'avatar')->get();

// Use specific columns
$users = User::select('id', 'name', 'email')->get();

// Use query scopes
class User extends Model
{
    public function scopeWithCompleteProfile($query)
    {
        return $query->whereHas('profile', function ($q) {
            $q->where('completion_percentage', '>=', 80);
        });
    }
}
```

#### Frontend Optimization

```javascript
// Lazy load components
const ProfileEditor = defineAsyncComponent(() => import('./ProfileEditor.vue'));

// Debounce search
const debouncedSearch = debounce(async (query) => {
    await searchUsers(query);
}, 300);
```

## Common Issues & Solutions

### 1. Composer Issues

#### Memory Limit Error

```bash
# Increase memory limit
php -d memory_limit=2G /usr/local/bin/composer install

# Or permanently in php.ini
memory_limit = 2G
```

#### Permission Issues

```bash
# Clear composer cache
composer clear-cache

# Reinstall dependencies
composer install --no-scripts
composer run-script post-install-cmd
```

### 2. Database Issues

#### Connection Failed

```bash
# Check MySQL service
sudo systemctl status mysql

# Restart MySQL
sudo systemctl restart mysql

# Check credentials
mysql -u username -p
```

#### Migration Errors

```bash
# Reset migrations
php artisan migrate:fresh

# Run specific migration
php artisan migrate --path=database/migrations/2024_01_01_000000_create_users_table.php

# Check migration status
php artisan migrate:status
```

### 3. Node.js Issues

#### npm Install Fails

```bash
# Clear npm cache
npm cache clean --force

# Delete node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

#### Build Errors

```bash
# Clear Vite cache
npm run build -- --force

# Check Node version
node --version  # Should be 18+
```

### 4. Permission Issues

#### Storage Permissions

```bash
# Linux/macOS
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Windows (in PowerShell)
icacls storage /grant "IIS_IUSRS:(OI)(CI)F"
icacls bootstrap/cache /grant "IIS_IUSRS:(OI)(CI)F"
```

#### Cache Permissions

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Regenerate cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Development Tools

### 1. Local Development Environment

#### Laravel Sail (Docker)

```bash
# Install Sail
composer require laravel/sail --dev

# Start containers
php artisan sail up

# Run commands in container
php artisan sail php artisan migrate
php artisan sail npm install
```

#### Laravel Valet (macOS)

```bash
# Install Valet
composer global require laravel/valet
valet install

# Link project
valet link mcbankslaravel
```

#### Laravel Herd (macOS)

```bash
# Install Herd
# Download from https://herd.laravel.com/

# Configure site
herd link mcbankslaravel
```

### 2. Database Tools

#### phpMyAdmin

```bash
# Install via Composer
composer create-project phpmyadmin/phpmyadmin

# Or use Docker
docker run --name phpmyadmin -d -p 8080:80 phpmyadmin/phpmyadmin
```

#### TablePlus (GUI)

- Download from https://tableplus.com/
- Configure MySQL connection with your `.env` credentials

#### DBeaver (Free)

- Download from https://dbeaver.io/
- Cross-platform database tool

### 3. API Testing

#### Postman

Import the API collection from `docs/postman-collection.json` (if available).

#### Insomnia

Alternative to Postman with modern interface.

#### HTTPie (CLI)

```bash
# Install
pip install httpie

# Test API
http GET localhost:8000/api/health
http POST localhost:8000/api/auth/login email=user@example.com password=password123
```

## Contributing

### 1. Code Review Process

1. Create feature branch from `main`
2. Make changes with proper commit messages
3. Run tests and ensure they pass
4. Submit pull request with description
5. Address review feedback
6. Merge after approval

### 2. Coding Standards

- Follow PSR-12 coding standard
- Use Laravel conventions
- Write tests for new features
- Update documentation
- Keep code DRY and readable

### 3. Release Process

1. Update version in `composer.json`
2. Update `CHANGELOG.md`
3. Create release tag
4. Deploy to production
5. Monitor for issues

## Support

### Getting Help

- **Documentation**: Check this guide and `docs/API.md`
- **Issues**: Create GitHub issue with detailed description
- **Discussions**: Use GitHub Discussions for questions
- **Email**: dev-team@your-domain.com

### Community

- **Slack**: Join our developer Slack workspace
- **Discord**: Join our Discord server
- **Twitter**: Follow @MCBANKS for updates

---

Happy coding! 🚀

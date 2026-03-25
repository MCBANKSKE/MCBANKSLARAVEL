# MCBANKS Laravel

A Laravel starter template with role-based authentication, Livewire components, and a modern UI. Perfect for building web applications with user management and permission systems.

---

## 🚀 Features

* **Laravel 12** - Latest Laravel framework
* **Livewire 4.1** - Dynamic components without writing JavaScript
* **Spatie Laravel Permission** - Role and permission system
* **Role-Based Authentication** - Admin, Host, Guest roles with permissions
* **Email Verification** - Required for secure account access
* **Multi-Step Registration** - Wizard-style form with validation
* **Modern UI** - Tailwind CSS, Glass Morphism, Gradients
* **Password Strength Indicator**
* **Responsive Design**
* **Geographical Data** - Complete countries, states, cities with currencies
* **Kenyan Administrative Data** - All 47 counties with constituencies and wards
* **User Profiles** - Complete profile management system
* **Avatar Upload** - Image processing with thumbnails and cropping
* **Privacy Settings** - Granular privacy controls
* **Profile Completion** - Progress tracking and indicators
* **Social Authentication** - OAuth login with Google, GitHub
* **Account Linking** - Connect multiple social accounts
* **Account Merging** - Smart conflict resolution

### 🔒 Security Features
* **Two-Factor Authentication** - TOTP support with recovery codes
* **Granular Rate Limiting** - Endpoint-specific rate limiting rules
* **User Activity Audit Logging** - Comprehensive security monitoring
* **IP Blacklisting** - Automatic blocking of suspicious IPs
* **Suspicious Activity Detection** - AI-driven anomaly detection
* **Security Event Tracking** - Real-time security monitoring

### 📚 Documentation
* **Comprehensive API Documentation** - Complete REST API reference
* **Developer Setup Guide** - Step-by-step development instructions
* **Production Deployment Guide** - Multiple deployment strategies
* **Security Best Practices** - Enterprise-grade security guidelines

## 📦 Installation

### Quick Start

```bash
composer create-project mcbankske/mcbankslaravel my-project
cd my-project
composer run setup
```

The setup script automatically:
- ✅ Installs all PHP dependencies with optimized autoloader
- ✅ Creates `.env` file from example
- ✅ Generates application key
- ✅ Runs database migrations
- ✅ Seeds database with geographical data, profiles, and sample social accounts
- ✅ Installs Node.js dependencies
- ✅ Builds frontend assets

### Manual Setup

1. **Clone the repository**

```bash
git clone https://github.com/MCBANKSKE/MCBANKSLARAVEL.git
cd mcbankslaravel
```

2. **Install dependencies**

```bash
composer install
npm install
```

3. **Environment setup**

```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure database**

* Edit `.env` with your database credentials

5. **Run migrations**

```bash
php artisan migrate
```

6. **Build assets**

```bash
npm run build
```

7. **Start development server**

```bash
php artisan serve
```

## 🎯 Quick Setup Script

Use the built-in composer script for automated setup:

```bash
composer run setup
```

This script will:
* Install PHP dependencies (`composer install`)
* Copy `.env.example` to `.env` if it doesn't exist
* Generate application key (`php artisan key:generate`)
* Run database migrations (`php artisan migrate --force`)
* Install Node.js dependencies (`npm install`)
* Build frontend assets (`npm run build`)

## 🔧 Development

**Development server with all services:**

```bash
composer run dev
```

This command runs multiple services concurrently:
- **Laravel development server** (PHP artisan serve)
- **Queue worker** (php artisan queue:listen)
- **Log viewer** (php artisan pail)
- **Vite frontend build** (npm run dev)

**Run tests:**

```bash
composer run test
# or
php artisan test
```

The test command clears configuration cache before running tests for consistent results.

## � Geographical Data Included

This package comes with comprehensive geographical data that's automatically seeded during installation:

### 🌐 International Data
- **Countries** - 250+ countries with complete information:
  - Name, ISO codes (ISO2, ISO3)
  - Capital cities, currencies, currency symbols
  - Phone codes, top-level domains
  - Regions, subregions, timezones
  - Latitude/longitude coordinates
  - Emoji flags and WikiData IDs

- **States/Provinces** - 5,000+ states and provinces worldwide
  - Linked to countries with proper relationships
  - ISO codes, FIPS codes, coordinates

- **Cities** - 150,000+ cities globally
  - Linked to states and countries
  - Geographic coordinates
  - Country and state codes

### 🇰🇪 Kenyan Administrative Data
- **Counties** - All 47 Kenyan counties
  - Complete county names and relationships

- **Sub-Counties** - Comprehensive administrative structure:
  - **Constituencies** - 290+ constituencies across all counties
  - **Wards** - 1,450+ wards nationwide
  - Proper relationships between counties, constituencies, and wards

### 💰 Currency Information
- Integrated currency data for all countries
- Currency symbols and codes included
- Perfect for financial applications

### 📊 Database Schema
```sql
countries          # 250+ countries with full details
states             # 5,000+ states/provinces
cities             # 150,000+ cities worldwide
counties           # 47 Kenyan counties
sub_counties       # Kenyan constituencies and wards
```

### 🔧 Usage Examples

```php
// Get all countries
$countries = Country::all();

// Get states for a specific country
$states = State::where('country_id', 1)->get();

// Get cities in a state
$cities = City::where('state_id', 1)->get();

// Get Kenyan counties
$counties = County::all();

// Get constituencies in a county
$constituencies = SubCounty::getUniqueConstituencies(1);

// Get wards in a constituency
$wards = SubCounty::getWardsByConstituency(1, 'changamwe');

// Get all wards in Kenya (filtered, no duplicates)
$allWards = SubCounty::getAllUniqueWards();
```

## ��️ Authentication System

### Registration (`RegistrationForm`)

* Multi-step wizard
* Optional role selection (default `member`)
* Conditional validation for profile fields
* Role-specific profile creation:

  * Member → `Member` and optionally `MemberAccount` 
  * Customer → `Customer` profile
  * Manager → `Manager` profile
  * Admin → only role assignment
* Email verification for members
* Auto-login on successful registration

**Blade integration:**

```blade
<livewire:auth.registration-form />
```

---

### Login (`LoginForm`)

* Email/password login
* Remember me support
* Role-based redirection:

  * Admin → `/admin` 
  * Member → `/member` (must verify email)
  * Customer/Manager → `/` (default)
* Error handling and validation

**Blade integration:**

```blade
<livewire:auth.login-form />
```

---

## 🛠️ Role Setup (Critical)

### 1. Create Roles Before Registering Users

Use the provided Artisan command to create roles:

```bash
# Create default roles
php artisan role:create admin
php artisan role:create host
php artisan role:create guest

# Create custom roles
php artisan role:create moderator
php artisan role:create editor
php artisan role:create subscriber
```

**Command Features:**
- **Duplicate Prevention**: Won't create roles that already exist
- **Case Normalization**: Automatically converts to lowercase
- **Error Handling**: Clear success/error messages
- **Validation**: Ensures role name is valid

This uses the `CreateRole` console command in `app/Console/Commands/CreateRole.php`.

### 2. Super Admin Feature

* `is_superadmin` boolean field on `users` table
* Overrides role-based permissions (not a Spatie role)
* Can bypass all role restrictions
* Set this field manually in the database or via seeder

### 3. Assign Roles Manually

```php
$user = User::find(1);
$user->assignRole('admin'); // Assign admin role
```

### 4. Adding New Roles

* Create role via the Artisan command: `php artisan role:create new_role`
* Update `LoginForm.php` redirection logic to handle the new role:

```php
if ($user->hasRole('new_role')) {
    return '/new-dashboard';
}
```

### 5. Role-Based Redirection Logic

The login system redirects users based on their roles:
- **Admin** → `/admin`
- **Host** → `/host`
- **Guest** → `/guest`
- **Fallback** → `/`

---

## 🔒 Two-Factor Authentication

### Overview
Enterprise-grade two-factor authentication system using TOTP (Time-based One-Time Password) with comprehensive recovery options.

### Features

#### TOTP Authentication
- **Google Authenticator Support** - Compatible with any TOTP app
- **QR Code Setup** - Easy setup with QR code scanning
- **Manual Key Entry** - Backup option for QR code issues
- **Secure Key Generation** - Cryptographically secure secret keys

#### Recovery System
- **8 Recovery Codes** - Backup codes for account recovery
- **Single-Use Codes** - Each code can only be used once
- **Code Regeneration** - Generate new recovery codes anytime
- **Downloadable Codes** - Export codes for secure storage

#### Security Features
- **Rate Limiting** - 5 attempts per 15 minutes
- **Session Management** - Secure 2FA verification tracking
- **Challenge Middleware** - Automatic 2FA verification for protected routes
- **Email Notifications** - Alerts for 2FA enable/disable and recovery code usage

#### User Interface
- **Livewire Components** - Modern reactive UI components
- **Setup Wizard** - Step-by-step 2FA setup process
- **Status Indicators** - Clear 2FA status display
- **Recovery Management** - Track remaining recovery codes

### Configuration

#### Environment Variables
```env
# Two-Factor Authentication (optional, defaults work fine)
GOOGLE2FA_SECRET=
GOOGLE2FA_QRCODE_SIZE=200
```

#### Dependencies
```bash
composer update
# Required packages:
# - pragmarx/google2fa-laravel
# - simplesoftwareio/simple-qrcode
```

### Usage Examples

#### Enable Two-Factor Authentication
```php
use App\Services\TwoFactorService;

$user = Auth::user();
$twoFactorService = app(TwoFactorService::class);

// Generate secret key and QR code
$secret = $twoFactorService->generateSecretKey($user);
$qrCode = $twoFactorService->generateQrCode($user, $secret);

// Enable 2FA after user verification
$twoFactorService->enable($user, $verificationCode);
```

#### Verify Two-Factor Authentication
```php
// Check if user has 2FA enabled
if ($user->hasTwoFactorEnabled()) {
    // Verify user code
    if ($user->verifyTwoFactorCode($request->code)) {
        // Authentication successful
        $user->twoFactorAuthentication->updateLastUsed();
    }
}
```

#### Disable Two-Factor Authentication
```php
$user->disableTwoFactor();
```

### Blade Integration
```blade
<!-- Two-Factor Authentication Component -->
<livewire:profile.two-factor-authentication />
```

### Routes
```php
// Two-Factor Authentication
Route::middleware(['auth', 'throttle:5,1'])->group(function () {
    Route::get('/2fa/challenge', [TwoFactorController::class, 'showChallenge'])->name('2fa.challenge');
    Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify');
    Route::get('/2fa/recovery', [TwoFactorController::class, 'showRecoveryForm'])->name('2fa.recovery');
    Route::post('/2fa/recovery/verify', [TwoFactorController::class, 'verifyRecovery'])->name('2fa.recovery.verify');
    Route::post('/2fa/logout', [TwoFactorController::class, 'logout'])->name('2fa.logout');
});
```

### Security Considerations
- Store recovery codes securely offline
- Enable 2FA on all admin accounts
- Monitor 2FA failed attempts in audit logs
- Regularly regenerate recovery codes

---

## ⚡ Rate Limiting System

### Overview
Advanced rate limiting system with endpoint-specific rules, IP blacklisting, and suspicious activity detection.

### Features

#### Granular Rate Limits
- **Authentication Endpoints** - 5 attempts per 15 minutes
- **Registration** - 3 attempts per hour
- **Password Reset** - 3 attempts per hour
- **Two-Factor** - 5 attempts per 15 minutes
- **API Requests** - 1000/hour (authenticated), 100/hour (anonymous)
- **Profile Updates** - 10 attempts per hour
- **Avatar Uploads** - 5 attempts per hour

#### Security Protection
- **IP Blacklisting** - Automatic blocking of malicious IPs
- **Suspicious Activity Detection** - AI-driven anomaly detection
- **Rate Limit Headers** - Standard HTTP rate limit headers
- **User-Based Limits** - Different limits for authenticated vs anonymous users

#### Monitoring & Statistics
- **Real-time Statistics** - Rate limiting analytics
- **Most Common Violations** - Track frequent abusers
- **IP Activity Tracking** - Monitor IP patterns
- **Performance Optimized** - Redis-based caching for performance

### Configuration

#### Middleware Registration
```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        // ... existing middleware
        \App\Http\Middleware\LogUserActivity::class,
    ],
    'api' => [
        // ... existing middleware
        \App\Http\Middleware\RateLimitApi::class,
    ],
];

protected $middlewareAliases = [
    'rate.limit.auth' => \App\Http\Middleware\RateLimitAuth::class,
    'rate.limit.profile' => \App\Http\Middleware\RateLimitProfile::class,
];
```

#### Environment Variables
```env
# Rate Limiting (optional, defaults work fine)
RATE_LIMIT_CACHE_DRIVER=redis
RATE_LIMIT_CACHE_PREFIX=rate_limit:
```

### Usage Examples

#### Check Rate Limits
```php
use App\Services\RateLimitingService;

$rateLimiting = app(RateLimitingService::class);

// Check authentication rate limit
if ($rateLimiting->checkAuthRateLimit($request)) {
    abort(429, 'Too many attempts');
}

// Check API rate limit
if ($rateLimiting->checkApiRateLimit($request)) {
    abort(429, 'API rate limit exceeded');
}
```

#### Get Rate Limit Status
```php
// Get rate limit information for current user
$status = $rateLimiting->getRateLimitStatus($request, 'auth', 5);

// Output rate limit headers
$headers = $rateLimiting->getRateLimitHeaders($request, 'api', 1000, 60);
foreach ($headers as $key => $value) {
    $response->headers->set($key, $value);
}
```

#### IP Blacklisting
```php
// Blacklist an IP address
$rateLimiting->blacklistIp('192.168.1.100', 60); // 60 minutes

// Check if IP is blacklisted
if ($rateLimiting->isIpBlacklisted($request->ip())) {
    abort(429, 'Your IP has been blocked');
}

// Remove IP from blacklist
$rateLimiting->unblacklistIp('192.168.1.100');
```

#### Log Suspicious Activity
```php
// Log suspicious activity for monitoring
$rateLimiting->logSuspiciousActivity($request, 'multiple_failed_logins');

// Check for suspicious activity patterns
if ($rateLimiting->checkSuspiciousActivityRateLimit($request)) {
    // Take action against suspicious user
}
```

### Rate Limiting Rules

#### Authentication Endpoints
```php
// Login attempts
RateLimitAuth: 5 attempts per 15 minutes

// Registration attempts  
RateLimitAuth: 3 attempts per hour

// Password reset requests
RateLimitAuth: 3 attempts per hour
```

#### API Endpoints
```php
// Authenticated users
RateLimitApi: 1000 requests per hour

// Anonymous users
RateLimitApi: 100 requests per hour

// Specific endpoints
RateLimitApi: Custom limits per endpoint
```

#### Profile Management
```php
// Profile updates
RateLimitProfile: 10 attempts per hour

// Avatar uploads
RateLimitProfile: 5 attempts per hour

// Profile views (more lenient)
RateLimitProfile: 200 attempts per hour
```

### Response Headers
All rate limited responses include:
- `X-RateLimit-Limit` - Maximum requests allowed
- `X-RateLimit-Remaining` - Requests remaining
- `X-RateLimit-Reset` - Time when limit resets

### Security Benefits
- **Prevents Brute Force** - Limits login attempts
- **API Protection** - Prevents API abuse
- **Resource Conservation** - Protects server resources
- **DDoS Mitigation** - Helps mitigate denial of service attacks

---

## � User Activity Audit Logging

### Overview
Comprehensive audit logging system that tracks all user activities, security events, and system changes for compliance and security monitoring.

### Features

#### Comprehensive Logging
- **Authentication Events** - Login, logout, failed attempts
- **Profile Changes** - Profile updates, avatar uploads
- **Two-Factor Events** - 2FA enable/disable, verification attempts
- **Social Authentication** - Social account connections/disconnections
- **Security Events** - Suspicious activities, rate limiting violations
- **API Access** - API requests, errors, unauthorized access

#### Data Tracking
- **Before/After Values** - Track data changes
- **Metadata Capture** - IP addresses, user agents, request details
- **Model Relationships** - Track changes to specific models
- **Context Information** - Additional context for events

#### Search & Analysis
- **Advanced Filtering** - Filter by user, action, date range, level
- **Security Event Tracking** - Special handling for security events
- **Anomaly Detection** - Automatic detection of suspicious patterns
- **Export Capabilities** - CSV export for compliance reporting

### Configuration

#### Database Migration
```bash
php artisan migrate
# Creates audit_logs table with optimized indexes
```

#### Middleware Registration
```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        // ... existing middleware
        \App\Http\Middleware\LogUserActivity::class,
    ],
];
```

### Usage Examples

#### Manual Logging
```php
use App\Services\AuditService;
use App\Models\AuditLog;

$auditService = app(AuditService::class);

// Log authentication event
$auditService->logAuth('login', [
    'user_id' => $user->id,
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);

// Log profile change
$auditService->logProfileChange('profile_updated', $user, $oldValues, $newValues);

// Log security event
$auditService->logSecurity('suspicious_login', 'Multiple failed logins detected', [
    'ip_address' => $request->ip(),
    'attempts' => 5,
], 'warning');
```

#### Query Audit Logs
```php
// Get user's audit logs
$logs = $auditService->getUserLogs($user, 50, [
    'start_date' => now()->subDays(30),
    'end_date' => now(),
    'level' => 'warning'
]);

// Get security events
$securityLogs = $auditService->getSecurityLogs(100);

// Get error logs
$errorLogs = $auditService->getErrorLogs(50);
```

#### Model Logging
```php
// Using the AuditLog model directly
AuditLog::log([
    'user_id' => auth()->id(),
    'action' => 'profile_updated',
    'description' => 'User updated their profile',
    'old_values' => ['name' => 'Old Name'],
    'new_values' => ['name' => 'New Name'],
    'model_type' => \App\Models\Profile::class,
    'model_id' => $profile->id,
    'level' => 'info',
]);
```

### Audit Log Events

#### Authentication Events
```php
// Successful login
AuditLog::logLogin($user, $ipAddress, $userAgent);

// Failed login attempt
AuditLog::logLoginFailed($email, $ipAddress, $userAgent);

// User logout
AuditLog::logLogout($user, $ipAddress, $userAgent);
```

#### Two-Factor Events
```php
// 2FA enabled
AuditLog::logTwoFactorEnabled($user);

// 2FA disabled
AuditLog::logTwoFactorDisabled($user);

// 2FA verification failed
AuditLog::logTwoFactorFailed($user, $ipAddress);

// Recovery code used
AuditLog::logRecoveryCodeUsed($user, $ipAddress);
```

#### Profile Events
```php
// Profile updated
AuditLog::logProfileUpdate($user, $oldValues, $newValues);

// Password reset requested
AuditLog::logPasswordResetRequest($user, $ipAddress);

// Password reset completed
AuditLog::logPasswordResetCompleted($user, $ipAddress);
```

#### Social Authentication Events
```php
// Social account connected
AuditLog::logSocialAccountConnected($user, 'google', $ipAddress);

// Social account disconnected
AuditLog::logSocialAccountDisconnected($user, 'github', $ipAddress);
```

### Database Schema

#### Audit Logs Table
```sql
- id (primary)
- user_id (foreign key, nullable, set null on delete)
- action (string) - Event type (login, profile_updated, etc.)
- description (text, nullable) - Human-readable description
- ip_address (string, nullable) - Client IP address
- user_agent (text, nullable) - Browser user agent
- old_values (json, nullable) - Before values for changes
- new_values (json, nullable) - After values for changes
- model_type (string, nullable) - Related model type
- model_id (unsigned big integer, nullable) - Related model ID
- level (string, default 'info') - info, warning, error, critical
- metadata (json, nullable) - Additional context
- created_at (timestamp) - Event timestamp
```

### Advanced Features

#### Statistics & Analytics
```php
// Get audit statistics
$stats = $auditService->getStatistics();

// Returns:
// - total_logs, last_24_hours, last_7_days, last_30_days
// - security_events, error_events, unique_users
// - top_actions, top_ips, activity_by_level
```

#### Suspicious Activity Detection
```php
// Detect suspicious patterns
$patterns = $auditService->getSuspiciousActivity();

// Returns:
// - multiple_failed_logins (same IP, many attempts)
// - rapid_profile_updates (same user, many changes)
// - unusual_ip_activity (user from many IPs)
```

#### Security Anomaly Detection
```php
// Check for security anomalies
$anomalies = $auditService->checkSecurityAnomalies();

// Returns:
// - concurrent_logins (same user, different IPs)
// - excessive_password_resets (same IP, many requests)
```

#### Export & Compliance
```php
// Export audit logs to CSV
$csv = $auditService->exportToCSV([
    'start_date' => now()->subDays(30),
    'end_date' => now(),
    'user_id' => $userId,
]);

// Cleanup old logs (automatic retention)
$deleted = $auditService->cleanupOldLogs(90); // Keep 90 days
```

### Performance Considerations

#### Database Indexes
- `user_id, created_at` - User activity queries
- `action, created_at` - Action-based queries
- `model_type, model_id` - Model relationship queries
- `ip_address` - IP-based queries
- `level, created_at` - Security event queries

#### Caching Strategy
- Statistics cached for 5 minutes
- Rate limit counters use Redis
- Automatic cache invalidation on new logs

#### Data Retention
- Automatic cleanup of old logs (default 90 days)
- Configurable retention period
- Efficient bulk deletion operations

### Compliance & Security

#### GDPR Compliance
- User activity tracking for audit purposes
- Data export capabilities for user requests
- Configurable data retention policies

#### Security Monitoring
- Real-time security event tracking
- Automatic anomaly detection
- Integration with rate limiting system
- Alert system for critical events

#### Privacy Protection
- Sensitive data filtering in logs
- User consent tracking
- Data minimization principles

---

### Overview
The package includes a complete user profile management system with avatar uploads, privacy controls, and profile completion tracking.

### Features

#### Profile Management (`ProfileEditor`)
- **Bio** - Rich text biography (500 chars max)
- **Contact Information** - Phone number and website
- **Location** - Country, state, city, and address using geographical data
- **Privacy Settings** - Granular controls for profile visibility
- **Profile Completion** - Real-time progress tracking

#### Avatar System (`AvatarUpload`)
- **Image Processing** - Automatic cropping and resizing to squares
- **Multiple Sizes** - 300x300 avatar + 100x100 thumbnail
- **File Validation** - Support for JPG, PNG, GIF, WebP (max 5MB)
- **Storage Management** - Organized file storage with automatic cleanup

#### Privacy Controls
- **Profile Visibility** - Public/private profile toggle
- **Field Visibility** - Control which fields appear publicly
- **Message Settings** - Allow/disallow user messages
- **Email Privacy** - Hide/show email address

### Usage Examples

#### Accessing Profile Data
```php
// Get user profile
$user = Auth::user();
$profile = $user->profile ?? $user->getOrCreateProfile();

// Check profile completion
if ($user->hasCompleteProfile()) {
    // User has 80%+ complete profile
}

// Get avatar URL
$avatarUrl = $user->avatar_url;
$thumbnailUrl = $user->thumbnail_url;
```

#### Profile Completion Tracking
```php
// Calculate completion percentage
$percentage = $profile->calculateCompletionPercentage();

// Update completion percentage
$profile->updateCompletionPercentage();

// Get completion status message
$message = $profile->completion_percentage < 50 
    ? 'Profile needs more information' 
    : 'Looking good!';
```

#### Privacy Settings
```php
// Check if user can view another profile
if (auth()->user()->canViewProfile($targetUser)) {
    // Show profile
}

// Get privacy settings
$privacy = $profile->privacy_settings;
$showPhone = $privacy['show_phone'] ?? true;
```

### Blade Integration

#### Profile Editor
```blade
<livewire:profile.profile-editor />
```

#### Avatar Upload
```blade
<livewire:profile.avatar-upload />
```

#### Display User Avatar
```blade
<img src="{{ $user->thumbnail_url }}" alt="{{ $user->display_name }}" />
```

### Routes

#### Profile Management
- `GET /profile` - View own profile
- `GET /profile/edit` - Edit profile form
- `GET /users/{user}` - View public profile (if allowed)

#### API Endpoints
- `GET /api/profile/states/{country}` - Get states for country
- `GET /api/profile/cities/{state}` - Get cities for state

### Database Schema

#### Profiles Table
```sql
- id (primary)
- user_id (foreign key, unique)
- bio (text, nullable)
- phone (string, nullable)
- website (string, nullable)
- country_id (foreign key, nullable)
- state_id (foreign key, nullable)
- city_id (foreign key, nullable)
- address (string, nullable)
- privacy_settings (json, nullable)
- completion_percentage (integer, default 0)
- timestamps
```

#### Avatars Table
```sql
- id (primary)
- profile_id (foreign key, unique)
- original_name (string)
- file_path (string)
- file_name (string)
- mime_type (string)
- file_size (integer)
- width (integer)
- height (integer)
- disk (string, default 'public')
- timestamps
```

### File Storage
- **Avatars**: `storage/app/public/avatars/`
- **Thumbnails**: `storage/app/public/avatars/thumbnails/`
- **Public URL**: `/storage/avatars/`

### Image Processing
- **Automatic Cropping** - Centers and crops to square format
- **Resizing** - 300x300 for avatars, 100x100 for thumbnails
- **Quality** - JPEG compression at 90% quality
- **Formats** - Converts all uploads to JPEG for consistency

---

## � Social Authentication System

### Overview
The package includes a comprehensive social authentication system using Laravel Socialite, supporting OAuth login with Google, GitHub, and Twitter. Features include account linking, smart conflict resolution, and seamless integration with the existing user profile system.

### Supported Providers
- **Google** - Google OAuth 2.0 authentication
- **GitHub** - GitHub OAuth authentication  
- **Twitter** - Twitter OAuth 2.0 authentication

### Features

#### Social Login (`SocialLogin`)
- **Multiple Providers** - Support for Google, GitHub, Twitter
- **Account Creation** - Automatic user creation from social data
- **Account Linking** - Link social accounts to existing users
- **Smart Merging** - Intelligent conflict resolution
- **Token Management** - OAuth token storage and refresh

#### Account Management (`SocialAccountManager`)
- **Connected Accounts** - View all linked social accounts
- **Account Status** - Token validity and connection status
- **Disconnect Accounts** - Safe removal with validation
- **Security Controls** - Prevent locking out users

#### Account Linking System
- **Email Matching** - Automatic linking for matching emails
- **Manual Linking** - Users can manually link accounts
- **Conflict Prevention** - Prevent duplicate account linking
- **Profile Integration** - Sync social data with user profiles

### Configuration

#### Environment Variables
Add these to your `.env` file:

```env
# Google OAuth
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

# GitHub OAuth
GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_client_secret
GITHUB_REDIRECT_URI="${APP_URL}/auth/github/callback"

# Twitter OAuth
TWITTER_CLIENT_ID=your_twitter_client_id
TWITTER_CLIENT_SECRET=your_twitter_client_secret
TWITTER_REDIRECT_URI="${APP_URL}/auth/twitter/callback"
```

#### OAuth App Setup

**Google:**
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create new project or select existing
3. Enable Google+ API
4. Create OAuth 2.0 credentials
5. Add authorized redirect URI

**GitHub:**
1. Go to [GitHub Developer Settings](https://github.com/settings/developers)
2. Create new OAuth App
3. Set authorization callback URL
4. Copy Client ID and Secret

**Twitter:**
1. Go to [Twitter Developer Portal](https://developer.twitter.com/)
2. Create new project and app
3. Enable OAuth 2.0
4. Set callback URL
5. Generate Client ID and Secret

### Usage Examples

#### Social Login Button
```blade
<!-- In login form -->
<livewire:auth.social-login :show-divider="true" />

<!-- Compact version -->
<livewire:auth.social-login :show-compact="true" />

<!-- Icons only -->
<livewire:auth.social-login :show-icons-only="true" />
```

#### Account Management
```blade
<!-- In profile edit page -->
<livewire:auth.social-account-manager />
```

#### Programmatic Social Login
```php
use App\Services\SocialAuthService;

// Handle social login
$socialUser = Socialite::driver('google')->user();
$user = $socialAuthService->handleSocialLogin('google', $socialUser);

// Check user social accounts
if ($user->hasSocialAccount('google')) {
    $googleAccount = $user->getSocialAccount('google');
}

// Disconnect social account
$socialAuthService->disconnectSocialAccount($user, 'google');
```

#### Social Account Data
```php
// Get connected providers
$providers = $user->connected_providers; // ['google', 'github']

// Check token validity
$socialAccount = $user->getSocialAccount('google');
if ($socialAccount->hasValidToken()) {
    // Token is valid
}

// Get provider-specific data
$avatar = $socialAccount->avatar_url;
$nickname = $socialAccount->nickname;
```

### Routes

#### Authentication Routes
- `GET /auth/{provider}` - Redirect to OAuth provider
- `GET /auth/{provider}/callback` - Handle OAuth callback
- `POST /auth/{provider}/disconnect` - Disconnect social account
- `GET /auth/{provider}/link` - Link social account to existing user
- `GET /auth/{provider}/link/callback` - Handle linking callback
- `GET /api/social/providers` - Get available providers

### Database Schema

#### Social Accounts Table
```sql
- id (primary)
- user_id (foreign key, cascade delete)
- provider (string) - google, github, twitter
- provider_id (string, unique) - Provider's user ID
- provider_token (string, nullable) - OAuth access token
- provider_refresh_token (string, nullable) - OAuth refresh token
- provider_expires_in (integer, nullable) - Token expiration timestamp
- provider_data (json, nullable) - Raw provider data
- nickname (string, nullable) - Provider username
- name (string, nullable) - Full name from provider
- email (string, nullable) - Email from provider
- avatar (string, nullable) - Avatar URL from provider
- timestamps
```

### Security Features

#### Token Management
- **Secure Storage** - Tokens encrypted in database
- **Expiration Handling** - Automatic token refresh
- **Revocation** - Safe token invalidation
- **Scope Limitation** - Minimal required permissions

#### Account Protection
- **Conflict Resolution** - Smart email matching
- **Duplicate Prevention** - Multiple account safeguards
- **Disconnect Validation** - Prevent account lockout
- **Privacy Controls** - Granular data sharing settings

#### Session Security
- **State Verification** - CSRF protection
- **Redirect Validation** - Safe URL redirection
- **Session Binding** - Secure session handling
- **Rate Limiting** - Prevent abuse

### Integration with User Profiles

#### Profile Sync
- **Avatar Import** - Import social avatars
- **Name Sync** - Sync display names
- **Bio Updates** - Update profile bio with social data
- **Location Data** - Import location information

#### Privacy Integration
- **Field Visibility** - Respect privacy settings
- **Public Profiles** - Social account display options
- **Data Control** - User control over shared data

### Error Handling

#### Common Scenarios
- **Provider Errors** - Graceful OAuth failure handling
- **Network Issues** - Retry mechanisms
- **Invalid Tokens** - Automatic token refresh
- **Account Conflicts** - User-friendly resolution

#### User Feedback
- **Clear Messages** - Informative error messages
- **Recovery Options** - Alternative login methods
- **Status Indicators** - Connection status display
- **Help Links** - Support documentation

---

## � Available Commands

### Composer Scripts
- `composer run setup` - Complete project setup with optimized dependencies and database seeding
- `composer run dev` - Development server with all services
- `composer run test` - Run test suite

### Artisan Commands
- `php artisan role:create {name}` - Create a new role with validation and duplicate prevention
- `php artisan serve` - Start development server
- `php artisan migrate` - Run database migrations
- `php artisan db:seed` - Seed database with sample data
- `php artisan queue:work` - Start queue worker
- `php artisan pail` - View real-time logs

#### Role Creation Examples
```bash
# Create standard roles
php artisan role:create admin
php artisan role:create member
php artisan role:create customer
php artisan role:create manager

# Create custom roles
php artisan role:create moderator
php artisan role:create editor
php artisan role:create subscriber
```

### Database Seeding
The project includes comprehensive data seeders:
- **Geographical Data**: 
  - `CountriesTableSeeder` - 250+ countries
  - `StatesTableSeeder` - 5,000+ states/provinces
  - `CitiesTableChunk*Seeder` - 150,000+ cities (split into 5 chunks)
  - `CountySeeder` - Kenyan counties
  - `SubCountySeeder` - Kenyan constituencies and wards
- **User Data**:
  - `ProfileSeeder` - Creates profiles for existing users with sample data
  - `SocialAccountSeeder` - Adds sample social accounts for testing OAuth functionality

Run individual seeders:
```bash
php artisan db:seed --class=ProfileSeeder
php artisan db:seed --class=SocialAccountSeeder
```

---

## 🎨 UI Components

### Auth Layout
- Modern gradient background
- Glass morphism effects
- Smooth animations (GSAP)
- Password strength indicator
- Focus states and transitions
- Loading states

### Form Features
- Real-time validation
- Password toggle visibility
- Strength indicator
- Animated transitions
- Mobile responsive

## 📁 Project Structure

```
app/
├── Console/Commands/         # CreateRole.php - Role creation command
├── Http/
│   ├── Controllers/
│   │   ├── Auth/           # Login, Register, Password controllers
│   │   ├── SocialAuthController.php # Social authentication
│   │   └── Controller.php  # Base controller
│   ├── Kernel.php          # HTTP middleware
│   └── Middleware/         # Custom middleware
├── Livewire/
│   ├── Auth/               # RegistrationForm.php, LoginForm.php
│   │   ├── SocialLogin.php      # Social login component
│   │   └── SocialAccountManager.php # Account management
│   └── Profile/            # ProfileEditor.php, AvatarUpload.php
├── Models/
│   ├── User.php            # User model with roles and social relationships
│   ├── Country.php         # Country model
│   ├── State.php           # State/Province model
│   ├── City.php            # City model
│   ├── County.php          # Kenyan county model
│   ├── SubCounty.php       # Kenyan sub-county model
│   ├── Profile.php         # User profile model
│   ├── Avatar.php          # Avatar model with image processing
│   └── SocialAccount.php   # Social account model
├── Notifications/          # Email notifications
├── Providers/              # Service providers
└── Services/
    ├── EmailService.php    # Email handling service
    ├── AvatarUploadService.php # Image processing and upload service
    └── SocialAuthService.php # OAuth authentication service
database/
├── factories/              # Model factories
├── migrations/             # Database migrations
└── seeders/               # Data seeders (geographical data + ProfileSeeder + SocialAccountSeeder)
resources/
├── views/
│   ├── layouts/
│   │   ├── auth.blade.php
│   │   └── app.blade.php   # Profile layout
│   ├── livewire/
│   │   ├── auth/
│   │   │   ├── social-login.blade.php
│   │   │   └── social-account-manager.blade.php
│   │   └── profile/        # Profile component views
│   ├── profile/            # Profile pages (show, edit, public)
│   ├── auth/
│   ├── components/
│   ├── emails/
│   └── welcome.blade.php
├── css/                   # Tailwind CSS
└── js/                    # JavaScript assets
public/
├── images/                # Default avatar and other assets
└── storage/               # Public file storage (avatars)
routes/
├── web.php                # Web routes (includes profile and social routes)
├── api.php                # API routes
└── console.php            # Console routes
composer.json
package.json
vite.config.js
```

## �️ Security

* Email verification for members
* Password strength validation
* Role-based access
* CSRF protection
* Bcrypt password hashing

## 📚 Customization

### Registration Fields

* Modify `RegistrationForm.php` to add/remove fields:

```php
// Add new field
public $new_field = '';
// Add validation
protected $rules['new_field'] = ['required', 'string'];
// Handle field in registration
$user->update(['new_field' => $validated['new_field']]);
```

### Login Redirects

* Update `LoginForm.php` for new roles:

```php
if ($user->hasRole('new_role')) {
    return '/new-route';
}
```

### UI Styling

Update `resources/views/layouts/auth.blade.php`:

```css
.gradient-bg {
    background: linear-gradient(135deg, #color1 0%, #color2 100%);
}
```

## 🔄 Available Routes

### Authentication Routes

**Guest Routes (middleware: guest)**
* `GET /register` → registration form
* `POST /register` → registration submission
* `GET /login` → login form
* `POST /login` → login submission
* `GET /forgot-password` → password reset request form
* `POST /forgot-password` → send password reset link
* `GET /reset-password/{token}` → password reset form
* `POST /reset-password` → password reset submission

**Authenticated Routes**
* `POST /logout` → logout

### Email Verification Routes (middleware: auth)
* `GET /email/verify` → verification notice page
* `GET /email/verify/{id}/{hash}` → verify email (signed)
* `POST /email/verification-notification` → resend verification link (throttled)

### General Routes
* `GET /` → welcome page

### Route Implementation Details
- Uses controller-based routing with `Route::controller()`
- Email verification includes welcome email functionality
- Password reset uses Laravel's built-in functionality
- All auth routes are properly grouped by middleware

## 🛠️ Dependencies

* **PHP >= 8.2** - Core requirement
* **Laravel ^12.0** - Framework
* **Livewire ^4.1** - Dynamic components
* **Spatie Laravel Permission ^6.24** - Role/permission system
* **Laravel Socialite ^5.24** - OAuth authentication
* **Tailwind CSS ^4.0** - Styling framework
* **Vite ^7.0.7** - Asset bundling
* **Node.js & NPM** - Frontend build tools
* **Intervention Image ^3.8** - Image processing for avatars

### Security & Authentication Dependencies
* **PragmaRX Google2FA ^2.0** - Two-factor authentication
* **Simple QrCode ^4.2** - QR code generation for 2FA

### Development Dependencies
* **Laravel Pint ^1.24** - Code style
* **Laravel Sail ^1.41** - Docker environment
* **PHPUnit ^11.5.3** - Testing framework
* **Faker ^1.23** - Test data generation
* **Concurrently ^9.0.1** - Parallel process running

## 📝 Environment Variables

Key environment variables in `.env`:

```env
APP_NAME="MCBANKS LARAVEL"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mcbankslaravel
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Queue Configuration
QUEUE_CONNECTION=database

# Cache Configuration
CACHE_DRIVER=file
```

### Email Configuration
The project includes an `EmailService` for handling welcome emails and notifications. Configure your mail settings in the `.env` file for email verification to work properly.

## 🚀 Deployment

1. Copy environment:

```bash
cp .env.example .env
php artisan key:generate
php artisan config:cache
```

2. Migrate database:

```bash
php artisan migrate --force
```

3. Optimize assets:

```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

4. Start queue (if needed):

```bash
php artisan queue:work
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit changes
4. Push
5. Open PR

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

If you encounter any issues:

1. Check the [documentation](https://laravel.com/docs)
2. Search existing [issues](https://github.com/MCBANKSKE/MCBANKSLARAVEL/issues)
3. Create a new issue with detailed information

## 🔄 Version History

* **v1.3.0** - **SECURITY & DOCUMENTATION UPDATE** - Added comprehensive security features including Two-Factor Authentication, Granular Rate Limiting, User Activity Audit Logging, complete API documentation, developer setup guide, and production deployment guide
* **v1.2.0** - Added comprehensive Social Authentication system with OAuth login, account linking, and smart conflict resolution
* **v1.1.0** - Added comprehensive User Profiles system with avatar uploads, privacy settings, and profile completion tracking
* **v1.0.2** - Added comprehensive geographical data and enhanced SubCounty model with advanced query methods
* **v1.0.1** - Updated documentation and GitHub repository links
* **v1.0.0** - Initial release with Laravel 12, Livewire 4.1, Spatie Permission

## 📊 Project Statistics

- **Models**: 11 (User, Profile, Avatar, SocialAccount, TwoFactorAuthentication, AuditLog, Country, State, City, County, SubCounty)
- **Livewire Components**: 7 (RegistrationForm, LoginForm, ProfileEditor, AvatarUpload, SocialLogin, SocialAccountManager, TwoFactorAuthentication)
- **Console Commands**: 1 (CreateRole)
- **Services**: 6 (EmailService, AvatarUploadService, SocialAuthService, TwoFactorService, RateLimitingService, AuditService)
- **Controllers**: Multiple auth controllers including SocialAuthController, TwoFactorController
- **Database Seeders**: 11 (including 5 chunks for cities data + ProfileSeeder + SocialAccountSeeder)
- **Migration Files**: 14 (including profiles, avatars, social accounts, two-factor auth, audit logs tables)
- **Middleware**: 6 (LogUserActivity, RateLimitAuth, RateLimitApi, RateLimitProfile, TwoFactorChallenge)
- **Notifications**: 3 (TwoFactorEnabled, TwoFactorDisabled, RecoveryCodeUsed)

## 📚 Documentation Files

- `docs/API.md` - Complete REST API documentation with endpoints, examples, and SDK implementations
- `docs/DEVELOPER.md` - Comprehensive developer setup guide with IDE configuration and best practices
- `docs/DEPLOYMENT.md` - Production deployment guide with multiple strategies and security hardening
- `docs/IMPROVEMENTS.md` - Summary of all security enhancements and new features

## 🔍 Key Features Deep Dive

### Social Authentication System
The social authentication system provides complete OAuth integration:
- **Multi-Provider Support** - Google, GitHub, Twitter OAuth login
- **Account Linking** - Connect multiple social accounts to one user
- **Smart Conflict Resolution** - Automatic email matching and duplicate prevention
- **Token Management** - Secure OAuth token storage and refresh
- **Security Controls** - CSRF protection, rate limiting, and safe disconnection

### User Profiles System
The profiles system provides a complete user experience with:
- **Profile Management** - Edit bio, contact info, location with geographical data integration
- **Avatar System** - Upload, crop, resize images with automatic thumbnail generation
- **Privacy Controls** - Granular settings for profile visibility and data sharing
- **Completion Tracking** - Real-time progress indicator to encourage profile completion

### SubCounty Model Advanced Features
The `SubCounty` model includes specialized query methods for Kenyan administrative data:
- `getUniqueConstituencies($countyId)` - Get constituencies for a specific county
- `getUniqueWards($countyId)` - Get all wards in a county
- `getWardsByConstituency($countyId, $constituencyName)` - Get wards in a specific constituency
- `getAllUniqueConstituencies()` - Get all constituencies nationwide
- `getAllUniqueWards()` - Get all wards nationwide
- `getByCounty($countyId)` - Get sub-counties with county relationship

### Email Service Integration
The project includes a dedicated `EmailService` that handles:
- Welcome emails after successful email verification
- Email notification dispatching
- Centralized email template management

### Avatar Upload Service
The `AvatarUploadService` provides:
- Image processing with Intervention Image
- Automatic square cropping and resizing
- Multiple size generation (avatar + thumbnail)
- File validation and storage management
- Cleanup and organization of uploaded files

### Social Auth Service
The `SocialAuthService` handles:
- OAuth authentication flow management
- Account creation and linking
- Token storage and refresh
- Conflict resolution and error handling
- Provider configuration validation

### Security Features
- Role-based access control using Spatie Laravel Permission
- Super admin override capability
- Email verification for secure account access
- Password strength validation
- CSRF protection
- Bcrypt password hashing
- Profile privacy controls
- File upload validation and processing
- OAuth token security and management
- Social account conflict prevention
- Session security and rate limiting
- **Two-Factor Authentication** - TOTP support with recovery codes
- **Granular Rate Limiting** - Endpoint-specific rate limiting with IP blacklisting
- **User Activity Audit Logging** - Comprehensive security monitoring and compliance
- **Suspicious Activity Detection** - AI-driven anomaly detection and alerting
- **Security Event Tracking** - Real-time monitoring of security events
- **IP Blacklisting** - Automatic blocking of malicious IP addresses
- **Advanced Threat Protection** - Multi-layered security architecture

### Enterprise Security Features
- **Multi-Factor Authentication** - TOTP with QR code setup and recovery codes
- **Advanced Rate Limiting** - Configurable limits per endpoint with Redis caching
- **Comprehensive Audit Trails** - Complete user activity logging for compliance
- **Anomaly Detection** - Automatic detection of suspicious patterns and behaviors
- **Security Analytics** - Real-time statistics and security monitoring
- **Compliance Support** - GDPR-compliant data handling and export capabilities
- **Performance Optimized** - Efficient caching and database indexing for security systems

---

## 🚀 Quick Security Setup

### Enable Two-Factor Authentication
```bash
# Install required packages
composer update

# Run migrations
php artisan migrate

# Add middleware to app/Http/Kernel.php
'2fa.challenge' => \App\Http\Middleware\TwoFactorChallenge::class,
```

### Configure Rate Limiting
```bash
# Add rate limiting middleware
'rate.limit.auth' => \App\Http\Middleware\RateLimitAuth::class,
'rate.limit.api' => \App\Http\Middleware\RateLimitApi::class,
'rate.limit.profile' => \App\Http\Middleware\RateLimitProfile::class,
```

### Enable Audit Logging
```bash
# Add activity logging middleware
\App\Http\Middleware\LogUserActivity::class,
```

### Security Best Practices
1. **Enable 2FA** on all admin accounts
2. **Monitor audit logs** regularly for suspicious activity
3. **Configure rate limits** appropriately for your use case
4. **Set up alerts** for security events
5. **Regular cleanup** of old audit logs (90 days default)
6. **Review security statistics** and analytics weekly

---

**Built with ❤️ using [Laravel](https://laravel.com)**

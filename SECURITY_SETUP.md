# Security Configuration Guide

This guide provides step-by-step instructions for configuring the security features in MCBANKS Laravel.

## 🚀 Quick Setup

### 1. Install Dependencies
```bash
composer update
```

### 2. Run Migrations
```bash
php artisan migrate
```

### 3. Run Setup Script
```bash
php setup_security.php
```

## 🔧 Manual Configuration

### Middleware Registration

Add these to `app/Http/Kernel.php`:

#### Middleware Groups
```php
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
```

#### Middleware Aliases
```php
protected $middlewareAliases = [
    // ... existing aliases
    'rate.limit.auth' => \App\Http\Middleware\RateLimitAuth::class,
    'rate.limit.profile' => \App\Http\Middleware\RateLimitProfile::class,
    '2fa.challenge' => \App\Http\Middleware\TwoFactorChallenge::class,
];
```

### Route Configuration

Add these to `routes/web.php`:

```php
use App\Http\Controllers\TwoFactorController;

// Two-Factor Authentication Routes
Route::middleware(['auth', 'throttle:5,1'])->group(function () {
    Route::controller(TwoFactorController::class)->group(function () {
        Route::get('/2fa/challenge', 'showChallenge')->name('2fa.challenge');
        Route::post('/2fa/verify', 'verify')->name('2fa.verify');
        Route::get('/2fa/recovery', 'showRecoveryForm')->name('2fa.recovery');
        Route::post('/2fa/recovery/verify', 'verifyRecovery')->name('2fa.recovery.verify');
        Route::post('/2fa/logout', 'logout')->name('2fa.logout');
    });
});
```

### Environment Variables

Add these to your `.env` file:

```env
# Two-Factor Authentication (optional, defaults work fine)
GOOGLE2FA_SECRET=
GOOGLE2FA_QRCODE_SIZE=200

# Rate Limiting (optional, defaults work fine)
RATE_LIMIT_CACHE_DRIVER=redis
RATE_LIMIT_CACHE_PREFIX=rate_limit:

# Security & Audit Settings
AUDIT_LOG_RETENTION_DAYS=90
SECURITY_ALERT_EMAIL=admin@example.com
```

## 📋 Configuration Checklist

### ✅ Dependencies
- [ ] `pragmarx/google2fa-laravel` installed
- [ ] `simplesoftwareio/simple-qrcode` installed

### ✅ Database
- [ ] Run `php artisan migrate`
- [ ] Two-factor authentication table created
- [ ] Audit logs table created

### ✅ Middleware
- [ ] `LogUserActivity` added to web middleware group
- [ ] `RateLimitApi` added to API middleware group
- [ ] Rate limiting middleware aliases registered
- [ ] Two-factor challenge middleware alias registered

### ✅ Routes
- [ ] Two-factor authentication routes added
- [ ] Controller imported and routes configured

### ✅ Environment
- [ ] Two-factor authentication variables configured
- [ ] Rate limiting cache driver configured
- [ ] Audit log retention period set

## 🧪 Testing Configuration

### Test Two-Factor Authentication
1. Login to your account
2. Navigate to profile edit page
3. Enable Two-Factor Authentication
4. Scan QR code with authenticator app
5. Verify setup with 6-digit code

### Test Rate Limiting
1. Attempt multiple failed logins (5+ times)
2. Verify rate limiting message appears
3. Check rate limiting headers in response

### Test Audit Logging
1. Perform various actions (login, profile update, etc.)
2. Check audit_logs table for entries
3. Verify metadata and IP addresses are logged

## 🔒 Security Best Practices

### Production Environment
1. **Enable 2FA** on all admin accounts
2. **Configure Redis** for rate limiting cache
3. **Set up email** for security notifications
4. **Monitor audit logs** regularly
5. **Set log retention** to appropriate period (90 days default)

### Monitoring
1. **Check security statistics** weekly
2. **Review failed login attempts**
3. **Monitor IP blacklisting**
4. **Track two-factor adoption rate**
5. **Audit suspicious activity patterns**

### Maintenance
1. **Clean up old audit logs** regularly
2. **Regenerate recovery codes** periodically
3. **Update rate limits** as needed
4. **Review middleware configuration**
5. **Test security features** after updates

## 🚨 Troubleshooting

### Common Issues

#### Two-Factor Authentication Not Working
- Check that `pragmarx/google2fa-laravel` is installed
- Verify QR code displays correctly
- Ensure time is synchronized on server and device
- Check that middleware is properly registered

#### Rate Limiting Not Applied
- Verify Redis is configured and running
- Check middleware registration in Kernel.php
- Ensure cache driver is set to Redis
- Verify route middleware is applied correctly

#### Audit Logs Not Created
- Check that `LogUserActivity` middleware is registered
- Verify database migration was successful
- Check that user is authenticated
- Ensure middleware is applied to routes

### Debug Mode
Add these to `.env` for debugging:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

Check logs with:
```bash
php artisan log:tail
```

## 📚 Additional Resources

- **README.md** - Complete feature documentation
- **docs/API.md** - REST API documentation
- **docs/DEVELOPER.md** - Developer setup guide
- **docs/DEPLOYMENT.md** - Production deployment guide
- **docs/IMPROVEMENTS.md** - Security enhancement summary

## 🆘 Support

If you encounter issues:

1. Run the setup script: `php setup_security.php`
2. Check the troubleshooting section above
3. Review the documentation files
4. Create an issue with detailed information

---

**Built with ❤️ using Laravel**

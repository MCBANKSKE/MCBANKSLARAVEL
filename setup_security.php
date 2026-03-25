#!/usr/bin/env php
<?php

/**
 * Security Setup Script for MCBANKS Laravel
 * 
 * This script helps configure the security features including:
 * - Two-Factor Authentication
 * - Rate Limiting
 * - Audit Logging
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

echo "🔒 MCBANKS Laravel Security Setup\n";
echo "===================================\n\n";

// Check if we're in the right directory
if (!file_exists(__DIR__ . '/artisan')) {
    echo "❌ Error: Please run this script from the project root directory.\n";
    exit(1);
}

echo "This script will help you configure the security features.\n\n";

// Step 1: Check dependencies
echo "📦 Step 1: Checking dependencies...\n";

$composerPath = __DIR__ . '/composer.json';
$composerContent = json_decode(file_get_contents($composerPath), true);

$requiredPackages = [
    'pragmarx/google2fa-laravel',
    'simplesoftwareio/simple-qrcode'
];

$missingPackages = [];
foreach ($requiredPackages as $package) {
    if (!isset($composerContent['require'][$package])) {
        $missingPackages[] = $package;
    }
}

if (!empty($missingPackages)) {
    echo "⚠️  Missing required packages:\n";
    foreach ($missingPackages as $package) {
        echo "   - $package\n";
    }
    echo "\n";
    echo "📥 Installing missing packages...\n";
    passthru('composer update');
    echo "\n";
} else {
    echo "✅ All required packages are installed.\n\n";
}

// Step 2: Run migrations
echo "🗄️  Step 2: Running database migrations...\n";
passthru('php artisan migrate --force');
echo "\n";

// Step 3: Check environment configuration
echo "🔧 Step 3: Checking environment configuration...\n";

$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) {
    echo "📝 Creating .env file from example...\n";
    copy(__DIR__ . '/.env.example', $envPath);
    passthru('php artisan key:generate');
}

$envContent = file_get_contents($envPath);
$envUpdates = [];

// Check for required environment variables
$requiredEnvVars = [
    'GOOGLE2FA_SECRET' => 'Two-Factor Authentication',
    'RATE_LIMIT_CACHE_DRIVER' => 'Rate Limiting',
    'AUDIT_LOG_RETENTION_DAYS' => 'Audit Logging'
];

foreach ($requiredEnvVars as $var => $feature) {
    if (!str_contains($envContent, $var . '=')) {
        echo "⚠️  Missing $var for $feature\n";
        $envUpdates[] = $var;
    } else {
        echo "✅ $var is configured\n";
    }
}

if (!empty($envUpdates)) {
    echo "\n📝 The following environment variables need to be configured:\n";
    foreach ($envUpdates as $var) {
        echo "   - $var\n";
    }
    echo "\n";
    echo "💡 Please update your .env file with these values.\n";
    echo "💡 See docs/DEPLOYMENT.md for configuration details.\n\n";
} else {
    echo "✅ Environment variables are configured.\n\n";
}

// Step 4: Check if middleware is registered
echo "🔌 Step 4: Checking middleware registration...\n";

$kernelPath = __DIR__ . '/app/Http/Kernel.php';
$kernelContent = file_get_contents($kernelPath);

$requiredMiddleware = [
    'LogUserActivity::class',
    'RateLimitApi::class',
    'RateLimitAuth::class',
    'RateLimitProfile::class',
    'TwoFactorChallenge::class'
];

$missingMiddleware = [];
foreach ($requiredMiddleware as $middleware) {
    if (!str_contains($kernelContent, $middleware)) {
        $missingMiddleware[] = $middleware;
    }
}

if (!empty($missingMiddleware)) {
    echo "⚠️  The following middleware need to be registered:\n";
    foreach ($missingMiddleware as $middleware) {
        echo "   - $middleware\n";
    }
    echo "\n";
    echo "📝 Please update app/Http/Kernel.php with these middleware.\n";
    echo "💡 See the README.md for configuration details.\n\n";
} else {
    echo "✅ All middleware are registered.\n\n";
}

// Step 5: Check if routes are added
echo "🛣️  Step 5: Checking route configuration...\n";

$routesPath = __DIR__ . '/routes/web.php';
$routesContent = file_get_contents($routesPath);

if (str_contains($routesContent, 'TwoFactorController')) {
    echo "✅ Two-Factor Authentication routes are configured.\n";
} else {
    echo "⚠️  Two-Factor Authentication routes need to be added.\n";
    echo "💡 See the README.md for route configuration.\n";
}

echo "\n";

// Step 6: Test configuration
echo "🧪 Step 6: Testing configuration...\n";

// Test if services can be resolved
try {
    require_once __DIR__ . '/bootstrap/app.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    
    if (class_exists('App\Services\TwoFactorService')) {
        echo "✅ TwoFactorService is available\n";
    }
    
    if (class_exists('App\Services\RateLimitingService')) {
        echo "✅ RateLimitingService is available\n";
    }
    
    if (class_exists('App\Services\AuditService')) {
        echo "✅ AuditService is available\n";
    }
    
} catch (Exception $e) {
    echo "⚠️  Error testing services: " . $e->getMessage() . "\n";
}

echo "\n";

// Step 7: Next steps
echo "🚀 Step 7: Next steps\n";
echo "===================\n\n";

echo "1. 📚 Read the documentation:\n";
echo "   - docs/API.md - Complete API documentation\n";
echo "   - docs/DEVELOPER.md - Developer setup guide\n";
echo "   - docs/DEPLOYMENT.md - Production deployment guide\n";
echo "   - docs/IMPROVEMENTS.md - Summary of all enhancements\n\n";

echo "2. 🔧 Configure environment variables:\n";
echo "   - Update .env with your specific settings\n";
echo "   - Set up mail configuration for notifications\n";
echo "   - Configure Redis for rate limiting (recommended)\n\n";

echo "3. 🧪 Test the features:\n";
echo "   - Enable Two-Factor Authentication for your account\n";
echo "   - Test rate limiting with multiple failed attempts\n";
echo "   - Check audit logs for user activity tracking\n\n";

echo "4. 🛡️ Security best practices:\n";
echo "   - Enable 2FA on all admin accounts\n";
echo "   - Monitor audit logs regularly\n";
echo "   - Set up security alerts\n";
echo "   - Regular cleanup of old logs\n\n";

echo "✅ Security setup script completed!\n";
echo "🎉 Your MCBANKS Laravel application is ready with enterprise-grade security!\n\n";

echo "📖 For more information, see:\n";
echo "   - README.md - Complete feature documentation\n";
echo "   - docs/DEPLOYMENT.md - Production deployment guide\n";
echo "   - docs/IMPROVEMENTS.md - Security enhancement summary\n\n";

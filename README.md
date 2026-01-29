# MCBANKS Laravel

A Laravel starter template with role-based authentication, Livewire components, and a modern UI. Perfect for building web applications with user management and permission systems.

---

## 🚀 Features

* **Laravel 12** - Latest Laravel framework
* **Livewire 4.1** - Dynamic components without writing JavaScript
* **Spatie Laravel Permission** - Role and permission system
* **Role-Based Authentication** - Admin, Member, Customer, Manager
* **Email Verification** - Required for members
* **Multi-Step Registration** - Wizard-style form
* **Modern UI** - Tailwind CSS, Glass Morphism, Gradients
* **Password Strength Indicator**
* **Responsive Design**

## 📦 Installation

### Quick Start

```bash
composer create-project mcbanks/mcbankslaravel my-project
cd my-project
```

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

* Install dependencies
* Copy `.env.example` to `.env` 
* Generate app key
* Run migrations
* Install and build frontend assets

## 🔧 Development

**Development server with all services:**

```bash
composer run dev
```

**Run tests:**

```bash
composer run test
# or
php artisan test
```

## 🏗️ Authentication System

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

1. **Create roles before registering users:**

```bash
php artisan role:create admin
php artisan role:create member
php artisan role:create customer
php artisan role:create manager
```

2. **Super Admin**

* `is_super_admin` boolean on `users` table
* Overrides roles, not a Spatie role
* Can bypass role restrictions

3. **Assign roles manually**

```php
$user->assignRole('admin'); // example
```

4. **Add new roles**

* Create role via Spatie
* Update `LoginForm.php` redirection:

```php
if ($user->hasRole('new_role')) {
    return '/new-dashboard';
}
```

---

## 🔄 Redirection Logic

* **Admin** → `/admin` 
* **Member** → `/member` (requires verified email)
* **Customer** → `/` 
* **Manager** → `/` 
* **Fallback** → `/`

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
├── Livewire/Auth/          # RegistrationForm.php, LoginForm.php
├── Http/Controllers/Auth/
database/
├── migrations/
├── seeders/
resources/
├── views/
│   ├── layouts/auth.blade.php
│   ├── livewire/auth/
│   └── auth/
├── js/
routes/
├── web.php
└── api.php
composer.json
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

**Authentication**

* `GET /login` → login form
* `POST /login` → login submission
* `GET /register` → registration form
* `POST /register` → registration submission
* `POST /logout` → logout

**Email Verification**

* `GET /email/verify` → verification notice
* `GET /email/verify/{id}/{hash}` → verify email
* `POST /email/verification-notification` → resend

**Password Reset**

* `GET /forgot-password` → form
* `POST /forgot-password` → send link
* `GET /reset-password/{token}` → form
* `POST /reset-password` → submission

## 🛠️ Dependencies

* PHP >= 8.2
* Laravel ^12.0
* Livewire ^4.1
* Spatie Laravel Permission ^6.24
* Tailwind CSS
* Node.js & NPM

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
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
```

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

* **v1.0.1** - Updated documentation and GitHub repository links
* **v1.0.0** - Initial release with Laravel 12, Livewire 4.1, Spatie Permission

---

**Built with ❤️ using [Laravel](https://laravel.com)**

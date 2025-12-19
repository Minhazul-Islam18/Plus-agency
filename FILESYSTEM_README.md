# Laravel Plus Agency - File System Documentation

## Overview

This is a Laravel 8.x based multi-purpose agency/business management application (Version 3.2, Released: December 13, 2021). The application follows a custom directory structure where the Laravel core is separated from public assets and has an integrated installer system.

---

## Root Directory Structure

```
Plus-agency/
├── assets/              # Public assets (CSS, JS, images, files)
├── core/                # Laravel application core
├── installer/           # Application installer
├── index.php           # Entry point for the application
├── .htaccess           # Apache rewrite rules
├── sw.js               # Service worker for PWA
└── version.json        # Application version information
```

---

## 1. Root Level Files

### `index.php`
- **Purpose**: Main entry point for the entire application
- **Function**:
  - Bootstraps the Laravel application from `core/bootstrap/app.php`
  - Loads Composer autoloader from `core/vendor/autoload.php`
  - Handles HTTP requests and sends responses
  - Note: Unlike standard Laravel, this is in the root directory, not in a public folder

### `.htaccess`
- **Purpose**: Apache web server configuration
- **Function**:
  - Enables URL rewriting
  - Routes all requests to `index.php`
  - Handles authorization headers
  - Removes trailing slashes from URLs

### `sw.js`
- **Purpose**: Service Worker for Progressive Web App functionality
- **Function**: Enables offline capabilities and improved performance

### `version.json`
- **Purpose**: Stores application version information
- **Content**: Version 3.2, Released: December 13, 2021

---

## 2. Assets Directory (`/assets/`)

Contains all public-facing static files organized by user type and function:

```
assets/
├── admin/           # Admin panel assets (CSS, JS, images)
├── user/            # User dashboard assets
├── front/           # Frontend/public website assets
├── lfm/             # Laravel File Manager assets
└── sitemaps/        # Generated sitemap files
```

**Purpose**: Serves static resources like stylesheets, JavaScript, images, and uploaded files.

---

## 3. Core Directory (`/core/`)

This is the main Laravel application directory containing all backend logic.

### 3.1 Application Layer (`/core/app/`)

Contains the business logic and models:

```
app/
├── Console/              # Artisan commands
│   ├── Commands/        # Custom console commands
│   └── Kernel.php       # Command scheduler
├── Exceptions/          # Exception handlers
├── Exports/             # Excel export classes
├── Http/                # HTTP layer
│   ├── Controllers/     # Request handlers
│   │   ├── Admin/      # Admin panel controllers
│   │   ├── Front/      # Frontend controllers
│   │   ├── User/       # User dashboard controllers
│   │   ├── Auth/       # Authentication controllers
│   │   └── Payment/    # Payment gateway controllers
│   ├── Middleware/      # HTTP middleware
│   ├── Requests/        # Form request validation
│   └── Helpers/         # Helper functions
│       ├── Helper.php  # General helper functions
│       └── Sections.php # Section-related helpers
├── Jobs/                # Queue jobs
├── Mail/                # Email templates (Mailable classes)
├── Notifications/       # Notification classes
├── Providers/           # Service providers
└── [Model Files]        # Eloquent models (at root of app/)
```

#### Key Model Files (Examples)
- `Admin.php` - Admin user model
- `Article.php`, `ArticleCategory.php` - Blog/article system
- `Course.php`, `CourseCategory.php`, `CoursePurchase.php` - Course management
- `Event.php`, `EventCategory.php`, `EventDetail.php` - Event system
- `Donation.php`, `DonationDetail.php` - Donation management
- `Blog.php`, `Bcategory.php` - Blog system
- `Coupon.php` - Discount coupon system
- `EmailTemplate.php` - Email template management
- `BasicSetting.php`, `BasicExtra.php` - Site settings
- And many more...

#### Custom Helper Files
Auto-loaded via `composer.json`:
- `Helper.php` - Contains utility functions like `setEnvironmentValue()`, `convertUtf8()`, `make_slug()`
- `Sections.php` - Section/content management helpers

### 3.2 Configuration (`/core/config/`)

Laravel configuration files for various services:

```
config/
├── app.php              # Application config (name, locale, timezone)
├── auth.php             # Authentication configuration
├── database.php         # Database connections
├── mail.php             # Email configuration
├── filesystems.php      # File storage configuration
├── cache.php            # Cache configuration
├── queue.php            # Queue configuration
├── services.php         # Third-party services
├── captcha.php          # reCAPTCHA configuration
├── paystack.php         # Paystack payment gateway
├── indipay.php          # Indian payment gateways
├── excel.php            # Excel import/export
├── lfm.php              # Laravel File Manager
├── installer.php        # Application installer config
└── ... (more configs)
```

### 3.3 Database (`/core/database/`)

Database-related files:

```
database/
├── migrations/          # Database schema migrations
├── seeds/              # Database seeders (sample data)
└── factories/          # Model factories for testing
```

**Purpose**: Version control for database schema and seed data for development/testing.

### 3.4 Resources (`/core/resources/`)

Frontend resources and views:

```
resources/
├── views/              # Blade template files
│   ├── admin/         # Admin panel views
│   ├── user/          # User dashboard views
│   ├── front/         # Public website views
│   ├── mail/          # Email templates
│   ├── pdf/           # PDF templates
│   ├── errors/        # Error pages (404, 500, etc.)
│   └── vendor/        # Third-party package views
├── lang/              # Language files (translations)
│   └── [75+ languages supported]
├── js/                # JavaScript source files
└── sass/              # SCSS/Sass stylesheets
```

**Purpose**: All visual templates, translations, and frontend source code.

### 3.5 Routes (`/core/routes/`)

Application routing:

```
routes/
├── web.php            # Web routes (main application routes - 95KB!)
├── api.php            # API routes
├── console.php        # Console routes (Artisan commands)
└── channels.php       # Broadcast channels
```

**Note**: `web.php` is particularly large (95KB), indicating extensive routing for all features.

### 3.6 Storage (`/core/storage/`)

Application storage:

```
storage/
├── app/                   # Application files
│   └── digital_products/ # Digital product files
├── framework/             # Framework generated files
│   ├── cache/            # Application cache
│   ├── sessions/         # Session files
│   └── views/            # Compiled Blade templates
└── logs/                  # Application logs
```

**Purpose**: File uploads, cache, sessions, compiled views, and logs.

### 3.7 Tests (`/core/tests/`)

Automated testing:

```
tests/
├── Feature/           # Feature tests
└── Unit/             # Unit tests
```

### 3.8 Bootstrap (`/core/bootstrap/`)

Application bootstrap:

```
bootstrap/
├── app.php           # Bootstrap the Laravel application
└── cache/            # Bootstrapped configuration cache
```

### 3.9 Vendor (`/core/vendor/`)

Third-party dependencies installed via Composer (83 packages).

### 3.10 Other Core Files

- `artisan` - Command-line interface for Laravel
- `composer.json` - PHP dependency management
- `package.json` - NPM dependency management
- `webpack.mix.js` - Asset compilation configuration
- `.env` - Environment configuration (database, mail, API keys)
- `.env.example` - Environment template
- `phpunit.xml` - PHPUnit testing configuration

---

## 4. Installer Directory (`/installer/`)

Application installation wizard:

```
installer/
├── css/              # Installer stylesheets
├── fonts/            # Installer fonts
└── img/              # Installer images
```

**Purpose**: Provides a web-based installation interface for initial setup.

---

## Application Flow

### 1. Request Lifecycle

```
User Request
    ↓
.htaccess (URL Rewriting)
    ↓
index.php (Entry Point)
    ↓
core/vendor/autoload.php (Composer Autoloader)
    ↓
core/bootstrap/app.php (Bootstrap Laravel)
    ↓
HTTP Kernel (Handle Request)
    ↓
Routes (web.php)
    ↓
Middleware
    ↓
Controllers
    ↓
Models (Database)
    ↓
Views (Blade Templates)
    ↓
Response
```

### 2. MVC Pattern

**Models** (`core/app/*.php`)
- Eloquent ORM models
- Located directly in `app/` directory
- Handle database interactions
- Examples: `Article.php`, `Course.php`, `User.php`

**Views** (`core/resources/views/`)
- Blade template engine
- Separated by user type (admin, user, front)
- Compiled and cached in `storage/framework/views/`

**Controllers** (`core/app/Http/Controllers/`)
- Handle HTTP requests
- Organized by section:
  - `Admin/` - Admin panel logic
  - `Front/` - Public website logic
  - `User/` - User dashboard logic
  - `Auth/` - Authentication logic
  - `Payment/` - Payment processing

---

## Key Features & Their Locations

### Authentication & Authorization
- **Controllers**: `core/app/Http/Controllers/Auth/`
- **Middleware**: `core/app/Http/Middleware/`
- **Config**: `core/config/auth.php`
- **Models**: `core/app/Admin.php`, `core/app/User.php`

### Content Management
- **Blog System**: `Article.php`, `ArticleCategory.php`, `Blog.php`
- **Pages**: `Page.php`, `Permalink.php`
- **FAQs**: `Faq.php`, `FAQCategory.php`
- **Portfolio**: `Portfolio.php`, `Pcategory.php`

### E-Learning
- **Models**: `Course.php`, `CourseCategory.php`, `CoursePurchase.php`, `CourseReview.php`
- **Controllers**: `core/app/Http/Controllers/Admin/CourseController.php`
- **Storage**: `core/storage/app/digital_products/`

### E-Commerce Features
- **Coupons**: `Coupon.php`
- **Packages**: `Package.php`, `PackageOrder.php`
- **Products**: `Product.php`, `Pcategory.php`

### Payment Integration
- **Controllers**: `core/app/Http/Controllers/Payment/`
- **Gateways**: Stripe, PayPal, Razorpay, Paytm, Instamojo, Mollie, Paystack
- **Config**: Various config files (paystack.php, indipay.php, etc.)

### Event Management
- **Models**: `Event.php`, `EventCategory.php`, `EventDetail.php`, `CalendarEvent.php`
- **Controllers**: `core/app/Http/Controllers/Admin/EventController.php`

### Donation System
- **Models**: `Donation.php`, `DonationDetail.php`
- **Controllers**: `core/app/Http/Controllers/Admin/DonationController.php`

### Email System
- **Templates**: `core/app/EmailTemplate.php`
- **Mailables**: `core/app/Mail/`
- **Config**: `core/config/mail.php`

### Multi-language Support
- **Location**: `core/resources/lang/`
- **Package**: `laravel-lang/lang` with 75+ languages
- **Models**: `Language.php`

### File Management
- **Package**: Laravel File Manager (unisharp/laravel-filemanager)
- **Assets**: `assets/lfm/`
- **Config**: `core/config/lfm.php`

### Backup System
- **Model**: `Backup.php`
- **Controller**: `core/app/Http/Controllers/Admin/BackupController.php`

### SEO & Sitemap
- **Package**: spatie/laravel-sitemap
- **Storage**: `assets/sitemaps/`

### Analytics & Tracking
- **Google Analytics**: Integrated in BasicSetting
- **Facebook Pixel**: Supported
- **Tawk.to Chat**: Supported

---

## Environment Configuration

The `.env` file controls:
- Database connection
- Mail server settings
- Payment gateway credentials
- API keys (Google, Facebook, etc.)
- Application settings
- Cache/session drivers
- Queue configuration

**Location**: `core/.env`

---

## Asset Compilation

Laravel Mix is used for compiling frontend assets:

**Configuration**: `core/webpack.mix.js`
**Source**: `core/resources/js/` and `core/resources/sass/`
**Compiled Output**: `assets/admin/`, `assets/user/`, `assets/front/`

---

## Database Structure

Migrations define the database schema:
- **Location**: `core/database/migrations/`
- **Execution**: `php artisan migrate`
- **Tables**: Users, Admins, Articles, Courses, Events, Donations, Orders, Settings, etc.

---

## Permissions & Security

### File Permissions
```
core/storage/          → 775 or 777 (writable)
core/bootstrap/cache/  → 775 or 777 (writable)
assets/               → 755 (readable)
```

### Security Features
- CSRF protection (Laravel default)
- XSS filtering (`masterro/laravel-xss-filter`)
- HTML purification (`mews/purifier`)
- ReCAPTCHA support (`anhskohbo/no-captcha`)
- SQL injection protection (Eloquent ORM)

---

## Maintenance & Management

### Artisan Commands
```bash
cd core
php artisan migrate          # Run migrations
php artisan db:seed          # Seed database
php artisan cache:clear      # Clear cache
php artisan config:clear     # Clear config cache
php artisan view:clear       # Clear view cache
php artisan queue:work       # Process queue jobs
```

### Cache System
- **Driver**: Configured in `.env`
- **Locations**:
  - `core/storage/framework/cache/`
  - `core/bootstrap/cache/`
- **Management**: Via admin panel cache controller

### Backup System
- Manual backup creation via admin panel
- Stored backups can be downloaded
- Database and file backups supported

---

## Third-Party Integrations

### Payment Gateways
- Stripe (Cartalyst)
- PayPal REST API
- Razorpay
- Paytm Wallet
- Instamojo
- Mollie
- Paystack
- Multiple Indian payment gateways (IndiPay)

### Social Authentication
- Laravel Socialite
- Facebook, Google, Twitter login

### Notifications
- Email notifications
- Web push notifications (laravel-notification-channels/webpush)

### Other Services
- Google Analytics
- Facebook Pixel
- Tawk.to Chat
- reCAPTCHA
- Disqus Comments
- WhatsApp Integration

---

## Custom Structure Notes

### Differences from Standard Laravel

1. **Public Directory**:
   - Standard Laravel has a `public/` folder as web root
   - This app uses the root directory as web root with `index.php` at root

2. **Core Directory**:
   - All Laravel files are in `core/` subdirectory
   - Allows separation of framework from public assets

3. **Assets Organization**:
   - Public assets are in root `assets/` directory
   - Separated by admin/user/front for better organization

4. **Model Location**:
   - Models are in `core/app/` root (not in `app/Models/`)
   - Follows older Laravel convention

5. **Helper Functions**:
   - Custom helpers auto-loaded via Composer
   - Located in `app/Http/Helpers/`

---

## Development Workflow

### Adding New Features

1. **Create Model**: `core/app/ModelName.php`
2. **Create Migration**: `core/database/migrations/`
3. **Create Controller**: `core/app/Http/Controllers/[Area]/`
4. **Add Routes**: `core/routes/web.php`
5. **Create Views**: `core/resources/views/[area]/`
6. **Add Assets**: `assets/[area]/`

### Testing

1. **Create Tests**: `core/tests/Feature/` or `core/tests/Unit/`
2. **Run Tests**: `php artisan test` or `vendor/bin/phpunit`

---

## Performance Optimization

### Caching
- **Config Cache**: `php artisan config:cache`
- **Route Cache**: `php artisan route:cache`
- **View Cache**: Automatic in production
- **Application Cache**: Redis/Memcached support

### Queue System
- Background job processing
- Email sending
- Notification dispatching
- **Worker**: `php artisan queue:work`

### Asset Optimization
- CSS/JS minification via Laravel Mix
- Image optimization recommended
- CDN integration supported

---

## Troubleshooting

### Common Issues

1. **Permission Errors**
   - Check `storage/` and `bootstrap/cache/` permissions
   - Should be writable (775 or 777)

2. **500 Internal Server Error**
   - Check `core/storage/logs/laravel.log`
   - Verify `.env` configuration
   - Clear cache: `php artisan cache:clear`

3. **Database Connection**
   - Verify `.env` database credentials
   - Check database server is running
   - Test connection: `php artisan tinker` → `DB::connection()->getPdo();`

4. **Missing Assets**
   - Run `npm install` and `npm run dev`
   - Check asset paths in views
   - Verify web server can access `assets/` directory

---

## Version Information

- **Application Version**: 3.2
- **Release Date**: December 13, 2021
- **Laravel Version**: 8.x
- **PHP Requirement**: ^7.3 or ^8.0
- **Database**: MySQL/MariaDB (configured in .env)

---

## File Count Summary

- **Controllers**: 100+ controllers across Admin/Front/User/Payment
- **Models**: 80+ Eloquent models
- **Views**: 500+ Blade templates
- **Migrations**: Database schema migrations
- **Languages**: 75+ language packs
- **Routes**: Extensive routing in web.php (95KB)
- **Dependencies**: 80+ Composer packages

---

## Conclusion

This Laravel application follows a custom but well-organized structure optimized for a multi-purpose business/agency platform. The separation of the Laravel core into a `core/` directory and public assets into `assets/` provides clear organization while maintaining Laravel conventions for the application logic itself.

The application supports:
- Multi-language content
- Multiple payment gateways
- Course/training management
- Event management
- Blog/article system
- Donation system
- File management
- Email campaigns
- And much more...

For modifications or customizations, always work within the `core/` directory for backend logic and `assets/` for frontend resources, following Laravel's MVC pattern and PSR-4 autoloading standards.

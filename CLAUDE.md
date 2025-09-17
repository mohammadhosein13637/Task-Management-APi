# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

**Start Development Environment:**
```bash
composer run-script dev
```
This runs the full development stack: Laravel server, queue worker, logs viewer, and Vite dev server concurrently.

**Individual Services:**
- `php artisan serve` - Start Laravel development server
- `npm run dev` - Start Vite development server for frontend assets
- `npm run build` - Build production frontend assets
- `php artisan queue:listen --tries=1` - Start queue worker
- `php artisan pail --timeout=0` - View application logs

**Testing:**
- `php artisan test` - Run all tests via Artisan
- `vendor/bin/phpunit` - Run PHPUnit directly
- `vendor/bin/phpunit tests/Unit` - Run unit tests only
- `vendor/bin/phpunit tests/Feature` - Run feature tests only

**Code Quality:**
- `vendor/bin/pint` - Format code using Laravel Pint

## Architecture

**Laravel 11 Application Structure:**
- **Models**: Located in `app/Models/` - Currently includes User model with authentication features
- **Controllers**: Located in `app/Http/Controllers/` - Base Controller class provided
- **Routes**: Web routes in `routes/web.php`, console commands in `routes/console.php`
- **Database**: Migrations in `database/migrations/`, factories in `database/factories/`, seeders in `database/seeders/`
- **Tests**: Unit tests in `tests/Unit/`, Feature tests in `tests/Feature/`

**Frontend Assets:**
- **Build System**: Vite with Laravel plugin
- **CSS Framework**: TailwindCSS configured
- **Asset Sources**: `resources/css/app.css` and `resources/js/app.js`
- **Configuration**: `vite.config.js`, `tailwind.config.js`, `postcss.config.js`

**API Documentation:**
- Swagger/OpenAPI documentation available at `/api/documentation`
- Generate docs: `php artisan l5-swagger:generate`

**Database:**
- Default setup uses SQLite (database file created automatically)
- Standard Laravel migration structure with users, cache, personal_access_tokens, and tasks tables
- User and Task factories provided for testing/seeding

**API Endpoints:**
- **Authentication**: `/api/register`, `/api/login`, `/api/logout`, `/api/me`
- **Tasks**: `/api/tasks` (full CRUD), `/api/tasks/{task}/complete`, `/api/tasks/{task}/incomplete`
- **Import**: `/api/import/tasks` (CSV upload), `/api/import/template` (get CSV template)
- **Admin**: Admin users can access all tasks and users

**Security Features:**
- Laravel Sanctum API authentication with 30-day token expiration
- Input validation and sanitization on all endpoints
- Role-based access control (user/admin)
- CSRF protection for web routes
- SQL injection protection via Eloquent ORM

**Development Environment:**
- PHP 8.2+ required
- Uses Laravel 11 framework conventions
- Includes Laravel Pail for log viewing, Laravel Sail for Docker support
- PHPUnit configured for testing with separate Unit/Feature test suites
- Comprehensive test coverage for all API endpoints
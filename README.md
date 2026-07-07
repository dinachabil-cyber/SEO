# SEO Factory

## Description

SEO Factory is a powerful Symfony-based application designed for creating and managing SEO-optimized websites with an intuitive page builder interface. It provides a comprehensive admin panel for managing sites, pages, and dynamic content sections, making it easy to build professional websites without extensive technical knowledge.

## Features

### Core Functionality
- **Multi-Site Management**: Create and manage multiple websites from a single admin panel
- **Page Builder**: Drag-and-drop interface for building pages with various section types
- **SEO Optimization**: Built-in SEO fields (meta title, meta description, meta keywords, H1 tags)
- **Dynamic Content**: Support for variables and dynamic content placeholders
- **Template System**: Reusable templates for quick site creation

### Section Types
The page builder supports 10 different section types:
- **Header**: Site navigation and branding
- **Hero**: Hero sections with customizable fields (text, images, buttons, badges)
- **Body**: Main content sections
- **Image**: Image-focused sections
- **Cards**: Card-based content layouts
- **Cards Premium**: Enhanced card layouts with premium styling
- **FAQ**: Frequently asked questions sections
- **Form**: Contact and lead generation forms
- **CTA**: Call-to-action sections
- **Footer**: Site footer with links and information

### Admin Features
- **User Management**: Admin user creation and password management
- **Site Dashboard**: Overview of all sites with statistics
- **Page Management**: Create, edit, and delete pages
- **Section Builder**: Visual section editor with live preview
- **Password Reset**: Secure password reset functionality
- **Site Duplication**: Clone existing sites for quick setup

### Technical Features
- **Doctrine ORM**: Robust database management with migrations
- **Twig Templating**: Flexible and powerful templating engine
- **Stimulus Controllers**: Modern JavaScript framework for interactive UI
- **Bootstrap 5**: Responsive and modern UI components
- **Asset Mapper**: Efficient asset management
- **Security**: Built-in authentication and authorization

## Architecture

### Entity Structure
```
Site (1) ──→ (N) Page (1) ──→ (N) PageSection
```

- **Site**: Represents a website with domain, locale, and configuration
- **Page**: Represents a page within a site with SEO metadata
- **PageSection**: Represents a section within a page with type and data

### Section Data Storage
Sections store their configuration in JSON format, allowing flexible and dynamic content management without database schema changes.

### Hero Fields System
The Hero section uses a configurable fields system defined in [`src/Config/HeroFieldsConfig.php`](src/Config/HeroFieldsConfig.php:1), providing:
- Centralized field definitions
- Tab-based organization (Content, Media, Layout, Style, Buttons)
- Easy extensibility for new fields
- Backward compatibility with legacy keys

## Installation

### Prerequisites

- PHP 8.4 or higher
- Composer
- MySQL/PostgreSQL database
- Symfony CLI (optional but recommended)

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd seo_project
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env .env.local
   ```
   Edit `.env.local` to configure your database connection:
   ```
   DATABASE_URL="mysql://user:password@127.0.0.1:3306/seo_factory"
   ```

4. **Run database migrations**
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

5. **Create admin user**
   ```bash
   php bin/console app:create-admin-user
   ```

6. **Start the development server**
   ```bash
   symfony serve
   ```
   Or using PHP's built-in server:
   ```bash
   php -S localhost:8000 -t public
   ```

## Usage

### Accessing the Admin Panel

1. Navigate to `http://localhost:8000/admin`
2. Log in with your admin credentials
3. You'll be redirected to the dashboard

### Creating a Site

1. From the dashboard, click "New Site"
2. Fill in the site details:
   - **Domain**: The website domain (e.g., `example.com`)
   - **Default Locale**: Default language (e.g., `fr`, `en`)
   - **Hosting**: Hosting provider information
   - **Database Name**: Database name for the site
   - **Technology**: Technology stack used
3. Click "Create Site"

### Creating a Page

1. Navigate to a site's dashboard
2. Click "Create Page"
3. Fill in the page details:
   - **Slug**: URL-friendly identifier (e.g., `home`, `about-us`)
   - **Meta Title**: SEO title for search engines
   - **Meta Description**: SEO description for search engines
   - **Meta Keywords**: Comma-separated keywords
   - **H1**: Main heading for the page
   - **Google Ads ID**: Optional Google Ads tracking ID
4. Click "Create Page"

### Building a Page with Sections

1. Navigate to a page's builder
2. Click "Add Section"
3. Select a section type (Header, Hero, Body, etc.)
4. Configure the section fields:
   - **Content**: Text, headings, buttons
   - **Media**: Images, alt text
   - **Layout**: Positioning, sizing
   - **Style**: Colors, spacing
   - **Buttons**: Button-specific styling
5. Click "Save Section"
6. Repeat for additional sections
7. Drag sections to reorder them

### Duplicating a Site

1. Navigate to a site's dashboard
2. Click "Duplicate"
3. Fill in the new site details
4. Click "Duplicate Site"

## Configuration

### Environment Variables

Key environment variables in `.env` or `.env.local`:

```bash
# Database
DATABASE_URL="mysql://user:password@127.0.0.1:3306/seo_factory"

# Application
APP_ENV=dev
APP_SECRET=your-secret-key

# Mailer (for password reset)
MAILER_DSN=smtp://localhost
```

### Security Configuration

Security settings are configured in [`config/packages/security.yaml`](config/packages/security.yaml:1):
- Password hashing algorithm
- User provider configuration
- Firewall rules
- Access control rules

### Doctrine Configuration

Database and ORM settings in [`config/packages/doctrine.yaml`](config/packages/doctrine.yaml:1):
- Database connection
- Migrations configuration
- Entity mapping

## Development

### Project Structure

```
seo_project/
├── assets/              # Frontend assets (JS, CSS)
├── bin/                 # Console commands
├── config/              # Symfony configuration
├── docs/                # Documentation
├── migrations/          # Database migrations
├── public/              # Web root
├── src/                 # Application source code
│   ├── Command/         # Console commands
│   ├── Config/          # Configuration classes
│   ├── Controller/      # Controllers
│   ├── Entity/          # Doctrine entities
│   ├── Form/            # Symfony forms
│   ├── Repository/      # Doctrine repositories
│   └── Security/        # Security voters
├── templates/           # Twig templates
│   ├── admin/           # Admin panel templates
│   ├── front/           # Frontend templates
│   └── dashboard/       # Dashboard templates
└── tests/               # PHPUnit tests
```

### Key Controllers

- **[`SiteController`](src/Controller/SiteController.php:1)**: Site CRUD operations
- **[`PageController`](src/Controller/PageController.php:1)**: Page CRUD operations
- **[`PageBuilderController`](src/Controller/PageBuilderController.php:1)**: Section management
- **[`FrontController`](src/Controller/FrontController.php:1)**: Frontend page rendering
- **[`DashboardController`](src/Controller/DashboardController.php:1)**: Admin dashboard

### Adding New Section Types

1. Add the type to [`SectionTypes::ALL`](src/Entity/SectionTypes.php:5)
2. Create a form builder in [`PageSectionType`](src/Form/PageSectionType.php:1)
3. Create admin template in `templates/admin/section/partials/`
4. Create frontend template in `templates/front/sections/`

### Adding Hero Fields

See [`docs/HERO_FIELDS_SYSTEM.md`](docs/HERO_FIELDS_SYSTEM.md:1) for detailed instructions on adding new Hero fields.

## API Reference

### Console Commands

- `app:create-admin-user`: Create a new admin user
- `app:create-test-site`: Create a test site for development
- `app:update-admin-password`: Update admin user password
- `app:clear-form-fields`: Clear form field cache

### Routes

#### Admin Routes
- `GET /admin`: Dashboard
- `GET /admin/site`: Site list
- `GET /admin/site/new`: Create site form
- `GET /admin/site/{id}`: Site details
- `GET /admin/site/{id}/edit`: Edit site form
- `GET /admin/site/{siteId}/page/{pageId}/builder`: Page builder

#### Frontend Routes
- `GET /`: Home page
- `GET /{slug}`: Dynamic page rendering

## Testing

### Running Tests

```bash
# Run all tests
php bin/phpunit

# Run specific test suite
php bin/phpunit --testsuite=unit

# Run with coverage
php bin/phpunit --coverage-html var/coverage
```

### Test Configuration

Test configuration is in [`phpunit.dist.xml`](phpunit.dist.xml:1).

## Deployment

### Production Setup

1. **Set environment to production**
   ```bash
   APP_ENV=prod
   APP_SECRET=<generate-a-secret>
   ```

2. **Install dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Clear and warm up cache**
   ```bash
   php bin/console cache:clear --env=prod
   ```

4. **Run migrations**
   ```bash
   php bin/console doctrine:migrations:migrate --env=prod
   ```

5. **Dump assets**
   ```bash
   php bin/console asset-map:compile --env=prod
   ```

### Docker Deployment

The project includes Docker configuration files:
- [`compose.yaml`](compose.yaml:1): Main Docker Compose configuration
- [`compose.override.yaml`](compose.override.yaml:1): Development overrides

```bash
# Start containers
docker-compose up -d

# Run migrations
docker-compose exec app php bin/console doctrine:migrations:migrate
```

## Troubleshooting

### Common Issues

1. **Database connection errors**
   - Verify `DATABASE_URL` in `.env.local`
   - Ensure database server is running
   - Check database credentials

2. **Permission errors**
   - Ensure `var/` directory is writable
   - Set proper permissions: `chmod -R 775 var/`

3. **Asset loading issues**
   - Run `php bin/console asset-map:install`
   - Clear cache: `php bin/console cache:clear`

4. **Migration errors**
   - Check database schema: `php bin/console doctrine:schema:validate`
   - Review migration files in `migrations/`

### Debug Mode

Enable debug mode for development:
```bash
APP_ENV=dev
APP_DEBUG=1
```

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Commit your changes: `git commit -am 'Add my feature'`
4. Push to the branch: `git push origin feature/my-feature`
5. Submit a pull request

### Coding Standards

- Follow PSR-12 coding standards
- Use PHPStan for static analysis
- Write tests for new features
- Update documentation as needed

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE:1) file for details.

## Support

For issues and questions:
- Create an issue in the repository
- Check the [documentation](docs/)
- Review the [Hero Fields System](docs/HERO_FIELDS_SYSTEM.md:1) guide

## Changelog

### Version 1.0.0
- Initial release
- Multi-site management
- Page builder with 10 section types
- Admin panel with user management
- SEO optimization features
- Hero fields system
- Password reset functionality
- Site duplication feature

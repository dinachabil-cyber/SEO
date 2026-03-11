# SEO Factory

## Description

SEO Factory is a Symfony-based application that allows users to create and manage SEO-optimized websites. It provides a simple interface for creating sites, pages, and templates, and it supports dynamic content through variables.

## Installation

### Prerequisites

- PHP 8.4 or higher
- Symfony 8.0 or higher
- Doctrine ORM
- Twig templating engine
- Symfony Flex

### Installation Steps

1. Clone the repository
2. Install dependencies: `composer install`
3. Configure the database: Edit `env` file to match your database settings
4. Run migrations: `symfony console doctrine:migrations:migrate`
5. Start the server: `symfony serve`

## Usage

### Creating a Site

1. Navigate to `/admin`
2. Click on "New Site"
3. Fill in the site details and select a template
4. Click "Create Site"

### Creating a Page

1. Navigate to the site dashboard
2. Click on "Create Page"
3. Fill in the page details
4. Click "Create Page"

### Duplicating a Site

1. Navigate to the site dashboard
2. Click on "Duplicate"
3. Fill in the new site details
4. Click "Duplicate Site"

## Templates

Templates are YAML files stored in the `templates_library` directory. Each template contains:

- Site information (name, description, default locale)
- Variables (dynamic content placeholders)
- Pages (slug, meta title, meta description, H1, published status)

Example template structure:

```yaml
name: "Insurance Auto (France)"
description: "Template for French auto insurance websites with prebuilt SEO structure"
defaultLocale: "fr"
variables:
  - brand: "Nom de l'assureur"
  - city: "Ville (ex: Paris, Lyon, Marseille)"
  - phone: "Numéro de téléphone (ex: 01 23 45 67 89)"
  - domain: "Nom de domaine (ex: monassuranceauto.fr)"
  - offer: "Offre spéciale (ex: -10% pour les jeunes conducteurs)"

pages:
  home:
    slug: "/"
    metaTitle: "Assurance Auto {{city}} - {{brand}} - Devis en Ligne"
    metaDescription: "Devis assurance auto {{city}} en 2min. {{brand}} propose les meilleurs tarifs pour votre voiture. {{offer}}"
    h1: "Meilleure Assurance Auto {{city}} - Devis en Ligne chez {{brand}}"
    isPublished: true
```

## Services

### SiteClonerService

The SiteClonerService provides methods to duplicate sites and create sites from templates.

#### duplicateSite()

Duplicates a site with all its pages.

```php
public function duplicateSite(Site $site): Site
```

#### createSiteFromTemplate()

Creates a site from a template.

```php
public function createSiteFromTemplate(string $templateKey, array $variables): Site
```

## Console Commands

### app:site:create-from-template

Creates a new site from a template.

```bash
php bin/console app:site:create-from-template auto_france --brand="Nom de l'assureur" --city="Paris" --phone="01 23 45 67 89" --domain="monassuranceauto.fr" --offer="-10% pour les jeunes conducteurs"
```

### app:site:duplicate

Duplicates an existing site.

```bash
php bin/console app:site:duplicate 1
```

### app:templates:list

Lists all available templates.

```bash
php bin/console app:templates:list
```

## Architecture

### Entities

- **Site**: Represents a website
- **Page**: Represents a page on a website

### Controllers

- **AdminController**: Handles admin dashboard operations
- **SiteController**: Handles site operations
- **FrontController**: Handles front-end operations

### Services

- **SiteClonerService**: Handles site duplication and template-based creation

### Repositories

- **SiteRepository**: Handles site data access
- **PageRepository**: Handles page data access

## Contributing

Contributions are welcome. Please follow these steps:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run the tests
5. Submit a pull request

## License

MIT

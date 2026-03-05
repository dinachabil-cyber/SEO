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



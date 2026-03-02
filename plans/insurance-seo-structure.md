# SEO Structure for Professional Insurance Website in France

## Overview

This document outlines a comprehensive SEO structure for a professional insurance website targeting the French market. The structure includes site architecture, page SEO elements, target keywords, and internal linking strategy.

## Site Structure

### 1. Home Page
- **URL**: `/`
- **SEO Title**: Assurance Profesionnelle en France - Devis & Comparateurs d'Assurance
- **H1 Suggestion**: Meilleurs Assurances Profesionnelles en France - Devis en Ligne
- **Target Keywords**:
  - assurance professionnelle France
  - devis assurance professionnelle
  - comparateur assurance professionnelle
  - assurance pour professionnels
  - assurance entreprise France

### 2. Services Page
- **URL**: `/services`
- **SEO Title**: Services d'Assurance Professionnelle - Tous Types d'Assurance pour Entreprises
- **H1 Suggestion**: Services d'Assurance Professionnelle - Solutions Complètes pour Votre Entreprise
- **Target Keywords**:
  - services d'assurance professionnelle
  - assurance pour artisans
  - assurance pour commerçants
  - assurance pour professions libérales
  - assurance responsabilité civile professionnelle

### 3. Blog Page
- **URL**: `/blog`
- **SEO Title**: Blog Assurance Professionnelle - Conseils & Actualités de l'Assurance
- **H1 Suggestion**: Blog Assurance Professionnelle - Conseils et Actualités pour les Professionnels
- **Target Keywords**:
  - blog assurance professionnelle
  - conseils assurance entreprise
  - actualités assurance professionnelle
  - réglementations assurance France
  - astuces pour réduire son assurance

### 4. FAQ Page
- **URL**: `/faq`
- **SEO Title**: FAQ Assurance Professionnelle - Réponses aux Questions Fréquentes
- **H1 Suggestion**: FAQ Assurance Professionnelle - Toutes vos Questions Répondues
- **Target Keywords**:
  - FAQ assurance professionnelle
  - questions sur l'assurance entreprise
  - quoi savoir sur l'assurance professionnelle
  - prix de l'assurance professionnelle
  - comment choisir son assurance professionnelle

### 5. Contact Page
- **URL**: `/contact`
- **SEO Title**: Contact Assurance Professionnelle - Demander un Devis ou un Renseignement
- **H1 Suggestion**: Contactez-Nous pour Votre Assurance Professionnelle
- **Target Keywords**:
  - contact assurance professionnelle
  - demander un devis assurance
  - renseignement assurance entreprise
  - service client assurance
  - numéro de téléphone assurance professionnelle

## Internal Linking Strategy

### Main Navigation Links
- Home page links to: Services, Blog, FAQ, Contact
- Services page links to: Home, Blog, FAQ, Contact
- Blog page links to: Home, Services, FAQ, Contact
- FAQ page links to: Home, Services, Blog, Contact
- Contact page links to: Home, Services, Blog, FAQ

### Cross-Linking Strategy
- **Home Page** should link to key service pages and blog posts
- **Services Page** should link to specific service subpages and related blog articles
- **Blog Articles** should link to related articles, service pages, and the FAQ section
- **FAQ Page** should link to relevant service pages and blog posts
- **Contact Page** should link to all main navigation pages and relevant services

### Content Links
- Blog posts should include links to related articles
- Service pages should include links to related services
- All pages should include at least 2-3 internal links to other relevant pages

## Migration Guide

To implement this SEO structure, follow these steps:

1. **Update Database Schema**: Run the migration to add SEO fields to the Page entity
2. **Create Insurance Website**: Add a new Site entity with name "Assurance Professionnelle France"
3. **Add Pages**: Create 5 Page entities with the URLs and SEO data from this document
4. **Update Content**: Ensure each page has content that matches the target keywords
5. **Implement Internal Links**: Add internal links according to the strategy outlined

## Maintenance

- Regularly update target keywords based on search trends
- Add new blog posts to keep content fresh
- Review and update internal links as the website grows
- Monitor SEO performance using tools like Google Analytics and Search Console

## Files Modified

1. [`src/Entity/Page.php`](src/Entity/Page.php) - Added SEO fields
2. [`src/Form/PageType.php`](src/Form/PageType.php) - Updated form to include SEO fields
3. [`templates/page/show.html.twig`](templates/page/show.html.twig) - Updated to display new fields
4. [`templates/page/index.html.twig`](templates/page/index.html.twig) - Updated to include new fields
5. [`migrations/Version20260302142700.php`](migrations/Version20260302142700.php) - Migration for database schema update

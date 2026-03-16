# Improve Admin "Sites" Table

## Summary

This plan outlines the changes needed to enhance the Admin "Sites" table in the Symfony admin dashboard according to the requirements.

## Current State Analysis

The Sites table already has most of the required columns:
- Domain
- Locale (defaultLocale)
- Hosting
- Database (databaseName, databasePassword)
- Technology
- Published At
- Creator (user)
- Pages (pageCount)
- Imprint (companyName, address, phone, email, legalRepresentative, registrationNumber)
- Status
- Actions

However, there are some improvements needed to match the exact requirements.

## Changes Needed

### 1. Fix Column Order

Current order: Domain → Locale → Hosting → Database → Technology → Published At → Creator → Pages → Imprint → Status → Actions

Required order: Domain → Locale → **Status** → Hosting → Database → Technology → Published At → Creator → Pages → Imprint → Actions

### 2. Published At Date Format

Current format uses `{{ site.publishedAt|format_datetime }}` which needs to be changed to "MMM D, YYYY - HH:mm" format.

### 3. Database Password Security

- The `databasePassword` is currently stored using `password_hash()` which is not suitable for storing passwords that need to be retrieved
- Need to implement proper encryption/decryption for database passwords

### 4. Database Credentials Modal

- The modal currently shows the raw hashed password, which is useless
- Need to fix the password visibility toggle functionality
- Need to ensure proper decryption when displaying the password

### 5. View Credentials Button

Add a "View Credentials" button to the Actions column that opens the database credentials modal.

### 6. Page Count Calculation

Ensure the `pageCount` property is correctly maintained when pages are added/removed.

## Implementation Steps

### Step 1: Update Database Password Encryption

1. Replace the current `password_hash()` implementation with proper encryption
2. Add methods for encrypting and decrypting database passwords
3. Update the `setDatabasePassword()` method
4. Update the `getDatabasePassword()` method to handle decryption

### Step 2: Fix Published At Date Format

1. Change the Twig filter from `format_datetime` to a custom format
2. Use `{{ site.publishedAt|date('M d, Y - H:i') }}`

### Step 3: Reorder Table Columns

1. Move the Status column to the 3rd position in the Twig template
2. Update the table header and body to reflect the new order

### Step 4: Improve Database Credentials Modal

1. Fix the password visibility toggle
2. Ensure the password is properly decrypted when displayed
3. Add proper error handling if decryption fails

### Step 5: Add View Credentials Button

1. Add a new button to the Actions column
2. Link the button to the database credentials modal

### Step 6: Verify Page Count Logic

1. Ensure the `pageCount` property is correctly incremented/decremented
2. Add tests to verify the page count functionality

## Files to Modify

1. `src/Entity/Site.php` - Update password encryption, add decryption method
2. `templates/admin/site/index.html.twig` - Update column order, date format, add button
3. `src/Repository/SiteRepository.php` - Ensure proper hydration of user relation
4. `tests/Entity/SiteTest.php` - Add tests for new functionality

## Security Considerations

1. Database passwords must be properly encrypted at rest
2. Decryption should only happen when needed and never stored in plain text
3. Access to credentials should be restricted to the site creator
4. The credentials modal should handle permission checks correctly

## Migration

No database schema changes are needed, but existing passwords will need to be re-encrypted if the encryption method changes.

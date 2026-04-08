# Hero Fields System - Documentation

## Overview

This document explains the Hero fields system implemented to provide a clear and maintainable way to add new input fields to the Hero section.

## Where Hero Fields Are Defined

### 1. Configuration: `src/Config/HeroFieldsConfig.php`

This is the **single source of truth** for all Hero field definitions. Each field is defined with:

- **name**: The field key (used in JSON data storage)
- **type**: Symfony form type class (TextType::class, CheckboxType::class, etc.)
- **tab**: Which admin tab it belongs to (Content, Media, Layout, Style, Buttons)
- **options**: Additional form field options (label, required, choices, etc.)
- **legacy_key**: Optional alternative keys for backward compatibility

### 2. Form Definition: `src/Form/PageSectionType.php`

The Hero section fields are defined in the `case 'hero':` section. Each field is added to the Symfony form with:
- Data loading from existing data (via `HeroFieldsConfig::resolveValue()`)
- Proper type mapping to Symfony form types

### 3. Admin Rendering: `templates/admin/section/partials/_form.html.twig`

The admin UI uses tabs to organize Hero fields:
- **Content Tab**: Text fields, buttons, badges
- **Media Tab**: Images, alt text
- **Layout Tab**: Positioning, sizing
- **Style Tab**: Colors, spacing
- **Buttons Tab**: Button-specific styling

### 4. Frontend Rendering: `templates/front/sections/_hero.html.twig`

The Twig template renders all Hero fields using `data.field_name` notation.

---

## How to Add a New Hero Field

### Step 1: Add field to HeroFieldsConfig.php

Add the field definition to the appropriate tab method in `HeroFieldsConfig.php`:

```php
// Example: Adding a "featured_text" field to Content tab
'featured_text' => [
    'type' => TextType::class,
    'options' => [
        'label' => 'Featured Text',
        'required' => false,
        'attr' => ['placeholder' => 'Enter featured text'],
    ],
],
```

### Step 2: Add field to PageSectionType.php

Add the form field to the `case 'hero':` section:

```php
->add('featured_text', TextType::class, [
    'label' => 'Featured Text',
    'required' => false,
    'data' => HeroFieldsConfig::resolveValue($existingData, 'featured_text', HeroFieldsConfig::getAllFields()['featured_text'] ?? []),
    'attr' => ['placeholder' => 'Enter featured text'],
])
```

### Step 3: Add field to frontend template (if needed)

Add rendering logic in `_hero.html.twig`:

```twig
{% if data.featured_text is defined and data.featured_text %}
    <div class="featured-text">{{ data.featured_text }}</div>
{% endif %}
```

---

## Field Types and Their Symfony Equivalents

| Config Type | Symfony Form Type | HTML Input |
|-------------|-------------------|------------|
| `TextType::class` | TextType | `<input type="text">` |
| `TextareaType::class` | TextareaType | `<textarea>` |
| `CheckboxType::class` | CheckboxType | `<input type="checkbox">` |
| `ChoiceType::class` | ChoiceType | `<select>` |
| `ColorType::class` | ColorType | `<input type="color">` |
| `HiddenType::class` | HiddenType | `<input type="hidden">` |

---

## Tab Organization

Fields are grouped by purpose:

### Content Tab (`getContentFields()`)
- Text content: `hero_title`, `hero_subtitle`, `badge_text`, `description`
- New fields: `top_text`, `phone_number`, `show_form`, `form_title`, `form_subtitle`
- Buttons: `primary_button_text`, `primary_button_url`, `secondary_button_text`, `secondary_button_url`

### Media Tab (`getMediaFields()`)
- Images: `hero_image_url`, `mobile_image_url`, `image_alt_text`, `show_image`
- New fields: `left_image`

### Layout Tab (`getLayoutFields()`)
- Structure: `layout_type`, `text_alignment`, `content_width`, `section_height`, `vertical_alignment`, `column_gap`

### Style Tab (`getStyleFields()`)
- Colors: `background_color`, `background_gradient`, `hero_text_color`, `title_color`, `subtitle_color`, `description_color`, `card_background_color`
- Spacing: `padding_top`, `padding_bottom`, `margin_top`, `margin_bottom`
- Other: `border_radius`, `box_shadow`

### Buttons Tab (`getButtonsFields()`)
- Primary: `primary_button_background_color`, `primary_button_text_color`, `primary_button_border_color`, `primary_button_border_radius`, `primary_button_style`
- Secondary: Same as above with `secondary_` prefix

---

## Data Storage

Hero fields are stored in the `page_sections.data` JSON column:

```json
{
    "hero_title": "Welcome",
    "hero_subtitle": "Subtitle text",
    "top_text": "New badge text",
    "phone_number": "+1 234 567 8900",
    "show_form": true,
    "form_title": "Contact Us",
    "form_subtitle": "We'd love to hear from you",
    "left_image": "/images/hero-left.jpg",
    "background_color": "#ffffff",
    ...
}
```

---

## Backward Compatibility

The system supports legacy field names via `legacy_key` in field configuration:

```php
'hero_title' => [
    'type' => TextType::class,
    'options' => ['label' => 'Hero Title', 'required' => true],
    'legacy_key' => 'title', // Also accepts "title" as alternative
],
```

When loading data, `HeroFieldsConfig::resolveValue()` checks:
1. Primary key (e.g., `hero_title`)
2. Legacy key (e.g., `title`)
3. Default value

---

## New Fields Added

The following fields have been added to demonstrate the system:

| Field Name | Type | Tab | Description |
|------------|------|-----|--------------|
| `top_text` | text | Content | Badge text at top of hero |
| `phone_number` | text | Content | Phone number with click-to-call |
| `show_form` | checkbox | Content | Toggle contact form visibility |
| `form_title` | text | Content | Custom form title |
| `form_subtitle` | textarea | Content | Custom form subtitle |
| `left_image` | text/media | Media | Left-side image for split layouts |

---

## Accessing Fields in Templates

In Twig templates, access Hero fields via the `data` variable:

```twig
{# Simple text field #}
{{ data.hero_title }}

{# Conditional rendering #}
{% if data.show_form %}
    {# Render form #}
{% endif %}

{# With default value #}
{{ data.phone_number|default('+1 234 567 8900') }}
```

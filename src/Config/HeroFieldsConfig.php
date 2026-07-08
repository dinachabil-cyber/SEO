<?php

namespace App\Config;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * HeroFieldsConfig - Central configuration for all Hero section fields
 * 
 * This class provides a single source of truth for defining Hero fields.
 * Each field is defined with:
 * - name: The field key (used in JSON data)
 * - type: Symfony form type class
 * - tab: Which admin tab it belongs to (Content, Media, Layout, Style, Buttons)
 * - options: Additional form field options (label, choices, etc.)
 * 
 * HOW TO ADD A NEW HERO FIELD:
 * 1. Add the field definition to the appropriate tab array below
 * 2. The field will automatically appear in the admin form
 * 3. The field will be saved in page_sections.data JSON
 * 4. The field will be available in Twig templates via data.field_name
 * 
 * EXAMPLE - Adding a new "phone_number" field to Content tab:
 * ```
 * 'phone_number' => [
 *     'type' => TextType::class,
 *     'options' => [
 *         'label' => 'Phone Number',
 *         'required' => false,
 *     ]
 * ],
 * ```
 */
class HeroFieldsConfig
{
    // Tab constants for organization
    public const TAB_BLOCKS = 'blocks';
    public const TAB_CONTENT = 'content';
    public const TAB_MEDIA = 'media';
    public const TAB_LAYOUT = 'layout';
    public const TAB_STYLE = 'style';
    public const TAB_BUTTONS = 'buttons';

    /**
     * Get all Hero fields grouped by tab
     * This is the single source of truth for Hero field definitions
     */
    public static function getFieldsByTab(): array
    {
        return [
            self::TAB_CONTENT => self::getContentFields(),
            self::TAB_MEDIA => self::getMediaFields(),
            self::TAB_LAYOUT => self::getLayoutFields(),
            self::TAB_STYLE => self::getStyleFields(),
            self::TAB_BUTTONS => self::getButtonsFields(),
        ];
    }

    /**
     * Get all fields as a flat array (name => config)
     */
    public static function getAllFields(): array
    {
        $fields = [];
        foreach (self::getFieldsByTab() as $tabFields) {
            $fields = array_merge($fields, $tabFields);
        }
        return $fields;
    }

    /**
     * Get field names only (for validation, etc.)
     */
    public static function getFieldNames(): array
    {
        return array_keys(self::getAllFields());
    }

    /**
     * CONTENT TAB FIELDS
     * Basic text content - hero_title, subtitle, badges, descriptions
     */
    public static function getContentFields(): array
    {
        return [
// Legacy fields (kept for backward compatibility)
            'hero_title' => [
                'type' => TextType::class,
                'options' => [
                    'label' => 'Hero Title',
                    'required' => true,
                ],
                'legacy_key' => 'title', // Alternative keys for backward compatibility
            ],
            'hero_subtitle' => [
                'type' => TextareaType::class,
                'options' => [
                    'label' => 'Hero Subtitle',
                    'required' => false,
                    'attr' => ['rows' => 2],
                ],
                'legacy_key' => 'subtitle',
            ],
            'badge_text' => [
                'type' => TextType::class,
                'options' => [
                    'label' => 'Badge Text',
                    'required' => false,
                ],
            ],
            'description' => [
                'type' => TextareaType::class,
                'options' => [
                    'label' => 'Description',
                    'required' => false,
                    'attr' => ['rows' => 3],
                ],
            ],

            // Primary button (Content tab for convenience)
            'primary_button_text' => [
                 'type' => TextType::class,
                 'options' => [
                     'label' => 'Primary Button Text',
                     'required' => false,
                 ],
                 'legacy_key' => 'ctaText',
             ],
             'primary_button_url' => [
                 'type' => TextType::class,
                 'options' => [
                     'label' => 'Primary Button URL',
                     'required' => false,
                 ],
                 'legacy_key' => 'ctaUrl',
             ],
             
             // Secondary button (Content tab for convenience)
             'secondary_button_text' => [
                 'type' => TextType::class,
                 'options' => [
                     'label' => 'Secondary Button Text',
                     'required' => false,
                 ],
             ],
             'secondary_button_url' => [
                 'type' => TextType::class,
                 'options' => [
                     'label' => 'Secondary Button URL',
                     'required' => false,
                 ],
             ],
        ];
    }

/**
     * MEDIA TAB FIELDS
     * Image and media related settings
     */
    public static function getMediaFields(): array
    {
        return [
            // Existing media fields
            'hero_image_url' => [
                'type' => TextType::class,
                'options' => [
                    'label' => 'Hero Image URL',
                    'required' => false,
                ],
                'legacy_key' => 'imageUrl',
            ],
            'mobile_image_url' => [
                'type' => TextType::class,
                'options' => [
                    'label' => 'Mobile Image URL',
                    'required' => false,
                ],
            ],
            'image_alt_text' => [
                'type' => TextType::class,
                'options' => [
                    'label' => 'Image Alt Text',
                    'required' => false,
                ],
            ],
            'show_image' => [
                'type' => CheckboxType::class,
                'options' => [
                    'label' => 'Show Image',
                    'required' => false,
                ],
            ],
        ];
    }

    /**
     * LAYOUT TAB FIELDS
     * Positioning, sizing, and structural settings
     */
    public static function getLayoutFields(): array
    {
        return [
            'layout_type' => [
                'type' => ChoiceType::class,
                'options' => [
                    'label' => 'Layout Type',
                    'required' => false,
                    'choices' => [
                        'Form Left, Image Right' => 'form_left_image_right',
                        'Image Left, Form Right' => 'image_left_form_right',
                        'Centered (Image above Form)' => 'centered',
                        'Form Only' => 'form_only',
                    ],
                    'placeholder' => 'Select layout',
                ],
            ],
            'text_alignment' => [
                'type' => ChoiceType::class,
                'options' => [
                    'label' => 'Text Alignment',
                    'required' => false,
                    'choices' => [
                        'Left' => 'left',
                        'Center' => 'center',
                        'Right' => 'right',
                    ],
                    'placeholder' => 'Select alignment',
                ],
            ],
            'content_width' => [
                'type' => ChoiceType::class,
                'options' => [
                    'label' => 'Content Width',
                    'required' => false,
                    'choices' => [
                        'Narrow' => 'narrow',
                        'Medium' => 'medium',
                        'Wide' => 'wide',
                        'Full Width' => 'full',
                    ],
                    'placeholder' => 'Select width',
                ],
            ],
            'section_height' => [
                'type' => ChoiceType::class,
                'options' => [
                    'label' => 'Section Height',
                    'required' => false,
                    'choices' => [
                        'Auto' => 'auto',
                        'Small' => 'small',
                        'Medium' => 'medium',
                        'Large' => 'large',
                        'Full Height' => 'full',
                    ],
                    'placeholder' => 'Select height',
                ],
            ],
            'vertical_alignment' => [
                'type' => ChoiceType::class,
                'options' => [
                    'label' => 'Vertical Alignment',
                    'required' => false,
                    'choices' => [
                        'Top' => 'top',
                        'Center' => 'center',
                        'Bottom' => 'bottom',
                    ],
                    'placeholder' => 'Select alignment',
                ],
            ],
            'column_gap' => [
                'type' => TextType::class,
                'options' => [
                    'label' => 'Column Gap (px)',
                    'required' => false,
                ],
            ],
        ];
    }

    /**
     * STYLE TAB FIELDS
     * Colors, spacing, and visual styling
     */
    public static function getStyleFields(): array
    {
        return [
            // Background
            'background_color' => [
                'type' => ColorType::class,
                'options' => [
                    'label' => 'Background Color',
                    'required' => false,
                ],
                'legacy_key' => 'backgroundColor',
            ],
            'hero_text_color' => [
                'type' => ColorType::class,
                'options' => [
                    'label' => 'Hero Text Color',
                    'required' => false,
                ],
                'legacy_key' => 'textColor',
            ],

            // Spacing
            'padding_top' => [
                'type' => TextType::class,
                'options' => [
                    'label' => 'Padding Top (px)',
                    'required' => false,
                ],
                'legacy_key' => 'paddingTop',
            ],
            'padding_bottom' => [
                'type' => TextType::class,
                'options' => [
                    'label' => 'Padding Bottom (px)',
                    'required' => false,
                ],
                'legacy_key' => 'paddingBottom',
            ],
            'margin_top' => [
                'type' => TextType::class,
                'options' => [
                    'label' => 'Margin Top (px)',
                    'required' => false,
                ],
                'legacy_key' => 'marginTop',
            ],
            'margin_bottom' => [
                'type' => TextType::class,
                'options' => [
                    'label' => 'Margin Bottom (px)',
                    'required' => false,
                ],
                'legacy_key' => 'marginBottom',
            ],
        ];
    }

    /**
     * BUTTONS TAB FIELDS
     * Removed for the hero section (no CTA buttons used).
     */
    public static function getButtonsFields(): array
    {
        return [];
    }

    /**
     * Get tab display names for admin UI
     */
    public static function getTabNames(): array
    {
        return [
            self::TAB_BLOCKS => 'Blocks',
            self::TAB_CONTENT => 'Content',
            self::TAB_MEDIA => 'Media',
            self::TAB_LAYOUT => 'Layout',
            self::TAB_STYLE => 'Style',
            self::TAB_BUTTONS => 'Buttons',
        ];
    }

    /**
     * Get tab icon names (Bootstrap Icons)
     */
    public static function getTabIcons(): array
    {
        return [
            self::TAB_BLOCKS => 'bi-layers',
            self::TAB_CONTENT => 'bi-card-text',
            self::TAB_MEDIA => 'bi-image',
            self::TAB_LAYOUT => 'bi-layout-text-window-reverse',
            self::TAB_STYLE => 'bi-palette',
            self::TAB_BUTTONS => 'bi-hand-index-thumb',
        ];
    }

    /**
     * Resolve field value with legacy key support
     * Used for backward compatibility with old data keys
     */
    public static function resolveValue(array $existingData, string $fieldName, array $fieldConfig): mixed
    {
        $options = $fieldConfig['options'] ?? [];
        $legacyKey = $fieldConfig['legacy_key'] ?? null;
        
        // Get default value from options
        $default = $options['data'] ?? match ($fieldConfig['type']) {
            CheckboxType::class => false,
            default => '',
        };
        
        // Try primary key first
        if (isset($existingData[$fieldName])) {
            return $existingData[$fieldName];
        }
        
        // Try legacy key if available
        if ($legacyKey && isset($existingData[$legacyKey])) {
            return $existingData[$legacyKey];
        }
        
        return $default;
    }
}

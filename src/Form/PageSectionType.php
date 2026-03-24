<?php

namespace App\Form;

use App\Config\HeroFieldsConfig;
use App\Entity\PageSection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PageSectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => array_combine(PageSection::ALLOWED_TYPES, PageSection::ALLOWED_TYPES),
                'label' => 'Section Type',
                'placeholder' => 'Choose a section type',
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            // Hidden field to store hero blocks JSON
            ->add('blocks_json', HiddenType::class, [
                'label' => false,
                'required' => false,
                'mapped' => false,
            ])
            // Hidden field to store form fields JSON
            ->add('form_fields_json', HiddenType::class, [
                'label' => false,
                'required' => false,
                'mapped' => false,
                'data' => [], // Will be populated in PRE_SET_DATA for form type
            ])
        ;

        // Dynamic fields based on type
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $section = $event->getData();
            $form = $event->getForm();

            if ($section && $section->getType()) {
                $existingData = $section->getData();
                // Ensure existing data is an array
                if (!is_array($existingData)) {
                    $existingData = [];
                }
                $this->addDynamicFields($form, $section->getType(), $existingData);
                
                // Set blocks_json data for hero type sections
                if ($section->getType() === 'hero' && $form->has('blocks_json')) {
                    $existingBlocks = $existingData['blocks'] ?? [];
                    // Handle case where blocks is already a string (corrupted data from previous saves)
                    if (is_string($existingBlocks)) {
                        $decoded = json_decode($existingBlocks, true);
                        $existingBlocks = is_array($decoded) ? $decoded : [];
                    }
                    $form->get('blocks_json')->setData(json_encode($existingBlocks));
                }
                
                // Set form_fields_json data for form type sections
                if ($section->getType() === 'form' && $form->has('form_fields_json')) {
                    $existingFormFields = $existingData['form_fields'] ?? [];
                    // Handle case where form_fields is already a string (corrupted data from previous saves)
                    if (is_string($existingFormFields)) {
                        $decoded = json_decode($existingFormFields, true);
                        $existingFormFields = is_array($decoded) ? $decoded : [];
                    }
                    $form->get('form_fields_json')->setData(json_encode($existingFormFields));
                }
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (isset($data['type'])) {
                $existingData = $data['data'] ?? [];
                // Ensure existing data is an array
                if (!is_array($existingData)) {
                    $existingData = [];
                }
                $this->addDynamicFields($form, $data['type'], $existingData);
            }
        });

        // Handle form submission - merge JSON data into the data array
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            // Get the JSON data from hidden fields
            $blocksJson = $form->get('blocks_json')->getData();
            // Get form fields JSON directly from hidden field
            $formFieldsJson = null;
            if ($form->has('form_fields_json')) {
                $formFieldsJson = $form->get('form_fields_json')->getData();
            }

            if ($data instanceof PageSection) {
                $sectionData = $data->getData();
                if (!is_array($sectionData)) {
                    $sectionData = [];
                }

                // Process hero blocks JSON
                if ($blocksJson) {
                    if (is_string($blocksJson) && !empty($blocksJson)) {
                        $decoded = json_decode($blocksJson, true);
                        if (is_array($decoded)) {
                            $sectionData['blocks'] = $decoded;
                        }
                    } elseif (is_array($blocksJson)) {
                        $sectionData['blocks'] = $blocksJson;
                    }
                }

                // Process form fields JSON
                if ($formFieldsJson) {
                    if (is_string($formFieldsJson) && !empty($formFieldsJson)) {
                        $decoded = json_decode($formFieldsJson, true);
                        if (is_array($decoded)) {
                            $sectionData['form_fields'] = $decoded;
                        }
                    } elseif (is_array($formFieldsJson)) {
                        $sectionData['form_fields'] = $formFieldsJson;
                    }
                }

                $data->setData($sectionData);
            }
        });
    }

    public static function addDynamicFields($form, $type, $existingData = [])
    {
        // Remove existing data field if exists
        if ($form->has('data')) {
            $form->remove('data');
        }

        $form->add('data', SectionDataType::class, [
            'type' => $type,
            'existing_data' => $existingData,
            'label' => false,
            'data' => $existingData, // Make sure data is passed to the form
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PageSection::class,
        ]);
    }
}

class SectionDataType extends AbstractType
    {
        /**
         * Ensure value is a string (convert arrays/objects to string)
         */
        private function ensureString($value): string
        {
            if (is_string($value)) {
                return $value;
            }
            if (is_array($value) || is_object($value)) {
                error_log(sprintf('Converting %s to string: %s', gettype($value), print_r($value, true)));
                return ''; // Return empty string for arrays/objects
            }
            return (string) $value; // Convert other types (numeric, boolean, etc.) to string
        }

        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $type = $options['type'];
            $existingData = $options['data'] ?? $options['existing_data'] ?? [];

        // Debug: Log existingData to help identify which field has array value
        foreach ($existingData as $key => $value) {
            if (is_array($value) && $key !== 'cards' && $key !== 'items') { // Skip known array fields
                error_log(sprintf('WARNING: Field "%s" in section type "%s" has array value: %s', $key, $type, print_r($value, true)));
            }
        }

        switch ($type) {
            case 'header':
                $builder
                    // Content Fields
                    ->add('brandText', TextType::class, [
                        'label' => 'Brand Text',
                        'required' => true,
                        'data' => $this->ensureString($existingData['brandText'] ?? ''),
                    ])
                    ->add('logoUrl', TextType::class, [
                        'label' => 'Logo URL',
                        'required' => false,
                        'data' => $this->ensureString($existingData['logoUrl'] ?? ''),
                    ])
                    ->add('menuItems', TextareaType::class, [
                        'label' => 'Menu Items (Label|/url per line)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['menuItems'] ?? ''),
                    ])
                    ->add('ctaText', TextType::class, [
                        'label' => 'CTA Text',
                        'required' => true,
                        'data' => $this->ensureString($existingData['ctaText'] ?? ''),
                    ])
                    ->add('ctaUrl', TextType::class, [
                        'label' => 'CTA URL',
                        'required' => true,
                        'data' => $this->ensureString($existingData['ctaUrl'] ?? ''),
                    ])
                    
                    // Style Fields
                    ->add('backgroundColor', ColorType::class, [
                        'label' => 'Header Background Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['backgroundColor'] ?? $existingData['background'] ?? ''),
                    ])
                    ->add('textColor', ColorType::class, [
                        'label' => 'Header Text Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['textColor'] ?? ''),
                    ])
                    ->add('buttonBackgroundColor', ColorType::class, [
                        'label' => 'CTA Button Background Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['buttonBackgroundColor'] ?? ''),
                    ])
                    ->add('buttonTextColor', ColorType::class, [
                        'label' => 'CTA Button Text Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['buttonTextColor'] ?? ''),
                    ])
                    ->add('buttonBorderColor', ColorType::class, [
                        'label' => 'CTA Button Border Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['buttonBorderColor'] ?? ''),
                    ])
                    ->add('buttonBorderRadius', TextType::class, [
                        'label' => 'CTA Button Border Radius (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['buttonBorderRadius'] ?? ''),
                    ])
                    ->add('buttonStyle', ChoiceType::class, [
                        'label' => 'CTA Button Style',
                        'required' => false,
                        'choices' => [
                            'Primary' => 'primary',
                            'Secondary' => 'secondary',
                            'Outline' => 'outline',
                            'Ghost' => 'ghost',
                        ],
                        'data' => $existingData['buttonStyle'] ?? 'primary',
                        'placeholder' => 'Select style',
                    ])
                    ->add('paddingTop', TextType::class, [
                        'label' => 'Padding Top (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['paddingTop'] ?? ''),
                    ])
                    ->add('paddingBottom', TextType::class, [
                        'label' => 'Padding Bottom (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['paddingBottom'] ?? ''),
                    ])
                    ->add('marginTop', TextType::class, [
                        'label' => 'Margin Top (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['marginTop'] ?? ''),
                    ])
                    ->add('marginBottom', TextType::class, [
                        'label' => 'Margin Bottom (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['marginBottom'] ?? ''),
                    ]);
                break;

            case 'hero_split':
                $builder
                    // Content Fields
                    ->add('title', TextType::class, [
                        'label' => 'Hero Title',
                        'required' => true,
                        'data' => $this->ensureString($existingData['title'] ?? ''),
                    ])
                    ->add('subtitle', TextareaType::class, [
                        'label' => 'Hero Subtitle',
                        'required' => false,
                        'data' => $this->ensureString($existingData['subtitle'] ?? ''),
                    ])
                    ->add('imageUrl', TextType::class, [
                        'label' => 'Image URL',
                        'required' => false,
                        'data' => $this->ensureString($existingData['imageUrl'] ?? ''),
                    ])
                    ->add('formTitle', TextType::class, [
                        'label' => 'Form Title',
                        'required' => false,
                        'data' => $this->ensureString($existingData['formTitle'] ?? ''),
                    ])
                    ->add('layout', ChoiceType::class, [
                        'label' => 'Layout',
                        'required' => false,
                        'choices' => [
                            'Text Left, Image Right' => 'text-left',
                            'Image Left, Text Right' => 'image-left',
                        ],
                        'data' => $existingData['layout'] ?? 'text-left',
                        'placeholder' => 'Select layout',
                    ])
                    ->add('ctaText', TextType::class, [
                        'label' => 'CTA Text',
                        'required' => false,
                        'data' => $this->ensureString($existingData['ctaText'] ?? ''),
                    ])
                    ->add('ctaUrl', TextType::class, [
                        'label' => 'CTA URL',
                        'required' => false,
                        'data' => $this->ensureString($existingData['ctaUrl'] ?? ''),
                    ])
                    
                    // Style Fields
                    ->add('backgroundColor', ColorType::class, [
                        'label' => 'Hero Background Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['backgroundColor'] ?? ''),
                    ])
                    ->add('textColor', ColorType::class, [
                        'label' => 'Hero Text Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['textColor'] ?? ''),
                    ])
                    ->add('titleColor', ColorType::class, [
                        'label' => 'Title Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['titleColor'] ?? ''),
                    ])
                    ->add('subtitleColor', ColorType::class, [
                        'label' => 'Subtitle Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['subtitleColor'] ?? ''),
                    ])
                    ->add('buttonBackgroundColor', ColorType::class, [
                        'label' => 'CTA Button Background Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['buttonBackgroundColor'] ?? ''),
                    ])
                    ->add('buttonTextColor', ColorType::class, [
                        'label' => 'CTA Button Text Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['buttonTextColor'] ?? ''),
                    ])
                    ->add('buttonBorderColor', ColorType::class, [
                        'label' => 'CTA Button Border Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['buttonBorderColor'] ?? ''),
                    ])
                    ->add('buttonBorderRadius', TextType::class, [
                        'label' => 'CTA Button Border Radius (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['buttonBorderRadius'] ?? ''),
                    ])
                    ->add('buttonStyle', ChoiceType::class, [
                        'label' => 'CTA Button Style',
                        'required' => false,
                        'choices' => [
                            'Primary' => 'primary',
                            'Secondary' => 'secondary',
                            'Outline' => 'outline',
                            'Ghost' => 'ghost',
                        ],
                        'data' => $existingData['buttonStyle'] ?? 'primary',
                        'placeholder' => 'Select style',
                    ])
                    ->add('paddingTop', TextType::class, [
                        'label' => 'Padding Top (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['paddingTop'] ?? ''),
                    ])
                    ->add('paddingBottom', TextType::class, [
                        'label' => 'Padding Bottom (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['paddingBottom'] ?? ''),
                    ])
                    ->add('marginTop', TextType::class, [
                        'label' => 'Margin Top (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['marginTop'] ?? ''),
                    ])
                    ->add('marginBottom', TextType::class, [
                        'label' => 'Margin Bottom (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['marginBottom'] ?? ''),
                    ]);
                break;

            case 'hero':
                $builder
                    // ========== BLOCKS TAB (Dynamic Builder) ==========
                    ->add('blocks', HeroBlocksType::class, [
                        'label' => false,
                        'data' => $existingData['blocks'] ?? [],
                    ])

                    // ========== CONTENT TAB ==========
                    // HeroFieldsConfig is the single source of truth for field definitions
                    // See src/Config/HeroFieldsConfig.php for all field definitions
                    ->add('hero_title', TextType::class, [
                        'label' => 'Hero Title',
                        'required' => true,
                        'data' => $this->ensureString($existingData['hero_title'] ?? $existingData['title'] ?? ''),
                    ])
                    ->add('hero_subtitle', TextareaType::class, [
                        'label' => 'Hero Subtitle',
                        'required' => false,
                        'data' => $this->ensureString($existingData['hero_subtitle'] ?? $existingData['subtitle'] ?? ''),
                        'attr' => ['rows' => 2],
                    ])
                    ->add('badge_text', TextType::class, [
                        'label' => 'Badge Text',
                        'required' => false,
                        'data' => $this->ensureString($existingData['badge_text'] ?? ''),
                    ])
                    ->add('description', TextareaType::class, [
                        'label' => 'Description',
                        'required' => false,
                        'data' => $this->ensureString($existingData['description'] ?? ''),
                        'attr' => ['rows' => 3],
                    ])
                    
                    // ===== NEW FIELDS (defined in HeroFieldsConfig.php) =====
                    ->add('top_text', TextType::class, [
                        'label' => 'Top Text / Badge',
                        'required' => false,
                        'data' => $this->ensureString($existingData['top_text'] ?? ''),
                        'attr' => ['placeholder' => 'e.g., Welcome to our site'],
                    ])
                    ->add('phone_number', TextType::class, [
                        'label' => 'Phone Number',
                        'required' => false,
                        'data' => $this->ensureString($existingData['phone_number'] ?? ''),
                        'attr' => ['placeholder' => '+1 234 567 8900'],
                    ])
                    ->add('show_form', CheckboxType::class, [
                        'label' => 'Show Contact Form',
                        'required' => false,
                        'data' => $existingData['show_form'] ?? false,
                    ])
                    ->add('form_title', TextType::class, [
                        'label' => 'Form Title',
                        'required' => false,
                        'data' => $this->ensureString($existingData['form_title'] ?? ''),
                    ])
                    ->add('form_subtitle', TextareaType::class, [
                        'label' => 'Form Subtitle',
                        'required' => false,
                        'data' => $this->ensureString($existingData['form_subtitle'] ?? ''),
                        'attr' => ['rows' => 2],
                    ])
                    ->add('left_image', TextType::class, [
                        'label' => 'Left Image URL',
                        'required' => false,
                        'data' => $this->ensureString($existingData['left_image'] ?? ''),
                        'attr' => ['placeholder' => '/images/left-image.jpg'],
                    ])
                    
                    // Primary button
                    ->add('primary_button_text', TextType::class, [
                        'label' => 'Primary Button Text',
                        'required' => false,
                        'data' => $this->ensureString($existingData['primary_button_text'] ?? $existingData['ctaText'] ?? ''),
                    ])
                    ->add('primary_button_url', TextType::class, [
                        'label' => 'Primary Button URL',
                        'required' => false,
                        'data' => $this->ensureString($existingData['primary_button_url'] ?? $existingData['ctaUrl'] ?? ''),
                    ])
                    
                    // Secondary button
                    ->add('secondary_button_text', TextType::class, [
                        'label' => 'Secondary Button Text',
                        'required' => false,
                        'data' => $this->ensureString($existingData['secondary_button_text'] ?? ''),
                    ])
                    ->add('secondary_button_url', TextType::class, [
                        'label' => 'Secondary Button URL',
                        'required' => false,
                        'data' => $this->ensureString($existingData['secondary_button_url'] ?? ''),
                    ])

                    // Legacy fields for backward compatibility (hidden)
                    ->add('title', HiddenType::class, [
                        'required' => false,
                        'mapped' => false,
                        'data' => $this->ensureString($existingData['title'] ?? ''),
                    ])
                    ->add('subtitle', HiddenType::class, [
                        'required' => false,
                        'mapped' => false,
                        'data' => $this->ensureString($existingData['subtitle'] ?? ''),
                    ])
                    ->add('ctaText', HiddenType::class, [
                        'required' => false,
                        'mapped' => false,
                        'data' => $this->ensureString($existingData['ctaText'] ?? ''),
                    ])
                    ->add('ctaUrl', HiddenType::class, [
                        'required' => false,
                        'mapped' => false,
                        'data' => $this->ensureString($existingData['ctaUrl'] ?? ''),
                    ])
                    ->add('imageUrl', HiddenType::class, [
                        'required' => false,
                        'mapped' => false,
                        'data' => $this->ensureString($existingData['imageUrl'] ?? ''),
                    ])
                    ->add('showForm', HiddenType::class, [
                        'required' => false,
                        'mapped' => false,
                        'data' => $existingData['showForm'] ?? false,
                    ])

                    // ========== MEDIA TAB ==========
                    ->add('hero_image_url', TextType::class, [
                        'label' => 'Hero Image URL',
                        'required' => false,
                        'data' => $this->ensureString($existingData['hero_image_url'] ?? $existingData['imageUrl'] ?? ''),
                    ])
                    ->add('mobile_image_url', TextType::class, [
                        'label' => 'Mobile Image URL',
                        'required' => false,
                        'data' => $this->ensureString($existingData['mobile_image_url'] ?? ''),
                    ])
                    ->add('image_alt_text', TextType::class, [
                        'label' => 'Image Alt Text',
                        'required' => false,
                        'data' => $this->ensureString($existingData['image_alt_text'] ?? ''),
                    ])
                    ->add('show_image', CheckboxType::class, [
                        'label' => 'Show Image',
                        'required' => false,
                        'data' => $existingData['show_image'] ?? true,
                    ])

                    // ========== LAYOUT TAB ==========
                    ->add('layout_type', ChoiceType::class, [
                        'label' => 'Layout Type',
                        'required' => false,
                        'choices' => [
                            'Text Left, Image Right' => 'text_left_image_right',
                            'Image Left, Text Right' => 'image_left_text_right',
                            'Centered' => 'centered',
                            'Form Right, Image Left' => 'form_right_image_left',
                            'Form Only' => 'form_only',
                        ],
                        'data' => $existingData['layout_type'] ?? 'centered',
                        'placeholder' => 'Select layout',
                    ])
                    ->add('text_alignment', ChoiceType::class, [
                        'label' => 'Text Alignment',
                        'required' => false,
                        'choices' => [
                            'Left' => 'left',
                            'Center' => 'center',
                            'Right' => 'right',
                        ],
                        'data' => $existingData['text_alignment'] ?? 'center',
                        'placeholder' => 'Select alignment',
                    ])
                    ->add('content_width', ChoiceType::class, [
                        'label' => 'Content Width',
                        'required' => false,
                        'choices' => [
                            'Narrow' => 'narrow',
                            'Medium' => 'medium',
                            'Wide' => 'wide',
                            'Full Width' => 'full',
                        ],
                        'data' => $existingData['content_width'] ?? 'medium',
                        'placeholder' => 'Select width',
                    ])
                    ->add('section_height', ChoiceType::class, [
                        'label' => 'Section Height',
                        'required' => false,
                        'choices' => [
                            'Auto' => 'auto',
                            'Small' => 'small',
                            'Medium' => 'medium',
                            'Large' => 'large',
                            'Full Height' => 'full',
                        ],
                        'data' => $existingData['section_height'] ?? 'medium',
                        'placeholder' => 'Select height',
                    ])
                    ->add('vertical_alignment', ChoiceType::class, [
                        'label' => 'Vertical Alignment',
                        'required' => false,
                        'choices' => [
                            'Top' => 'top',
                            'Center' => 'center',
                            'Bottom' => 'bottom',
                        ],
                        'data' => $existingData['vertical_alignment'] ?? 'center',
                        'placeholder' => 'Select alignment',
                    ])
                    ->add('column_gap', TextType::class, [
                        'label' => 'Column Gap (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['column_gap'] ?? '30'),
                    ])

                    // ========== STYLE TAB ==========
                    ->add('background_color', ColorType::class, [
                        'label' => 'Background Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['background_color'] ?? $existingData['backgroundColor'] ?? ''),
                    ])
                    ->add('background_gradient', TextType::class, [
                        'label' => 'Background Gradient (CSS)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['background_gradient'] ?? ''),
                        'attr' => ['placeholder' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
                    ])
                    ->add('hero_text_color', ColorType::class, [
                        'label' => 'Hero Text Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['hero_text_color'] ?? $existingData['textColor'] ?? ''),
                    ])
                    ->add('title_color', ColorType::class, [
                        'label' => 'Title Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['title_color'] ?? $existingData['titleColor'] ?? ''),
                    ])
                    ->add('subtitle_color', ColorType::class, [
                        'label' => 'Subtitle Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['subtitle_color'] ?? $existingData['subtitleColor'] ?? ''),
                    ])
                    ->add('description_color', ColorType::class, [
                        'label' => 'Description Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['description_color'] ?? ''),
                    ])
                    ->add('card_background_color', ColorType::class, [
                        'label' => 'Card Background Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['card_background_color'] ?? ''),
                    ])
                    ->add('border_radius', TextType::class, [
                        'label' => 'Border Radius (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['border_radius'] ?? ''),
                    ])
                    ->add('box_shadow', CheckboxType::class, [
                        'label' => 'Show Box Shadow',
                        'required' => false,
                        'data' => $existingData['box_shadow'] ?? false,
                    ])
                    ->add('padding_top', TextType::class, [
                        'label' => 'Padding Top (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['padding_top'] ?? $existingData['paddingTop'] ?? '60'),
                    ])
                    ->add('padding_bottom', TextType::class, [
                        'label' => 'Padding Bottom (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['padding_bottom'] ?? $existingData['paddingBottom'] ?? '60'),
                    ])
                    ->add('margin_top', TextType::class, [
                        'label' => 'Margin Top (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['margin_top'] ?? $existingData['marginTop'] ?? ''),
                    ])
                    ->add('margin_bottom', TextType::class, [
                        'label' => 'Margin Bottom (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['margin_bottom'] ?? $existingData['marginBottom'] ?? ''),
                    ])

                    // ========== BUTTONS TAB ==========
                    ->add('primary_button_background_color', ColorType::class, [
                        'label' => 'Primary Button Background Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['primary_button_background_color'] ?? $existingData['buttonBackgroundColor'] ?? ''),
                    ])
                    ->add('primary_button_text_color', ColorType::class, [
                        'label' => 'Primary Button Text Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['primary_button_text_color'] ?? $existingData['buttonTextColor'] ?? ''),
                    ])
                    ->add('primary_button_border_color', ColorType::class, [
                        'label' => 'Primary Button Border Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['primary_button_border_color'] ?? $existingData['buttonBorderColor'] ?? ''),
                    ])
                    ->add('primary_button_border_radius', TextType::class, [
                        'label' => 'Primary Button Border Radius (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['primary_button_border_radius'] ?? $existingData['buttonBorderRadius'] ?? ''),
                    ])
                    ->add('primary_button_style', ChoiceType::class, [
                        'label' => 'Primary Button Style',
                        'required' => false,
                        'choices' => [
                            'Primary' => 'primary',
                            'Secondary' => 'secondary',
                            'Outline' => 'outline',
                            'Ghost' => 'ghost',
                        ],
                        'data' => $existingData['primary_button_style'] ?? 'primary',
                        'placeholder' => 'Select style',
                    ])
                    ->add('secondary_button_background_color', ColorType::class, [
                        'label' => 'Secondary Button Background Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['secondary_button_background_color'] ?? ''),
                    ])
                    ->add('secondary_button_text_color', ColorType::class, [
                        'label' => 'Secondary Button Text Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['secondary_button_text_color'] ?? ''),
                    ])
                    ->add('secondary_button_border_color', ColorType::class, [
                        'label' => 'Secondary Button Border Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['secondary_button_border_color'] ?? ''),
                    ])
                    ->add('secondary_button_border_radius', TextType::class, [
                        'label' => 'Secondary Button Border Radius (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['secondary_button_border_radius'] ?? ''),
                    ])
                    ->add('secondary_button_style', ChoiceType::class, [
                        'label' => 'Secondary Button Style',
                        'required' => false,
                        'choices' => [
                            'Primary' => 'primary',
                            'Secondary' => 'secondary',
                            'Outline' => 'outline',
                            'Ghost' => 'ghost',
                        ],
                        'data' => $existingData['secondary_button_style'] ?? 'outline',
                        'placeholder' => 'Select style',
                    ]);
                break;

            case 'hero_quote':
                $builder
                    // ========== CONTENT TAB ==========
                    ->add('top_text', TextType::class, [
                        'label' => 'Top Text / Badge',
                        'required' => false,
                        'data' => $this->ensureString($existingData['top_text'] ?? ''),
                    ])
                    ->add('hero_title', TextType::class, [
                        'label' => 'Hero Title',
                        'required' => true,
                        'data' => $this->ensureString($existingData['hero_title'] ?? ''),
                    ])
                    ->add('hero_subtitle', TextareaType::class, [
                        'label' => 'Hero Subtitle',
                        'required' => false,
                        'data' => $this->ensureString($existingData['hero_subtitle'] ?? ''),
                    ])
                    ->add('phone_number', TextType::class, [
                        'label' => 'Phone Number',
                        'required' => false,
                        'data' => $this->ensureString($existingData['phone_number'] ?? ''),
                    ])
                    ->add('left_image', TextType::class, [
                        'label' => 'Left Image URL',
                        'required' => false,
                        'data' => $this->ensureString($existingData['left_image'] ?? ''),
                    ])
                    
                    // ========== FORM TAB ==========
                    ->add('form_title', TextType::class, [
                        'label' => 'Form Title',
                        'required' => false,
                        'data' => $this->ensureString($existingData['form_title'] ?? ''),
                    ])
                    ->add('form_description', TextareaType::class, [
                        'label' => 'Form Description',
                        'required' => false,
                        'data' => $this->ensureString($existingData['form_description'] ?? ''),
                    ])
                    ->add('button_text', TextType::class, [
                        'label' => 'CTA Button Text',
                        'required' => false,
                        'data' => $this->ensureString($existingData['button_text'] ?? 'Get Quote'),
                    ])
                    
                    // ========== STYLE TAB ==========
                    ->add('background_color', ColorType::class, [
                        'label' => 'Background Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['background_color'] ?? '#f8f9fa'),
                    ])
                    ->add('text_color', ColorType::class, [
                        'label' => 'Text Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['text_color'] ?? '#212529'),
                    ])
                    ->add('title_color', ColorType::class, [
                        'label' => 'Title Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['title_color'] ?? '#212529'),
                    ])
                    ->add('form_card_background', ColorType::class, [
                        'label' => 'Form Card Background',
                        'required' => false,
                        'data' => $this->ensureString($existingData['form_card_background'] ?? '#ffffff'),
                    ])
                    ->add('button_background_color', ColorType::class, [
                        'label' => 'Button Background Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['button_background_color'] ?? '#007bff'),
                    ])
                    ->add('button_text_color', ColorType::class, [
                        'label' => 'Button Text Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['button_text_color'] ?? '#ffffff'),
                    ])
                    
                    // ========== LAYOUT TAB ==========
                    ->add('padding_top', TextType::class, [
                        'label' => 'Padding Top (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['padding_top'] ?? '80'),
                    ])
                    ->add('padding_bottom', TextType::class, [
                        'label' => 'Padding Bottom (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['padding_bottom'] ?? '80'),
                    ]);
                break;

            case 'body':
                $builder
                    ->add('content', TextareaType::class, [
                        'label' => 'Content',
                        'required' => true,
                        'data' => $this->ensureString($existingData['content'] ?? ''),
                        'attr' => ['rows' => 10],
                    ]);
                break;

            case 'image':
                $builder
                    ->add('imageUrl', TextType::class, [
                        'label' => 'Image URL',
                        'required' => true,
                        'data' => $this->ensureString($existingData['imageUrl'] ?? ''),
                    ])
                    ->add('alt', TextType::class, [
                        'label' => 'Alt Text',
                        'required' => true,
                        'data' => $this->ensureString($existingData['alt'] ?? ''),
                    ])
                    ->add('caption', TextareaType::class, [
                        'label' => 'Caption',
                        'required' => false,
                        'data' => $this->ensureString($existingData['caption'] ?? ''),
                    ]);
                break;

            case 'cards':
            case 'cards_premium':
                $builder
                    // Content Fields
                    ->add('sectionTitle', TextType::class, [
                        'label' => 'Section Title',
                        'required' => false,
                        'data' => $this->ensureString($existingData['sectionTitle'] ?? ''),
                    ])
                    ->add('cards', CollectionType::class, [
                        'entry_type' => CardType::class,
                        'entry_options' => ['label' => false],
                        'allow_add' => true,
                        'allow_delete' => true,
                        'prototype' => true,
                        'data' => $existingData['cards'] ?? [],
                        'label' => 'Cards',
                    ])
                    
                    // Layout Fields
                    ->add('cardLayout', ChoiceType::class, [
                        'label' => 'Card Layout',
                        'required' => false,
                        'choices' => [
                            'Vertical' => 'vertical',
                            '2 Columns' => 'grid-2',
                            '3 Columns' => 'grid-3',
                            '4 Columns' => 'grid-4',
                            'Horizontal' => 'horizontal',
                            'Centered' => 'centered',
                            'Compact' => 'compact',
                        ],
                        'data' => $existingData['cardLayout'] ?? 'grid-3',
                        'placeholder' => 'Select layout',
                    ])
                    ->add('cardStyle', ChoiceType::class, [
                        'label' => 'Card Style',
                        'required' => false,
                        'choices' => [
                            'Standard' => 'standard',
                            'Rounded' => 'rounded',
                            'Square' => 'square',
                            'Oval' => 'oval',
                            'Bordered' => 'bordered',
                            'Shadowed' => 'shadowed',
                        ],
                        'data' => $existingData['cardStyle'] ?? 'standard',
                        'placeholder' => 'Select style',
                    ])
                    
                    // Style Fields
                    ->add('backgroundColor', ColorType::class, [
                        'label' => 'Section Background Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['backgroundColor'] ?? ''),
                    ])
                    ->add('textColor', ColorType::class, [
                        'label' => 'Section Text Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['textColor'] ?? ''),
                    ])
                    ->add('titleColor', ColorType::class, [
                        'label' => 'Section Title Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['titleColor'] ?? ''),
                    ])
                    ->add('subtitleColor', ColorType::class, [
                        'label' => 'Section Subtitle Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['subtitleColor'] ?? ''),
                    ])
                    ->add('cardBackgroundColor', ColorType::class, [
                        'label' => 'Card Background Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['cardBackgroundColor'] ?? ''),
                    ])
                    ->add('cardTitleColor', ColorType::class, [
                        'label' => 'Card Title Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['cardTitleColor'] ?? ''),
                    ])
                    ->add('cardTextColor', ColorType::class, [
                        'label' => 'Card Text Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['cardTextColor'] ?? ''),
                    ])
                    ->add('cardBorderColor', ColorType::class, [
                        'label' => 'Card Border Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['cardBorderColor'] ?? ''),
                    ])
                    ->add('cardShadow', CheckboxType::class, [
                        'label' => 'Show Card Shadow',
                        'required' => false,
                        'data' => $existingData['cardShadow'] ?? false,
                    ])
                    ->add('cardBorderRadius', TextType::class, [
                        'label' => 'Card Border Radius (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['cardBorderRadius'] ?? ''),
                    ])
                    ->add('buttonBackgroundColor', ColorType::class, [
                        'label' => 'Button Background Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['buttonBackgroundColor'] ?? ''),
                    ])
                    ->add('buttonTextColor', ColorType::class, [
                        'label' => 'Button Text Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['buttonTextColor'] ?? ''),
                    ])
                    ->add('buttonBorderColor', ColorType::class, [
                        'label' => 'Button Border Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['buttonBorderColor'] ?? ''),
                    ])
                    ->add('buttonBorderRadius', TextType::class, [
                        'label' => 'Button Border Radius (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['buttonBorderRadius'] ?? ''),
                    ])
                    ->add('buttonStyle', ChoiceType::class, [
                        'label' => 'Button Style',
                        'required' => false,
                        'choices' => [
                            'Primary' => 'primary',
                            'Secondary' => 'secondary',
                            'Outline' => 'outline',
                            'Ghost' => 'ghost',
                        ],
                        'data' => $existingData['buttonStyle'] ?? 'primary',
                        'placeholder' => 'Select style',
                    ])
                    ->add('textAlignment', ChoiceType::class, [
                        'label' => 'Text Alignment',
                        'required' => false,
                        'choices' => [
                            'Left' => 'left',
                            'Center' => 'center',
                            'Right' => 'right',
                        ],
                        'data' => $existingData['textAlignment'] ?? 'center',
                        'placeholder' => 'Select alignment',
                    ])
                    ->add('paddingTop', TextType::class, [
                        'label' => 'Padding Top (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['paddingTop'] ?? ''),
                    ])
                    ->add('paddingBottom', TextType::class, [
                        'label' => 'Padding Bottom (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['paddingBottom'] ?? ''),
                    ])
                    ->add('marginTop', TextType::class, [
                        'label' => 'Margin Top (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['marginTop'] ?? ''),
                    ])
                    ->add('marginBottom', TextType::class, [
                        'label' => 'Margin Bottom (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['marginBottom'] ?? ''),
                    ]);
                break;

            case 'faq':
                $builder
                    ->add('sectionTitle', TextType::class, [
                        'label' => 'Section Title',
                        'required' => false,
                        'data' => $this->ensureString($existingData['sectionTitle'] ?? ''),
                    ])
                    ->add('items', CollectionType::class, [
                        'entry_type' => FaqItemType::class,
                        'entry_options' => ['label' => false],
                        'allow_add' => true,
                        'allow_delete' => true,
                        'prototype' => true,
                        'data' => $existingData['items'] ?? [],
                        'label' => 'FAQ Items',
                    ]);
                break;

            case 'form':
                $builder
                    // ========== FIELDS TAB (Dynamic Builder) ==========
                    ->add('form_fields', FormFieldsType::class, [
                        'label' => false,
                        'data' => $existingData['form_fields'] ?? [],
                    ]);


                // ========== CONTENT TAB (Legacy) ==========
                $builder
                    ->add('section_title', TextType::class, [
                        'label' => 'Section Title',
                        'required' => false,
                        'data' => $this->ensureString($existingData['section_title'] ?? ''),
                    ])
                    ->add('section_subtitle', TextareaType::class, [
                        'label' => 'Section Subtitle',
                        'required' => false,
                        'data' => $this->ensureString($existingData['section_subtitle'] ?? ''),
                    ])
                    ->add('form_title', TextType::class, [
                        'label' => 'Form Title',
                        'required' => false,
                        'data' => $this->ensureString($existingData['form_title'] ?? $existingData['title'] ?? ''),
                    ])
                    ->add('form_description', TextareaType::class, [
                        'label' => 'Form Description',
                        'required' => false,
                        'data' => $this->ensureString($existingData['form_description'] ?? ''),
                    ])
                    ->add('submit_button_text', TextType::class, [
                        'label' => 'Submit Button Text',
                        'required' => false,
                        'data' => $this->ensureString($existingData['submit_button_text'] ?? $existingData['submitText'] ?? 'Submit'),
                    ])
                    ->add('success_message', TextareaType::class, [
                        'label' => 'Success Message',
                        'required' => false,
                        'data' => $this->ensureString($existingData['success_message'] ?? $existingData['successMessage'] ?? 'Thank you! Your message has been sent.'),
                    ])

                    // Legacy fields for backward compatibility (hidden)
                    ->add('title', HiddenType::class, [
                        'required' => false,
                        'mapped' => false,
                        'data' => $this->ensureString($existingData['title'] ?? ''),
                    ])
                    ->add('submitText', HiddenType::class, [
                        'required' => false,
                        'mapped' => false,
                        'data' => $this->ensureString($existingData['submitText'] ?? ''),
                    ])
                    ->add('successMessage', HiddenType::class, [
                        'required' => false,
                        'mapped' => false,
                        'data' => $this->ensureString($existingData['successMessage'] ?? ''),
                    ])
                    ->add('fields', HiddenType::class, [
                        'required' => false,
                        'mapped' => false,
                        'data' => (isset($existingData['fields']) && is_array($existingData['fields'])) ? json_encode($existingData['fields']) : '',
                    ])

                    // ========== FORM SETTINGS TAB ==========
                    ->add('form_type', ChoiceType::class, [
                        'label' => 'Form Type',
                        'required' => false,
                        'choices' => [
                            'Contact' => 'contact',
                            'Lead Generation' => 'lead',
                            'Quote Request' => 'quote',
                            'Newsletter' => 'newsletter',
                            'Custom' => 'custom',
                        ],
                        'data' => $existingData['form_type'] ?? 'contact',
                        'placeholder' => 'Select form type',
                    ])
                    ->add('form_key', TextType::class, [
                        'label' => 'Form Key / ID',
                        'required' => false,
                        'data' => $this->ensureString($existingData['form_key'] ?? $existingData['form_id'] ?? ''),
                        'attr' => ['placeholder' => 'e.g., contact-form-001'],
                    ])
                    ->add('show_name_field', CheckboxType::class, [
                        'label' => 'Show Name Field',
                        'required' => false,
                        'data' => $existingData['show_name_field'] ?? true,
                    ])
                    ->add('show_email_field', CheckboxType::class, [
                        'label' => 'Show Email Field',
                        'required' => false,
                        'data' => $existingData['show_email_field'] ?? true,
                    ])
                    ->add('show_phone_field', CheckboxType::class, [
                        'label' => 'Show Phone Field',
                        'required' => false,
                        'data' => $existingData['show_phone_field'] ?? false,
                    ])
                    ->add('show_message_field', CheckboxType::class, [
                        'label' => 'Show Message Field',
                        'required' => false,
                        'data' => $existingData['show_message_field'] ?? false,
                    ])
                    ->add('show_company_field', CheckboxType::class, [
                        'label' => 'Show Company Field',
                        'required' => false,
                        'data' => $existingData['show_company_field'] ?? false,
                    ])
                    ->add('show_checkbox_consent', CheckboxType::class, [
                        'label' => 'Show Consent Checkbox',
                        'required' => false,
                        'data' => $existingData['show_checkbox_consent'] ?? false,
                    ])
                    ->add('redirect_url_after_submit', TextType::class, [
                        'label' => 'Redirect URL After Submit',
                        'required' => false,
                        'data' => $this->ensureString($existingData['redirect_url_after_submit'] ?? ''),
                        'attr' => ['placeholder' => '/thank-you'],
                    ])
                    ->add('store_submissions', CheckboxType::class, [
                        'label' => 'Store Submissions',
                        'required' => false,
                        'data' => $existingData['store_submissions'] ?? true,
                    ])

                    // ========== LAYOUT TAB ==========
                    ->add('form_layout', ChoiceType::class, [
                        'label' => 'Form Layout',
                        'required' => false,
                        'choices' => [
                            'Centered' => 'centered',
                            'Full Width' => 'full_width',
                            'Split with Image' => 'split_with_image',
                            'Split with Text' => 'split_with_text',
                        ],
                        'data' => $existingData['form_layout'] ?? 'centered',
                        'placeholder' => 'Select layout',
                    ])
                    ->add('form_width', ChoiceType::class, [
                        'label' => 'Form Width',
                        'required' => false,
                        'choices' => [
                            'Narrow' => 'narrow',
                            'Medium' => 'medium',
                            'Wide' => 'wide',
                            'Full Width' => 'full',
                        ],
                        'data' => $existingData['form_width'] ?? 'medium',
                        'placeholder' => 'Select width',
                    ])
                    ->add('form_alignment', ChoiceType::class, [
                        'label' => 'Form Alignment',
                        'required' => false,
                        'choices' => [
                            'Left' => 'left',
                            'Center' => 'center',
                            'Right' => 'right',
                        ],
                        'data' => $existingData['form_alignment'] ?? 'center',
                        'placeholder' => 'Select alignment',
                    ])
                    ->add('show_icon_above_title', CheckboxType::class, [
                        'label' => 'Show Icon Above Title',
                        'required' => false,
                        'data' => $existingData['show_icon_above_title'] ?? false,
                    ])

                    // ========== MEDIA TAB ==========
                    ->add('side_image_url', TextType::class, [
                        'label' => 'Side Image URL',
                        'required' => false,
                        'data' => $this->ensureString($existingData['side_image_url'] ?? ''),
                    ])
                    ->add('image_alt_text', TextType::class, [
                        'label' => 'Image Alt Text',
                        'required' => false,
                        'data' => $this->ensureString($existingData['image_alt_text'] ?? ''),
                    ])
                    ->add('show_image', CheckboxType::class, [
                        'label' => 'Show Image',
                        'required' => false,
                        'data' => $existingData['show_image'] ?? false,
                    ])

                    // ========== STYLE TAB ==========
                    ->add('section_background_color', ColorType::class, [
                        'label' => 'Section Background Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['section_background_color'] ?? $existingData['backgroundColor'] ?? ''),
                    ])
                    ->add('form_card_background_color', ColorType::class, [
                        'label' => 'Form Card Background Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['form_card_background_color'] ?? ''),
                    ])
                    ->add('title_color', ColorType::class, [
                        'label' => 'Title Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['title_color'] ?? $existingData['titleColor'] ?? ''),
                    ])
                    ->add('subtitle_color', ColorType::class, [
                        'label' => 'Subtitle Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['subtitle_color'] ?? $existingData['subtitleColor'] ?? ''),
                    ])
                    ->add('label_color', ColorType::class, [
                        'label' => 'Label Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['label_color'] ?? ''),
                    ])
                    ->add('input_background_color', ColorType::class, [
                        'label' => 'Input Background Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['input_background_color'] ?? ''),
                    ])
                    ->add('input_text_color', ColorType::class, [
                        'label' => 'Input Text Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['input_text_color'] ?? ''),
                    ])
                    ->add('input_border_color', ColorType::class, [
                        'label' => 'Input Border Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['input_border_color'] ?? ''),
                    ])
                    ->add('input_border_radius', TextType::class, [
                        'label' => 'Input Border Radius (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['input_border_radius'] ?? ''),
                    ])
                    ->add('button_background_color', ColorType::class, [
                        'label' => 'Button Background Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['button_background_color'] ?? $existingData['buttonBackgroundColor'] ?? ''),
                    ])
                    ->add('button_text_color', ColorType::class, [
                        'label' => 'Button Text Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['button_text_color'] ?? $existingData['buttonTextColor'] ?? ''),
                    ])
                    ->add('button_border_color', ColorType::class, [
                        'label' => 'Button Border Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['button_border_color'] ?? $existingData['buttonBorderColor'] ?? ''),
                    ])
                    ->add('button_border_radius', TextType::class, [
                        'label' => 'Button Border Radius (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['button_border_radius'] ?? $existingData['buttonBorderRadius'] ?? ''),
                    ])
                    ->add('padding_top', TextType::class, [
                        'label' => 'Padding Top (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['padding_top'] ?? $existingData['paddingTop'] ?? '60'),
                    ])
                    ->add('padding_bottom', TextType::class, [
                        'label' => 'Padding Bottom (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['padding_bottom'] ?? $existingData['paddingBottom'] ?? '60'),
                    ])
                    ->add('margin_top', TextType::class, [
                        'label' => 'Margin Top (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['margin_top'] ?? $existingData['marginTop'] ?? ''),
                    ])
                    ->add('margin_bottom', TextType::class, [
                        'label' => 'Margin Bottom (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['margin_bottom'] ?? $existingData['marginBottom'] ?? ''),
                    ])
                    ->add('box_shadow', CheckboxType::class, [
                        'label' => 'Show Box Shadow',
                        'required' => false,
                        'data' => $existingData['box_shadow'] ?? false,
                    ]);
                break;

            case 'cta':
                $builder
                    // Content Fields
                    ->add('title', TextType::class, [
                        'label' => 'Title',
                        'required' => true,
                        'data' => $this->ensureString($existingData['title'] ?? ''),
                    ])
                    ->add('text', TextareaType::class, [
                        'label' => 'Text',
                        'required' => false,
                        'data' => $this->ensureString($existingData['text'] ?? ''),
                    ])
                    ->add('buttonText', TextType::class, [
                        'label' => 'Button Text',
                        'required' => true,
                        'data' => $this->ensureString($existingData['buttonText'] ?? 'Learn More'),
                    ])
                    ->add('buttonUrl', TextType::class, [
                        'label' => 'Button URL',
                        'required' => true,
                        'data' => $this->ensureString($existingData['buttonUrl'] ?? ''),
                    ])
                    
                    // Style Fields
                    ->add('backgroundColor', ColorType::class, [
                        'label' => 'CTA Background Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['backgroundColor'] ?? ''),
                    ])
                    ->add('textColor', ColorType::class, [
                        'label' => 'CTA Text Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['textColor'] ?? ''),
                    ])
                    ->add('titleColor', ColorType::class, [
                        'label' => 'CTA Title Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['titleColor'] ?? ''),
                    ])
                    ->add('buttonBackgroundColor', ColorType::class, [
                        'label' => 'Button Background Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['buttonBackgroundColor'] ?? ''),
                    ])
                    ->add('buttonTextColor', ColorType::class, [
                        'label' => 'Button Text Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['buttonTextColor'] ?? ''),
                    ])
                    ->add('buttonBorderColor', ColorType::class, [
                        'label' => 'Button Border Color',
                        'required' => false,
                        'data' => $this->ensureString($existingData['buttonBorderColor'] ?? ''),
                    ])
                    ->add('buttonBorderRadius', TextType::class, [
                        'label' => 'Button Border Radius (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['buttonBorderRadius'] ?? ''),
                    ])
                    ->add('buttonStyle', ChoiceType::class, [
                        'label' => 'Button Style',
                        'required' => false,
                        'choices' => [
                            'Primary' => 'primary',
                            'Secondary' => 'secondary',
                            'Outline' => 'outline',
                            'Ghost' => 'ghost',
                        ],
                        'data' => $existingData['buttonStyle'] ?? 'primary',
                        'placeholder' => 'Select style',
                    ])
                    ->add('textAlignment', ChoiceType::class, [
                        'label' => 'Text Alignment',
                        'required' => false,
                        'choices' => [
                            'Left' => 'left',
                            'Center' => 'center',
                            'Right' => 'right',
                        ],
                        'data' => $existingData['textAlignment'] ?? 'center',
                        'placeholder' => 'Select alignment',
                    ])
                    ->add('paddingTop', TextType::class, [
                        'label' => 'Padding Top (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['paddingTop'] ?? ''),
                    ])
                    ->add('paddingBottom', TextType::class, [
                        'label' => 'Padding Bottom (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['paddingBottom'] ?? ''),
                    ])
                    ->add('marginTop', TextType::class, [
                        'label' => 'Margin Top (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['marginTop'] ?? ''),
                    ])
                    ->add('marginBottom', TextType::class, [
                        'label' => 'Margin Bottom (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['marginBottom'] ?? ''),
                    ]);
                break;

            case 'footer':
                // Content Tab Fields
                $builder
                    ->add('company_name', TextType::class, [
                        'label' => 'Company Name',
                        'required' => false,
                        'data' => $this->ensureString($existingData['company_name'] ?? ''),
                    ])
                    ->add('company_description', TextareaType::class, [
                        'label' => 'Company Description',
                        'required' => false,
                        'attr' => ['rows' => 3],
                        'data' => $this->ensureString($existingData['company_description'] ?? ''),
                    ])
                    ->add('useful_links', TextareaType::class, [
                        'label' => 'Useful Links (Label|/url per line)',
                        'required' => false,
                        'attr' => ['rows' => 4, 'placeholder' => 'Home|/\nAbout|/about\nContact|/contact'],
                        'data' => $this->ensureString($existingData['useful_links'] ?? ''),
                    ])
                    ->add('address', TextareaType::class, [
                        'label' => 'Address',
                        'required' => false,
                        'attr' => ['rows' => 2],
                        'data' => $this->ensureString($existingData['address'] ?? ''),
                    ])
                    ->add('phone', TextType::class, [
                        'label' => 'Phone',
                        'required' => false,
                        'data' => $this->ensureString($existingData['phone'] ?? ''),
                    ])
                    ->add('email', EmailType::class, [
                        'label' => 'Email',
                        'required' => false,
                        'data' => $this->ensureString($existingData['email'] ?? ''),
                    ])
                    ->add('copyright_text', TextType::class, [
                        'label' => 'Copyright Text',
                        'required' => false,
                        'data' => $this->ensureString($existingData['copyright_text'] ?? ''),
                    ])
                    ->add('ctaText', TextType::class, [
                        'label' => 'CTA Text',
                        'required' => false,
                        'data' => $this->ensureString($existingData['ctaText'] ?? ''),
                    ])
                    ->add('ctaUrl', TextType::class, [
                        'label' => 'CTA URL',
                        'required' => false,
                        'data' => $this->ensureString($existingData['ctaUrl'] ?? ''),
                    ]);
                
                // Style Tab Fields
                $builder
                    ->add('background_color', ColorType::class, [
                        'label' => 'Background Color',
                        'required' => false,
                        'attr' => ['class' => 'color-picker-input'],
                        'data' => $this->ensureString($existingData['background_color'] ?? '#1a1a2e'),
                    ])
                    ->add('text_color', ColorType::class, [
                        'label' => 'Text Color',
                        'required' => false,
                        'attr' => ['class' => 'color-picker-input'],
                        'data' => $this->ensureString($existingData['text_color'] ?? '#ffffff'),
                    ])
                    ->add('heading_color', ColorType::class, [
                        'label' => 'Heading Color',
                        'required' => false,
                        'attr' => ['class' => 'color-picker-input'],
                        'data' => $this->ensureString($existingData['heading_color'] ?? '#ffffff'),
                    ])
                    ->add('link_color', ColorType::class, [
                        'label' => 'Link Color',
                        'required' => false,
                        'attr' => ['class' => 'color-picker-input'],
                        'data' => $this->ensureString($existingData['link_color'] ?? '#b8b8b8'),
                    ])
                    ->add('link_hover_color', ColorType::class, [
                        'label' => 'Link Hover Color',
                        'required' => false,
                        'attr' => ['class' => 'color-picker-input'],
                        'data' => $this->ensureString($existingData['link_hover_color'] ?? '#4a90e2'),
                    ])
                    ->add('border_top_color', ColorType::class, [
                        'label' => 'Border Top Color',
                        'required' => false,
                        'attr' => ['class' => 'color-picker-input'],
                        'data' => $this->ensureString($existingData['border_top_color'] ?? '#333333'),
                    ])
                    ->add('padding_top', TextType::class, [
                        'label' => 'Padding Top (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['padding_top'] ?? '60'),
                    ])
                    ->add('padding_bottom', TextType::class, [
                        'label' => 'Padding Bottom (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['padding_bottom'] ?? '40'),
                    ])
                    ->add('margin_top', TextType::class, [
                        'label' => 'Margin Top (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['margin_top'] ?? '0'),
                    ])
                    ->add('margin_bottom', TextType::class, [
                        'label' => 'Margin Bottom (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['margin_bottom'] ?? '0'),
                    ])
                    ->add('title_font_size', TextType::class, [
                        'label' => 'Title Font Size (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['title_font_size'] ?? '18'),
                    ])
                    ->add('text_font_size', TextType::class, [
                        'label' => 'Text Font Size (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['text_font_size'] ?? '14'),
                    ])
                    ->add('link_font_size', TextType::class, [
                        'label' => 'Link Font Size (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['link_font_size'] ?? '14'),
                    ])
                    ->add('text_alignment', ChoiceType::class, [
                        'label' => 'Text Alignment',
                        'required' => false,
                        'choices' => [
                            'Left' => 'left',
                            'Center' => 'center',
                            'Right' => 'right',
                        ],
                        'data' => $existingData['text_alignment'] ?? 'left',
                    ])
                    ->add('column_gap', TextType::class, [
                        'label' => 'Column Gap (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['column_gap'] ?? '30'),
                    ])
                    ->add('columns_count', ChoiceType::class, [
                        'label' => 'Columns Count',
                        'required' => false,
                        'choices' => [
                            '2 Columns' => 2,
                            '3 Columns' => 3,
                            '4 Columns' => 4,
                        ],
                        'data' => isset($existingData['columns_count']) ? (int) $existingData['columns_count'] : 3,
                    ])
                    ->add('border_radius', TextType::class, [
                        'label' => 'Border Radius (px)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['border_radius'] ?? '8'),
                    ])
                    ->add('box_shadow', ChoiceType::class, [
                        'label' => 'Box Shadow',
                        'required' => false,
                        'choices' => [
                            'None' => 'none',
                            'Small' => '0 2px 4px rgba(0,0,0,0.1)',
                            'Medium' => '0 4px 8px rgba(0,0,0,0.15)',
                            'Large' => '0 8px 16px rgba(0,0,0,0.2)',
                        ],
                        'data' => $existingData['box_shadow'] ?? 'none',
                    ]);
                
                // Layout Tab Fields
                $builder
                    ->add('container_width', ChoiceType::class, [
                        'label' => 'Container Width',
                        'required' => false,
                        'choices' => [
                            'Full Width' => '100%',
                            'Large (1140px)' => '1140px',
                            'Medium (960px)' => '960px',
                            'Small (720px)' => '720px',
                        ],
                        'data' => $existingData['container_width'] ?? '1140px',
                    ])
                    ->add('layout_type', ChoiceType::class, [
                        'label' => 'Layout Type',
                        'required' => false,
                        'choices' => [
                            'Classic (3 columns)' => 'classic',
                            'Modern (4 columns)' => 'modern',
                            'Simple (2 columns)' => 'simple',
                            'Minimal (1 column)' => 'minimal',
                        ],
                        'data' => $existingData['layout_type'] ?? 'classic',
                    ])
                    ->add('stack_on_mobile', ChoiceType::class, [
                        'label' => 'Stack on Mobile',
                        'required' => false,
                        'choices' => [
                            'Yes - Stack vertically' => true,
                            'No - Keep inline' => false,
                        ],
                        'data' => isset($existingData['stack_on_mobile']) ? (bool) $existingData['stack_on_mobile'] : true,
                    ])
                    ->add('show_divider', ChoiceType::class, [
                        'label' => 'Show Divider',
                        'required' => false,
                        'choices' => [
                            'Yes' => true,
                            'No' => false,
                        ],
                        'data' => isset($existingData['show_divider']) ? (bool) $existingData['show_divider'] : true,
                    ]);
                break;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'type' => '',
            'existing_data' => [],
        ]);
    }
}

class CardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Title'])
            ->add('description', TextareaType::class, ['label' => 'Description', 'attr' => ['rows' => 3]])
            ->add('imageUrl', TextType::class, ['label' => 'Image URL', 'required' => false])
            ->add('linkUrl', TextType::class, ['label' => 'Link URL', 'required' => false]);
    }
}

class FaqItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', TextType::class, ['label' => 'Question'])
            ->add('answer', TextareaType::class, ['label' => 'Answer', 'attr' => ['rows' => 3]]);
    }
}

<?php

namespace App\Form;

use App\Entity\PageSection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
                    // Content Fields
                    ->add('title', TextType::class, [
                        'label' => 'Title',
                        'required' => true,
                        'data' => $this->ensureString($existingData['title'] ?? ''),
                    ])
                    ->add('subtitle', TextareaType::class, [
                        'label' => 'Subtitle',
                        'required' => false,
                        'data' => $this->ensureString($existingData['subtitle'] ?? ''),
                    ])
                    ->add('imageUrl', TextType::class, [
                        'label' => 'Image URL',
                        'required' => false,
                        'data' => $this->ensureString($existingData['imageUrl'] ?? ''),
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
                    ->add('showForm', CheckboxType::class, [
                        'label' => 'Show Form',
                        'required' => false,
                        'data' => $existingData['showForm'] ?? false,
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
                    ->add('title', TextType::class, [
                        'label' => 'Form Title',
                        'required' => true,
                        'data' => $this->ensureString($existingData['title'] ?? ''),
                    ])
                    ->add('fields', ChoiceType::class, [
                        'label' => 'Fields',
                        'required' => true,
                        'multiple' => true,
                        'expanded' => true,
                        'choices' => [
                            'Name' => 'name',
                            'Email' => 'email',
                            'Phone' => 'phone',
                            'Message' => 'message',
                        ],
                        'data' => $existingData['fields'] ?? [],
                    ])
                    ->add('submitText', TextType::class, [
                        'label' => 'Submit Button Text',
                        'required' => true,
                        'data' => $this->ensureString($existingData['submitText'] ?? 'Submit'),
                    ])
                    ->add('successMessage', TextareaType::class, [
                        'label' => 'Success Message',
                        'required' => false,
                        'data' => $this->ensureString($existingData['successMessage'] ?? ''),
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
                $builder
                    ->add('text', TextareaType::class, [
                        'label' => 'Footer Text',
                        'required' => true,
                        'data' => $this->ensureString($existingData['text'] ?? ''),
                    ])
                    ->add('links', TextareaType::class, [
                        'label' => 'Links (Label|/url per line)',
                        'required' => false,
                        'data' => $this->ensureString($existingData['links'] ?? ''),
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

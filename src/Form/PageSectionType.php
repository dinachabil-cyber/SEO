<?php

namespace App\Form;

use App\Entity\PageSection;
use App\Entity\ReferenceSection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\Form\FormFieldType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PageSectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Determine if we should add the name field
        $dataClass = $options['data_class'];
        $shouldAddName = false;
        
        // Add name field if data class is ReferenceSection or if name is in only_fields
        if ($dataClass === ReferenceSection::class || ($options['only_fields'] && in_array('name', $options['only_fields']))) {
            $shouldAddName = true;
            $builder->add('name', TextType::class, [
                'label' => 'Section Name',
                'constraints' => [new Assert\NotBlank()],
            ]);
        }

        // Add type field only if not restricted or type is in allowed fields
        if (!$options['only_fields'] || in_array('type', $options['only_fields'])) {
            $builder->add('type', ChoiceType::class, [
                'choices' => [
                    'Header' => 'header',
                    'Hero' => 'hero',
                    'Hero Split (Image + Form)' => 'hero_split',
                    'Body' => 'body',
                    'Image' => 'image',
                    'Cards' => 'cards_premium',
                    'FAQ' => 'faq',
                    'Form' => 'form',
                    'CTA' => 'cta',
                    'Footer' => 'footer',
                ],
                'label' => 'Section Type',
                'placeholder' => 'Choose a section type',
                'constraints' => [new Assert\NotBlank()],
            ]);
        }

        // Only add dynamic fields if not restricting fields or data is allowed
        if (!$options['only_fields'] || in_array('data', $options['only_fields'])) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $section = $event->getData();
                $form = $event->getForm();

                if ($section && $section->getType()) {
                    self::addDynamicFields($form, $section->getType(), $section->getData() ?? []);
                }
            });

            $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();

                if (!empty($data['type'])) {
                    self::addDynamicFields($form, $data['type'], $data['data'] ?? []);
                }
            });
        }
    }

    public static function addDynamicFields($form, string $type, array $existingData = []): void
    {
        $form->add('data', SectionDataType::class, [
            'type' => $type,
            'existing_data' => $existingData,
            'label' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'only_fields' => null,
        ]);
    }
}

class SectionDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $type = $options['type'];
        $data = $options['existing_data'] ?? [];

        // Listen for form submit to restructure hero_split form data
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($type) {
            if ($type !== 'hero_split') {
                return;
            }
            
            $formData = $event->getData();
            
            // Restructure form fields to be nested under form object
            $formData['form'] = [
                'title' => $formData['formTitle'] ?? '',
                'submitText' => $formData['submitText'] ?? 'Envoyer',
                'consentText' => $formData['consentText'] ?? '',
                'successMessage' => $formData['successMessage'] ?? '',
                'fields' => $formData['formFields'] ?? []
            ];
            
            // Remove top-level form fields to avoid duplication
            unset($formData['formTitle']);
            unset($formData['formFields']);
            unset($formData['submitText']);
            unset($formData['consentText']);
            unset($formData['successMessage']);
            
            $event->setData($formData);
        });

        // Listen for form submit to restructure style-related fields
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($type) {
            $formData = $event->getData();
            
            // For all section types, move style fields to style array
            $styleFields = [
                'variant',
                'sticky',
                'background',
                'textColor',
                'backgroundVariant',
                'layout',
                'columns',
                'cardVariant',
                'accentColor',
                'buttonColor',
                'buttonCustomColor',
                'buttonStyle',
                'borderColor',
                'shadow',
                'rounded',
                'maxWidth',
                'textAlign',
                'align',
                'accordionVariant',
            ];
            
            $styleData = [];
            foreach ($styleFields as $field) {
                if (isset($formData[$field])) {
                    $styleData[$field] = $formData[$field];
                    unset($formData[$field]);
                }
            }
            
            if (!empty($styleData)) {
                $formData['style'] = $styleData;
            }
            
            // Special handling for form section
            if ($type === 'form') {
                // Ensure fields are properly structured
                if (isset($formData['fields']) && !is_array($formData['fields'])) {
                    $formData['fields'] = [];
                }
            }
            
            $event->setData($formData);
        });

        // Initialize form data when form is created
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($type) {
            $data = $event->getData();
            
            if ($type === 'hero_split') {
                // Ensure form structure exists
                if (!isset($data['form']) || !is_array($data['form'])) {
                    $data['form'] = [
                        'title' => '',
                        'submitText' => 'Envoyer',
                        'consentText' => '',
                        'successMessage' => '',
                        'fields' => []
                    ];
                }
                
                // Ensure fields array exists
                if (!isset($data['form']['fields']) || !is_array($data['form']['fields'])) {
                    $data['form']['fields'] = [];
                }
                
                $event->setData($data);
            } elseif ($type === 'form') {
                // Ensure fields array exists
                if (!isset($data['fields']) || !is_array($data['fields'])) {
                    $data['fields'] = [];
                }
                
                $event->setData($data);
            }
        });

        switch ($type) {
            case 'header':
                $builder
                    ->add('brandText', TextType::class, [
                        'label' => 'Brand Text',
                        'data' => $data['brandText'] ?? '',
                    ])
                    ->add('logoUrl', TextType::class, [
                        'label' => 'Logo URL',
                        'required' => false,
                        'data' => $data['logoUrl'] ?? '',
                    ])
                    ->add('menuItems', CollectionType::class, [
                        'entry_type' => MenuItemType::class,
                        'entry_options' => ['label' => false],
                        'allow_add' => true,
                        'allow_delete' => true,
                        'prototype' => true,
                        'data' => $data['menuItems'] ?? [],
                        'label' => 'Menu Items',
                    ])
                    ->add('ctaText', TextType::class, [
                        'label' => 'CTA Text',
                        'required' => false,
                        'data' => $data['ctaText'] ?? '',
                    ])
                    ->add('ctaUrl', TextType::class, [
                        'label' => 'CTA URL',
                        'required' => false,
                        'data' => $data['ctaUrl'] ?? '',
                    ])
                    ->add('variant', ChoiceType::class, [
                        'label' => 'Variant',
                        'choices' => [
                            'Light' => 'light',
                            'Dark' => 'dark',
                        ],
                        'data' => $data['style']['variant'] ?? 'light',
                    ])
                    ->add('sticky', CheckboxType::class, [
                        'label' => 'Sticky',
                        'required' => false,
                        'data' => $data['style']['sticky'] ?? false,
                    ])
                    ->add('background', ColorType::class, [
                        'label' => 'Background',
                        'required' => false,
                        'data' => $data['style']['background'] ?? null,
                    ]);
                break;

            case 'hero':
                $builder
                    ->add('title', TextType::class, [
                        'label' => 'Title',
                        'data' => $data['title'] ?? '',
                    ])
                    ->add('subtitle', TextareaType::class, [
                        'label' => 'Subtitle',
                        'required' => false,
                        'data' => $data['subtitle'] ?? '',
                    ])
                    ->add('ctaText', TextType::class, [
                        'label' => 'CTA Text',
                        'required' => false,
                        'data' => $data['ctaText'] ?? '',
                    ])
                    ->add('ctaUrl', TextType::class, [
                        'label' => 'CTA URL',
                        'required' => false,
                        'data' => $data['ctaUrl'] ?? '',
                    ])
                    ->add('backgroundVariant', ChoiceType::class, [
                        'label' => 'Background Variant',
                        'choices' => [
                            'Surface' => 'surface',
                            'Light' => 'light',
                            'Gradient' => 'gradient',
                        ],
                        'data' => $data['style']['backgroundVariant'] ?? 'surface',
                    ])
                    ->add('background', ColorType::class, [
                        'label' => 'Background Color',
                        'required' => false,
                        'data' => $data['style']['background'] ?? '',
                    ])
                    ->add('textColor', ColorType::class, [
                        'label' => 'Text Color',
                        'required' => false,
                        'data' => $data['style']['textColor'] ?? '',
                    ])
                    ->add('accentColor', ColorType::class, [
                        'label' => 'Accent Color',
                        'required' => false,
                        'data' => $data['style']['accentColor'] ?? '',
                    ])
                    ->add('buttonColor', ChoiceType::class, [
                        'label' => 'Button Color',
                        'choices' => [
                            'Primary' => 'primary',
                            'Secondary' => 'secondary',
                            'Accent' => 'accent',
                            'Success' => 'success',
                            'Warning' => 'warning',
                            'Danger' => 'danger',
                            'Custom' => 'custom',
                        ],
                        'data' => $data['style']['buttonColor'] ?? 'primary',
                    ])
                    ->add('buttonCustomColor', ColorType::class, [
                        'label' => 'Custom Button Color',
                        'required' => false,
                        'data' => $data['style']['buttonCustomColor'] ?? '',
                    ]);
                break;

            case 'hero_split':
                $builder
                    ->add('title', TextType::class, [
                        'label' => 'Hero Title',
                        'required' => false,
                        'data' => $data['title'] ?? '',
                    ])
                    ->add('subtitle', TextareaType::class, [
                        'label' => 'Hero Subtitle',
                        'required' => false,
                        'data' => $data['subtitle'] ?? '',
                    ])
                    ->add('imageUrl', TextType::class, [
                        'label' => 'Image URL',
                        'required' => false,
                        'data' => $data['imageUrl'] ?? '',
                    ])
                    ->add('imageAlt', TextType::class, [
                        'label' => 'Image Alt',
                        'required' => false,
                        'data' => $data['imageAlt'] ?? '',
                    ])
                    ->add('formTitle', TextType::class, [
                        'label' => 'Form Title',
                        'required' => false,
                        'data' => $data['form']['title'] ?? '',
                    ])
                    ->add('formFields', CollectionType::class, [
                        'entry_type' => FormFieldType::class,
                        'entry_options' => ['label' => false],
                        'allow_add' => true,
                        'allow_delete' => true,
                        'prototype' => true,
                        'data' => $data['form']['fields'] ?? [],
                        'label' => 'Form Fields',
                    ])
                    ->add('submitText', TextType::class, [
                        'label' => 'Submit Button Text',
                        'required' => false,
                        'data' => $data['form']['submitText'] ?? 'Envoyer',
                    ])
                    ->add('consentText', TextareaType::class, [
                        'label' => 'Consent Text',
                        'required' => false,
                        'data' => $data['form']['consentText'] ?? '',
                    ])
                    ->add('successMessage', TextareaType::class, [
                        'label' => 'Success Message',
                        'required' => false,
                        'data' => $data['form']['successMessage'] ?? '',
                    ])
                    ->add('layout', ChoiceType::class, [
                        'label' => 'Layout',
                        'choices' => [
                            'Image Left / Form Right' => 'image-left-form-right',
                            'Form Left / Image Right' => 'form-left-image-right',
                        ],
                        'data' => $data['style']['layout'] ?? 'image-left-form-right',
                    ])
                    ->add('backgroundVariant', ChoiceType::class, [
                        'label' => 'Background Variant',
                        'choices' => [
                            'Surface' => 'surface',
                            'Light' => 'light',
                            'Gradient' => 'gradient',
                        ],
                        'data' => $data['style']['backgroundVariant'] ?? 'light',
                    ])
                    ->add('buttonColor', ChoiceType::class, [
                        'label' => 'Button Color',
                        'choices' => [
                            'Primary' => 'primary',
                            'Secondary' => 'secondary',
                            'Accent' => 'accent',
                            'Success' => 'success',
                            'Warning' => 'warning',
                            'Danger' => 'danger',
                            'Custom' => 'custom',
                        ],
                        'data' => $data['style']['buttonColor'] ?? 'primary',
                    ])
                    ->add('buttonCustomColor', ColorType::class, [
                        'label' => 'Custom Button Color',
                        'required' => false,
                        'data' => $data['style']['buttonCustomColor'] ?? '',
                    ]);
                break;

            case 'body':
                $builder
                    ->add('title', TextType::class, [
                        'label' => 'Title',
                        'required' => false,
                        'data' => $data['title'] ?? '',
                    ])
                    ->add('content', TextareaType::class, [
                        'label' => 'Content',
                        'attr' => ['rows' => 8],
                        'data' => $data['content'] ?? '',
                    ]);
                break;

            case 'image':
                $builder
                    ->add('imageUrl', TextType::class, [
                        'label' => 'Image URL',
                        'data' => $data['imageUrl'] ?? '',
                    ])
                    ->add('alt', TextType::class, [
                        'label' => 'Alt',
                        'data' => $data['alt'] ?? '',
                    ])
                    ->add('caption', TextareaType::class, [
                        'label' => 'Caption',
                        'required' => false,
                        'data' => $data['caption'] ?? '',
                    ]);
                break;

            case 'cards_premium':
                $builder
                    ->add('sectionTitle', TextType::class, [
                        'label' => 'Section Title',
                        'required' => false,
                        'data' => $data['sectionTitle'] ?? '',
                    ])
                    ->add('sectionSubtitle', TextareaType::class, [
                        'label' => 'Section Subtitle',
                        'required' => false,
                        'attr' => ['rows' => 2],
                        'data' => $data['sectionSubtitle'] ?? '',
                    ])
                    ->add('cards', CollectionType::class, [
                        'entry_type' => CardType::class,
                        'entry_options' => ['label' => false],
                        'allow_add' => true,
                        'allow_delete' => true,
                        'prototype' => true,
                        'data' => $data['cards'] ?? [],
                        'label' => 'Cards',
                    ])
                    ->add('background', ColorType::class, [
                        'label' => 'Section Background Color',
                        'required' => false,
                        'data' => $data['style']['background'] ?? '#f8f9fa',
                    ])
                    ->add('textColor', ColorType::class, [
                        'label' => 'Text Color',
                        'required' => false,
                        'data' => $data['style']['textColor'] ?? '',
                    ])
                    ->add('buttonColor', ChoiceType::class, [
                        'label' => 'Button Color',
                        'choices' => [
                            'Primary' => 'primary',
                            'Secondary' => 'secondary',
                            'Accent' => 'accent',
                            'Success' => 'success',
                            'Warning' => 'warning',
                            'Danger' => 'danger',
                            'Custom' => 'custom',
                        ],
                        'data' => $data['style']['buttonColor'] ?? 'warning',
                    ])
                    ->add('buttonCustomColor', ColorType::class, [
                        'label' => 'Custom Button Color',
                        'required' => false,
                        'data' => $data['style']['buttonCustomColor'] ?? '',
                    ]);
                break;
                
            case 'cards':
                $builder
                    ->add('sectionTitle', TextType::class, [
                        'label' => 'Section Title',
                        'required' => false,
                        'data' => $data['sectionTitle'] ?? '',
                    ])
                    ->add('cards', CollectionType::class, [
                        'entry_type' => CardType::class,
                        'entry_options' => ['label' => false],
                        'allow_add' => true,
                        'allow_delete' => true,
                        'prototype' => true,
                        'data' => $data['cards'] ?? [],
                        'label' => 'Cards',
                    ])
                    ->add('columns', ChoiceType::class, [
                        'label' => 'Columns',
                        'choices' => [
                            '2' => 2,
                            '3' => 3,
                            '4' => 4,
                        ],
                        'data' => $data['style']['columns'] ?? 3,
                    ])
                    ->add('cardVariant', ChoiceType::class, [
                        'label' => 'Card Variant',
                        'choices' => [
                            'Solid' => 'solid',
                            'Outline' => 'outline',
                            'Glass' => 'glass',
                        ],
                        'data' => $data['style']['cardVariant'] ?? 'solid',
                    ])
                    ->add('background', ColorType::class, [
                        'label' => 'Card Background Color',
                        'required' => false,
                        'data' => $data['style']['background'] ?? '',
                    ])
                    ->add('textColor', ColorType::class, [
                        'label' => 'Card Text Color',
                        'required' => false,
                        'data' => $data['style']['textColor'] ?? '',
                    ])
                    ->add('buttonColor', ChoiceType::class, [
                        'label' => 'Button Color',
                        'choices' => [
                            'Primary' => 'primary',
                            'Secondary' => 'secondary',
                            'Accent' => 'accent',
                            'Success' => 'success',
                            'Warning' => 'warning',
                            'Danger' => 'danger',
                            'Custom' => 'custom',
                        ],
                        'data' => $data['style']['buttonColor'] ?? 'primary',
                    ])
                    ->add('buttonCustomColor', ColorType::class, [
                        'label' => 'Custom Button Color',
                        'required' => false,
                        'data' => $data['style']['buttonCustomColor'] ?? '',
                    ])
                    ->add('borderColor', ColorType::class, [
                        'label' => 'Card Border Color',
                        'required' => false,
                        'data' => $data['style']['borderColor'] ?? '',
                    ])
                    ->add('shadow', CheckboxType::class, [
                        'label' => 'Card Shadow',
                        'required' => false,
                        'data' => $data['style']['shadow'] ?? false,
                    ])
                    ->add('rounded', CheckboxType::class, [
                        'label' => 'Rounded Corners',
                        'required' => false,
                        'data' => $data['style']['rounded'] ?? false,
                    ]);
                break;

            case 'faq':
                $builder
                    ->add('sectionTitle', TextType::class, [
                        'label' => 'Section Title',
                        'required' => false,
                        'data' => $data['sectionTitle'] ?? '',
                    ])
                    ->add('items', CollectionType::class, [
                        'entry_type' => FaqItemType::class,
                        'entry_options' => ['label' => false],
                        'allow_add' => true,
                        'allow_delete' => true,
                        'prototype' => true,
                        'data' => $data['items'] ?? [],
                        'label' => 'FAQ Items',
                    ]);
                break;

            case 'form':
                $builder
                    ->add('title', TextType::class, [
                        'label' => 'Form Title',
                        'data' => $data['title'] ?? '',
                    ])
                    ->add('fields', CollectionType::class, [
                        'entry_type' => FormFieldType::class,
                        'entry_options' => ['label' => false],
                        'allow_add' => true,
                        'allow_delete' => true,
                        'prototype' => true,
                        'data' => $data['fields'] ?? [],
                        'label' => 'Fields',
                    ])
                    ->add('submitText', TextType::class, [
                        'label' => 'Submit Text',
                        'data' => $data['submitText'] ?? 'Envoyer',
                    ])
                    ->add('successMessage', TextareaType::class, [
                        'label' => 'Success Message',
                        'required' => false,
                        'data' => $data['successMessage'] ?? '',
                    ])
                    ->add('consentText', TextareaType::class, [
                        'label' => 'Consent Text',
                        'required' => false,
                        'data' => $data['consentText'] ?? '',
                    ])
                    ->add('background', ColorType::class, [
                        'label' => 'Background Color',
                        'required' => false,
                        'data' => $data['style']['background'] ?? '',
                    ])
                    ->add('textColor', ColorType::class, [
                        'label' => 'Text Color',
                        'required' => false,
                        'data' => $data['style']['textColor'] ?? '',
                    ])
                    ->add('buttonColor', ChoiceType::class, [
                        'label' => 'Button Color',
                        'choices' => [
                            'Primary' => 'primary',
                            'Secondary' => 'secondary',
                            'Accent' => 'accent',
                            'Success' => 'success',
                            'Warning' => 'warning',
                            'Danger' => 'danger',
                            'Custom' => 'custom',
                        ],
                        'data' => $data['style']['buttonColor'] ?? 'primary',
                    ])
                    ->add('buttonCustomColor', ColorType::class, [
                        'label' => 'Custom Button Color',
                        'required' => false,
                        'data' => $data['style']['buttonCustomColor'] ?? '',
                    ]);
                break;

            case 'cta':
                $builder
                    ->add('title', TextType::class, [
                        'label' => 'Title',
                        'data' => $data['title'] ?? '',
                    ])
                    ->add('text', TextareaType::class, [
                        'label' => 'Text',
                        'required' => false,
                        'data' => $data['text'] ?? '',
                    ])
                    ->add('icon', TextType::class, [
                        'label' => 'Icon (optional)',
                        'required' => false,
                        'data' => $data['icon'] ?? '',
                    ])
                    ->add('iconEmoji', TextType::class, [
                        'label' => 'Icon Emoji (optional)',
                        'required' => false,
                        'data' => $data['iconEmoji'] ?? '',
                    ])
                    ->add('buttonText', TextType::class, [
                        'label' => 'Button Text',
                        'data' => $data['buttonText'] ?? '',
                    ])
                    ->add('buttonUrl', TextType::class, [
                        'label' => 'Button URL',
                        'data' => $data['buttonUrl'] ?? '',
                    ])
                    ->add('backgroundVariant', ChoiceType::class, [
                        'label' => 'Background',
                        'choices' => [
                            'Primary' => 'primary',
                            'Gradient' => 'gradient',
                            'Surface' => 'surface',
                            'Warning' => 'warning',
                        ],
                        'data' => $data['style']['backgroundVariant'] ?? 'primary',
                    ])
                    ->add('background', ColorType::class, [
                        'label' => 'Background Color',
                        'required' => false,
                        'data' => $data['style']['background'] ?? '',
                    ])
                    ->add('textColor', ColorType::class, [
                        'label' => 'Text Color',
                        'required' => false,
                        'data' => $data['style']['textColor'] ?? '',
                    ])
                    ->add('buttonColor', ChoiceType::class, [
                        'label' => 'Button Color',
                        'choices' => [
                            'Primary' => 'primary',
                            'Secondary' => 'secondary',
                            'Accent' => 'accent',
                            'Success' => 'success',
                            'Warning' => 'warning',
                            'Danger' => 'danger',
                            'Custom' => 'custom',
                        ],
                        'data' => $data['style']['buttonColor'] ?? 'primary',
                    ])
                    ->add('buttonCustomColor', ColorType::class, [
                        'label' => 'Custom Button Color',
                        'required' => false,
                        'data' => $data['style']['buttonCustomColor'] ?? '',
                    ]);
                break;

            case 'footer':
                $builder
                    ->add('brandName', TextType::class, [
                        'label' => 'Brand Name',
                        'required' => false,
                        'data' => $data['brandName'] ?? '',
                    ])
                    ->add('description', TextareaType::class, [
                        'label' => 'Description',
                        'required' => false,
                        'data' => $data['description'] ?? '',
                    ])
                    ->add('usefulLinks', CollectionType::class, [
                        'entry_type' => MenuItemType::class,
                        'entry_options' => ['label' => false],
                        'allow_add' => true,
                        'allow_delete' => true,
                        'prototype' => true,
                        'data' => $data['usefulLinks'] ?? [],
                        'label' => 'Useful Links',
                    ])
                    ->add('address', TextareaType::class, [
                        'label' => 'Address',
                        'required' => false,
                        'data' => $data['address'] ?? '',
                    ])
                    ->add('phone', TextType::class, [
                        'label' => 'Phone',
                        'required' => false,
                        'data' => $data['phone'] ?? '',
                    ])
                    ->add('email', EmailType::class, [
                        'label' => 'Email',
                        'required' => false,
                        'data' => $data['email'] ?? '',
                    ])
                    ->add('copyright', TextType::class, [
                        'label' => 'Copyright',
                        'required' => false,
                        'data' => $data['copyright'] ?? '',
                    ])
                    ->add('variant', ChoiceType::class, [
                        'label' => 'Variant',
                        'choices' => [
                            'Light' => 'light',
                            'Dark' => 'dark',
                        ],
                        'data' => $data['style']['variant'] ?? 'dark',
                    ])
                    ->add('background', ColorType::class, [
                        'label' => 'Footer Background Color',
                        'required' => false,
                        'data' => $data['style']['background'] ?? '#111827'
                    ])
                    ->add('textColor', ColorType::class, [
                        'label' => 'Footer Text Color',
                        'required' => false,
                        'data' => $data['style']['textColor'] ?? '#ffffff',
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
}class MenuItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, ['label' => 'Label'])
            ->add('url', TextType::class, ['label' => 'URL']);
    }
}class CardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Title'])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['rows' => 3],
            ])
            ->add('features', TextareaType::class, [
                'label' => 'Features (one per line)',
                'required' => false,
                'attr' => ['rows' => 4, 'placeholder' => 'Feature 1
Feature 2
Feature 3'],
            ])
            ->add('icon', TextType::class, [
                'label' => 'Icon (optional)',
                'required' => false,
            ])
            ->add('iconEmoji', TextType::class, [
                'label' => 'Icon Emoji (optional)',
                'required' => false,
            ])
            ->add('imageUrl', TextType::class, [
                'label' => 'Image URL',
                'required' => false,
            ])
            ->add('linkUrl', TextType::class, [
                'label' => 'Link URL',
                'required' => false,
            ])
            ->add('buttonText', TextType::class, [
                'label' => 'Button Text',
                'required' => false,
            ])
            ->add('badge', TextType::class, [
                'label' => 'Badge',
                'required' => false,
            ]);
    }
}class FaqItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', TextType::class, ['label' => 'Question'])
            ->add('answer', TextareaType::class, [
                'label' => 'Answer',
                'attr' => ['rows' => 3],
            ]);
    }
}
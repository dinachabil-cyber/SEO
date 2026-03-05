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
                $this->addDynamicFields($form, $section->getType(), $section->getData());
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (isset($data['type'])) {
                $this->addDynamicFields($form, $data['type'], $data['data'] ?? []);
            }
        });
    }

    public static function addDynamicFields($form, $type, $existingData = [])
    {
        // Handle form fields compatibility - convert old string array to new format if needed
        if ($type === 'form' && isset($existingData['fields']) && is_array($existingData['fields'])) {
            $convertedFields = [];
            foreach ($existingData['fields'] as $field) {
                if (is_string($field)) {
                    $convertedFields[] = [
                        'name' => $field,
                        'label' => ucfirst($field),
                        'type' => $field === 'message' ? 'textarea' : 'text',
                        'required' => true,
                        'placeholder' => 'Your ' . ucfirst($field),
                        'options' => '',
                        'width' => 'full'
                    ];
                } else {
                    $convertedFields[] = $field;
                }
            }
            $existingData['fields'] = $convertedFields;
        }

        $form->add('data', SectionDataType::class, [
            'type' => $type,
            'existing_data' => $existingData,
            'label' => false,
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
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $type = $options['type'];
            $existingData = $options['existing_data'];

            switch ($type) {
                case 'header':
                    $builder
                        ->add('brandText', TextType::class, [
                            'label' => 'Brand Text',
                            'required' => true,
                            'data' => $existingData['brandText'] ?? '',
                        ])
                        ->add('logoUrl', TextType::class, [
                            'label' => 'Logo URL',
                            'required' => false,
                            'data' => $existingData['logoUrl'] ?? '',
                        ])
                        ->add('menuItems', TextareaType::class, [
                            'label' => 'Menu Items (Label|/url per line)',
                            'required' => false,
                            'data' => $existingData['menuItems'] ?? '',
                        ])
                        ->add('ctaText', TextType::class, [
                            'label' => 'CTA Text',
                            'required' => true,
                            'data' => $existingData['ctaText'] ?? '',
                        ])
                        ->add('ctaUrl', TextType::class, [
                            'label' => 'CTA URL',
                            'required' => true,
                            'data' => $existingData['ctaUrl'] ?? '',
                        ])
                        ->add('style', ChoiceType::class, [
                            'label' => 'Style',
                            'required' => true,
                            'choices' => [
                                'Light' => 'light',
                                'Dark' => 'dark',
                            ],
                            'data' => $existingData['style']['variant'] ?? 'light',
                        ])
                        ->add('sticky', CheckboxType::class, [
                            'label' => 'Sticky Header',
                            'required' => false,
                            'data' => $existingData['style']['sticky'] ?? false,
                        ])
                        ->add('background', ColorType::class, [
                            'label' => 'Background Color',
                            'required' => false,
                            'data' => $existingData['style']['background'] ?? '',
                        ]);
                    break;

                case 'hero':
                    $builder
                        ->add('title', TextType::class, [
                            'label' => 'Title',
                            'required' => true,
                            'data' => $existingData['title'] ?? '',
                        ])
                        ->add('subtitle', TextareaType::class, [
                            'label' => 'Subtitle',
                            'required' => false,
                            'data' => $existingData['subtitle'] ?? '',
                        ])
                        ->add('imageUrl', TextType::class, [
                            'label' => 'Image URL',
                            'required' => false,
                            'data' => $existingData['imageUrl'] ?? '',
                        ])
                        ->add('ctaText', TextType::class, [
                            'label' => 'CTA Text',
                            'required' => true,
                            'data' => $existingData['ctaText'] ?? '',
                        ])
                        ->add('ctaUrl', TextType::class, [
                            'label' => 'CTA URL',
                            'required' => true,
                            'data' => $existingData['ctaUrl'] ?? '',
                        ])
                        ->add('bullets', TextareaType::class, [
                            'label' => 'Bullets (one per line)',
                            'required' => false,
                            'data' => $existingData['bullets'] ?? '',
                        ])
                        ->add('layout', ChoiceType::class, [
                            'label' => 'Layout',
                            'required' => true,
                            'choices' => [
                                'Left' => 'left',
                                'Right' => 'right',
                            ],
                            'data' => $existingData['style']['layout'] ?? 'left',
                        ])
                        ->add('background', ColorType::class, [
                            'label' => 'Background Color',
                            'required' => false,
                            'data' => $existingData['style']['background'] ?? '',
                        ]);
                    break;

                case 'body':
                    $builder
                        ->add('title', TextType::class, [
                            'label' => 'Title',
                            'required' => false,
                            'data' => $existingData['title'] ?? '',
                        ])
                        ->add('content', TextareaType::class, [
                            'label' => 'Content',
                            'required' => true,
                            'data' => $existingData['content'] ?? '',
                            'attr' => ['rows' => 10],
                        ])
                        ->add('maxWidth', ChoiceType::class, [
                            'label' => 'Max Width',
                            'required' => true,
                            'choices' => [
                                'Normal' => 'normal',
                                'Narrow' => 'narrow',
                            ],
                            'data' => $existingData['style']['maxWidth'] ?? 'normal',
                        ])
                        ->add('textAlign', ChoiceType::class, [
                            'label' => 'Text Align',
                            'required' => true,
                            'choices' => [
                                'Left' => 'left',
                                'Center' => 'center',
                            ],
                            'data' => $existingData['style']['textAlign'] ?? 'left',
                        ]);
                    break;

                case 'image':
                    $builder
                        ->add('imageUrl', TextType::class, [
                            'label' => 'Image URL',
                            'required' => true,
                            'data' => $existingData['imageUrl'] ?? '',
                        ])
                        ->add('alt', TextType::class, [
                            'label' => 'Alt Text',
                            'required' => true,
                            'data' => $existingData['alt'] ?? '',
                        ])
                        ->add('caption', TextareaType::class, [
                            'label' => 'Caption',
                            'required' => false,
                            'data' => $existingData['caption'] ?? '',
                        ])
                        ->add('rounded', CheckboxType::class, [
                            'label' => 'Rounded Corners',
                            'required' => false,
                            'data' => $existingData['style']['rounded'] ?? false,
                        ])
                        ->add('shadow', CheckboxType::class, [
                            'label' => 'Shadow',
                            'required' => false,
                            'data' => $existingData['style']['shadow'] ?? false,
                        ]);
                    break;

                case 'cards':
                    $builder
                        ->add('sectionTitle', TextType::class, [
                            'label' => 'Section Title',
                            'required' => false,
                            'data' => $existingData['sectionTitle'] ?? '',
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
                        ->add('columns', ChoiceType::class, [
                            'label' => 'Columns',
                            'required' => true,
                            'choices' => [
                                '2 Columns' => '2',
                                '3 Columns' => '3',
                                '4 Columns' => '4',
                            ],
                            'data' => $existingData['style']['columns'] ?? '3',
                        ])
                        ->add('cardVariant', ChoiceType::class, [
                            'label' => 'Card Variant',
                            'required' => true,
                            'choices' => [
                                'Solid' => 'solid',
                                'Outline' => 'outline',
                                'Glass' => 'glass',
                            ],
                            'data' => $existingData['style']['cardVariant'] ?? 'solid',
                        ])
                        ->add('accentColor', ChoiceType::class, [
                            'label' => 'Accent Color',
                            'required' => true,
                            'choices' => [
                                'Primary' => 'primary',
                                'Accent' => 'accent',
                            ],
                            'data' => $existingData['style']['accentColor'] ?? 'primary',
                        ]);
                    break;

                case 'faq':
                    $builder
                        ->add('sectionTitle', TextType::class, [
                            'label' => 'Section Title',
                            'required' => false,
                            'data' => $existingData['sectionTitle'] ?? '',
                        ])
                        ->add('items', CollectionType::class, [
                            'entry_type' => FaqItemType::class,
                            'entry_options' => ['label' => false],
                            'allow_add' => true,
                            'allow_delete' => true,
                            'prototype' => true,
                            'data' => $existingData['items'] ?? [],
                            'label' => 'FAQ Items',
                        ])
                        ->add('accordionVariant', ChoiceType::class, [
                            'label' => 'Accordion Variant',
                            'required' => false,
                            'choices' => [
                                'Default' => 'default',
                                'Light' => 'light',
                                'Dark' => 'dark',
                            ],
                            'data' => $existingData['style']['accordionVariant'] ?? 'default',
                        ]);
                    break;

                case 'form':
                    $builder
                        ->add('title', TextType::class, [
                            'label' => 'Form Title',
                            'required' => true,
                            'data' => $existingData['title'] ?? '',
                        ])
                        ->add('fields', CollectionType::class, [
                            'entry_type' => FormFieldType::class,
                            'entry_options' => ['label' => false],
                            'allow_add' => true,
                            'allow_delete' => true,
                            'prototype' => true,
                            'data' => $existingData['fields'] ?? [],
                            'label' => 'Fields',
                        ])
                        ->add('submitText', TextType::class, [
                            'label' => 'Submit Button Text',
                            'required' => true,
                            'data' => $existingData['submitText'] ?? 'Submit',
                        ])
                        ->add('successMessage', TextareaType::class, [
                            'label' => 'Success Message',
                            'required' => false,
                            'data' => $existingData['successMessage'] ?? '',
                        ])
                        ->add('layout', ChoiceType::class, [
                            'label' => 'Layout',
                            'required' => true,
                            'choices' => [
                                '1 Column' => '1col',
                                '2 Columns' => '2col',
                            ],
                            'data' => $existingData['style']['layout'] ?? '1col',
                        ])
                        ->add('buttonStyle', ChoiceType::class, [
                            'label' => 'Button Style',
                            'required' => true,
                            'choices' => [
                                'Primary' => 'primary',
                                'Accent' => 'accent',
                            ],
                            'data' => $existingData['style']['buttonStyle'] ?? 'primary',
                        ]);
                    break;

                case 'cta':
                    $builder
                        ->add('title', TextType::class, [
                            'label' => 'Title',
                            'required' => true,
                            'data' => $existingData['title'] ?? '',
                        ])
                        ->add('text', TextareaType::class, [
                            'label' => 'Text',
                            'required' => false,
                            'data' => $existingData['text'] ?? '',
                        ])
                        ->add('buttonText', TextType::class, [
                            'label' => 'Button Text',
                            'required' => true,
                            'data' => $existingData['buttonText'] ?? 'Learn More',
                        ])
                        ->add('buttonUrl', TextType::class, [
                            'label' => 'Button URL',
                            'required' => true,
                            'data' => $existingData['buttonUrl'] ?? '',
                        ])
                        ->add('background', ChoiceType::class, [
                            'label' => 'Background',
                            'required' => true,
                            'choices' => [
                                'Primary' => 'primary',
                                'Gradient' => 'gradient',
                                'Surface' => 'surface',
                            ],
                            'data' => $existingData['style']['background'] ?? 'primary',
                        ])
                        ->add('align', ChoiceType::class, [
                            'label' => 'Text Align',
                            'required' => true,
                            'choices' => [
                                'Center' => 'center',
                                'Left' => 'left',
                            ],
                            'data' => $existingData['style']['align'] ?? 'center',
                        ]);
                    break;

                case 'footer':
                    $builder
                        ->add('text', TextareaType::class, [
                            'label' => 'Footer Text',
                            'required' => true,
                            'data' => $existingData['text'] ?? '',
                        ])
                        ->add('links', TextareaType::class, [
                            'label' => 'Links (Label|/url per line)',
                            'required' => false,
                            'data' => $existingData['links'] ?? '',
                        ])
                        ->add('phone', TextType::class, [
                            'label' => 'Phone',
                            'required' => false,
                            'data' => $existingData['phone'] ?? '',
                        ])
                        ->add('email', EmailType::class, [
                            'label' => 'Email',
                            'required' => false,
                            'data' => $existingData['email'] ?? '',
                        ])
                        ->add('style', ChoiceType::class, [
                            'label' => 'Style',
                            'required' => true,
                            'choices' => [
                                'Light' => 'light',
                                'Dark' => 'dark',
                            ],
                            'data' => $existingData['style']['variant'] ?? 'light',
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

class FormFieldType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('label', TextType::class, ['label' => 'Label'])
                ->add('name', TextType::class, ['label' => 'Name'])
                ->add('type', ChoiceType::class, [
                    'label' => 'Type',
                    'choices' => [
                        'Text' => 'text',
                        'Email' => 'email',
                        'Phone' => 'tel',
                        'Textarea' => 'textarea',
                        'Select' => 'select',
                        'Checkbox' => 'checkbox',
                    ],
                ])
                ->add('required', CheckboxType::class, ['label' => 'Required', 'required' => false])
                ->add('placeholder', TextType::class, ['label' => 'Placeholder', 'required' => false])
                ->add('options', TextareaType::class, [
                    'label' => 'Options (for select, one per line)',
                    'required' => false,
                    'attr' => ['rows' => 3],
                ])
                ->add('width', ChoiceType::class, [
                    'label' => 'Width',
                    'choices' => [
                        'Full' => 'full',
                        'Half' => 'half',
                    ],
                ]);
        }
    }

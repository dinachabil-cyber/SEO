<?php

namespace App\Form;

use App\Entity\PageSection;
use App\Entity\SectionTypes;
use App\Form\FormFieldsType;
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
                'choices' => array_combine(SectionTypes::ALL, SectionTypes::ALL),
                'label' => 'Section Type',
                'placeholder' => 'Choose a section type',
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $section = $event->getData();
            $form = $event->getForm();

            if ($section && $section->getType()) {
                $existingData = method_exists($section, 'getEffectiveData')
                    ? $section->getEffectiveData()
                    : $section->getData();
                if (!is_array($existingData)) {
                    $existingData = [];
                }

                $this->addDynamicFields($form, $section->getType(), $existingData);
            }
        });

        // When creating a NEW section, the type is submitted via the request but the
        // entity has no type yet, so PRE_SET_DATA never adds the dynamic fields.
        // Add them here based on the submitted type so the content (form_fields,
        // FAQ items, etc.) is not discarded.
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            if ($form->has('data')) {
                return;
            }

            $data = $event->getData();
            $type = is_array($data) ? ($data['type'] ?? null) : null;
            if (!$type) {
                return;
            }

            $existingData = (is_array($data) && isset($data['data']) && is_array($data['data']))
                ? $data['data']
                : [];
            if (!is_array($existingData)) {
                $existingData = [];
            }

            $this->addDynamicFields($form, $type, $existingData);
        });

$builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
    $data = $event->getData();
    if (!is_array($data)) {
        return;
    }

    if (isset($data['data']['hero_form_fields']) && is_string($data['data']['hero_form_fields'])) {
        $decoded = json_decode($data['data']['hero_form_fields'], true);
        $data['data']['hero_form_fields'] = is_array($decoded) ? $decoded : [];
        $event->setData($data);
    }

    if (isset($data['data']['form_fields']) && is_string($data['data']['form_fields'])) {
        $decoded = json_decode($data['data']['form_fields'], true);
        $data['data']['form_fields'] = is_array($decoded) ? $decoded : [];
        $event->setData($data);
    }
});
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PageSection::class,
            'request' => null,
            'allow_extra_fields' => true,
        ]);
    }

    public static function addDynamicFields($form, $type, $existingData = [])
    {
        if ($form->has('data')) {
            $form->remove('data');
        }

        $form->add('data', SectionDataType::class, [
            'type' => $type,
            'existing_data' => $existingData,
            'label' => false,
            'data' => $existingData,
        ]);
    }
}

class SectionDataType extends AbstractType
{
    private const BUTTON_STYLES = [
        'Primary' => 'primary',
        'Secondary' => 'secondary',
        'Outline' => 'outline',
        'Ghost' => 'ghost',
    ];

    private function ensureString($value): string
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_array($value) || is_object($value)) {
            error_log(sprintf('Converting %s to string: %s', gettype($value), print_r($value, true)));
            return '';
        }
        return (string) $value;
    }

    private function get($existingData, $key, $default = '')
    {
        return $existingData[$key] ?? $default;
    }

    private function addTextField(FormBuilderInterface $builder, string $name, string $label, array $existingData, bool $required = false, string $default = ''): void
    {
        $builder->add($name, TextType::class, [
            'label' => $label,
            'required' => $required,
            'data' => $this->ensureString($this->get($existingData, $name, $default)),
        ]);
    }

    private function addTextareaField(FormBuilderInterface $builder, string $name, string $label, array $existingData, bool $required = false, string $default = '', array $attr = []): void
    {
        $builder->add($name, TextareaType::class, [
            'label' => $label,
            'required' => $required,
            'data' => $this->ensureString($this->get($existingData, $name, $default)),
            'attr' => $attr,
        ]);
    }

    private function addColorField(FormBuilderInterface $builder, string $name, string $label, array $existingData, string $default = '', array $attr = [], string $fallbackKey = ''): void
    {
        if ($fallbackKey !== '') {
            $builder->add($name, ColorType::class, [
                'label' => $label,
                'required' => false,
                'data' => $this->ensureString($this->get($existingData, $name, $this->get($existingData, $fallbackKey, $default))),
                'attr' => $attr,
            ]);
        } else {
            $builder->add($name, ColorType::class, [
                'label' => $label,
                'required' => false,
                'data' => $this->ensureString($this->get($existingData, $name, $default)),
                'attr' => $attr,
            ]);
        }
    }

    private function addSpacingFields(FormBuilderInterface $builder, array $existingData, array $defaults = []): void
    {
        $defaults = array_merge([
            'paddingTop' => '',
            'paddingBottom' => '',
            'marginTop' => '',
            'marginBottom' => '',
        ], $defaults);

        $builder
            ->add('paddingTop', TextType::class, [
                'label' => 'Padding Top (px)',
                'required' => false,
                'data' => $this->ensureString($this->get($existingData, 'paddingTop', $this->get($existingData, 'padding_top', $defaults['paddingTop']))),
            ])
            ->add('paddingBottom', TextType::class, [
                'label' => 'Padding Bottom (px)',
                'required' => false,
                'data' => $this->ensureString($this->get($existingData, 'paddingBottom', $this->get($existingData, 'padding_bottom', $defaults['paddingBottom']))),
            ])
            ->add('marginTop', TextType::class, [
                'label' => 'Margin Top (px)',
                'required' => false,
                'data' => $this->ensureString($this->get($existingData, 'marginTop', $this->get($existingData, 'margin_top', $defaults['marginTop']))),
            ])
            ->add('marginBottom', TextType::class, [
                'label' => 'Margin Bottom (px)',
                'required' => false,
                'data' => $this->ensureString($this->get($existingData, 'marginBottom', $this->get($existingData, 'margin_bottom', $defaults['marginBottom']))),
            ]);
    }

    private function addButtonStyleChoice(FormBuilderInterface $builder, string $name, string $label, array $existingData, string $default = 'primary'): void
    {
        $builder->add($name, ChoiceType::class, [
            'label' => $label,
            'required' => false,
            'choices' => self::BUTTON_STYLES,
            'data' => $existingData[$name] ?? $default,
            'placeholder' => 'Select style',
            'help' => 'Button appearance. "primary"/"success" etc. are solid filled colors; "outline-*" are bordered; the style should match your theme.',
        ]);
    }

    private function addButtonFields(FormBuilderInterface $builder, string $prefix, array $existingData, string $defaultStyle = 'primary'): void
    {
        $bgKey = $prefix !== '' ? $prefix . '_button_background_color' : 'buttonBackgroundColor';
        $textKey = $prefix !== '' ? $prefix . '_button_text_color' : 'buttonTextColor';
        $borderKey = $prefix !== '' ? $prefix . '_button_border_color' : 'buttonBorderColor';
        $radiusKey = $prefix !== '' ? $prefix . '_button_border_radius' : 'buttonBorderRadius';
        $styleKey = $prefix !== '' ? $prefix . '_button_style' : 'buttonStyle';

        $this->addColorField($builder, $bgKey, ucfirst($prefix) . ' Button Background Color', $existingData);
        $this->addColorField($builder, $textKey, ucfirst($prefix) . ' Button Text Color', $existingData);
        $this->addColorField($builder, $borderKey, ucfirst($prefix) . ' Button Border Color', $existingData);
        $builder->add($radiusKey, TextType::class, [
            'label' => ucfirst($prefix) . ' Button Border Radius (px)',
            'required' => false,
            'data' => $this->ensureString($this->get($existingData, $radiusKey)),
        ]);
        $this->addButtonStyleChoice($builder, $styleKey, ucfirst($prefix) . ' Button Style', $existingData, $defaultStyle);
    }

    private function addTextAlignmentChoice(FormBuilderInterface $builder, string $name, string $label, array $existingData, string $default = 'center'): void
    {
        $builder->add($name, ChoiceType::class, [
            'label' => $label,
            'required' => false,
            'choices' => [
                'Left' => 'left',
                'Center' => 'center',
                'Right' => 'right',
            ],
            'data' => $existingData[$name] ?? $default,
            'placeholder' => 'Select alignment',
            'help' => 'Align the text inside this section: Left, Center, or Right.',
        ]);
    }

    private function addHiddenLegacyField(FormBuilderInterface $builder, string $name, array $existingData): void
    {
        $builder->add($name, HiddenType::class, [
            'required' => false,
            'mapped' => false,
            'data' => $this->ensureString($this->get($existingData, $name)),
        ]);
    }

    private function addSectionTitleField(FormBuilderInterface $builder, array $existingData, string $default = ''): void
    {
        $builder->add('sectionTitle', TextType::class, [
            'label' => 'Section Title',
            'required' => false,
            'data' => $this->ensureString($this->get($existingData, 'sectionTitle', $default)),
        ]);
    }

    private function addBackgroundColorField(FormBuilderInterface $builder, string $name, string $label, array $existingData, string $default = ''): void
    {
        $builder->add($name, ColorType::class, [
            'label' => $label,
            'required' => false,
            'data' => $this->ensureString($this->get($existingData, $name, $default)),
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $type = $options['type'];
        $existingData = $options['data'] ?? $options['existing_data'] ?? [];

        foreach ($existingData as $key => $value) {
            if (is_array($value) && !in_array($key, ['cards', 'items', 'hero_fields', 'form_fields', 'hero_form_fields', 'blocks'], true)) {
                error_log(sprintf('WARNING: Field "%s" in section type "%s" has array value: %s', $key, $type, print_r($value, true)));
            }
        }

        switch ($type) {
            case 'header':
                $builder
                    ->add('brandText', TextType::class, [
                        'label' => 'Brand Text',
                        'required' => true,
                        'data' => $this->ensureString($this->get($existingData, 'brandText')),
                    ])
                    ->add('logoUrl', TextType::class, [
                        'label' => 'Logo URL',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'logoUrl')),
                    ])
                    ->add('menuItems', TextareaType::class, [
                        'label' => 'Menu Items (Label|/url per line)',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'menuItems')),
                    ])
                    ->add('ctaText', TextType::class, [
                        'label' => 'CTA Text',
                        'required' => true,
                        'data' => $this->ensureString($this->get($existingData, 'ctaText')),
                    ])
                    ->add('ctaUrl', TextType::class, [
                        'label' => 'CTA URL',
                        'required' => true,
                        'data' => $this->ensureString($this->get($existingData, 'ctaUrl')),
                    ])
                    ->add('backgroundColor', ColorType::class, [
                        'label' => 'Header Background Color',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'backgroundColor', $this->get($existingData, 'background'))),
                    ])
                    ->add('textColor', ColorType::class, [
                        'label' => 'Header Text Color',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'textColor')),
                    ]);

                $this->addButtonFields($builder, '', $existingData, 'primary');
                $this->addSpacingFields($builder, $existingData);
                break;

            case 'hero':
                $builder
                    ->add('hero_title', TextType::class, [
                        'label' => 'Hero Title',
                        'required' => true,
                        'data' => $this->ensureString($this->get($existingData, 'hero_title', $this->get($existingData, 'title'))),
                    ])
                    ->add('hero_subtitle', TextareaType::class, [
                        'label' => 'Hero Subtitle',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'hero_subtitle', $this->get($existingData, 'subtitle'))),
                        'attr' => ['rows' => 2],
                    ])
                    ->add('badge_text', TextType::class, [
                        'label' => 'Badge Text',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'badge_text')),
                    ])
                    ->add('description', TextareaType::class, [
                        'label' => 'Description',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'description')),
                        'attr' => ['rows' => 3],
                    ])
                    ->add('top_text', TextType::class, [
                        'label' => 'Top Text / Badge',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'top_text')),
                    ])
                    ->add('phone_number', TextType::class, [
                        'label' => 'Phone Number',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'phone_number')),
                    ])
                    ->add('primary_button_text', TextType::class, [
                        'label' => 'Primary Button Text',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'primary_button_text', $this->get($existingData, 'ctaText'))),
                    ])
                    ->add('primary_button_url', TextType::class, [
                        'label' => 'Primary Button URL',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'primary_button_url', $this->get($existingData, 'ctaUrl'))),
                    ])
                    ->add('secondary_button_text', TextType::class, [
                        'label' => 'Secondary Button Text',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'secondary_button_text')),
                    ])
                    ->add('secondary_button_url', TextType::class, [
                        'label' => 'Secondary Button URL',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'secondary_button_url')),
                    ])
                    ->add('form_title', TextType::class, [
                        'label' => 'Form Title',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'form_title')),
                    ])
                    ->add('form_subtitle', TextareaType::class, [
                        'label' => 'Form Subtitle',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'form_subtitle')),
                        'attr' => ['rows' => 2],
                    ]);

                $this->addHiddenLegacyField($builder, 'title', $existingData);
                $this->addHiddenLegacyField($builder, 'subtitle', $existingData);
                $this->addHiddenLegacyField($builder, 'ctaText', $existingData);
                $this->addHiddenLegacyField($builder, 'ctaUrl', $existingData);
                $this->addHiddenLegacyField($builder, 'imageUrl', $existingData);

                $hero_fields_data = $existingData['hero_form_fields'] ?? $existingData['hero_fields'] ?? $existingData['hero_form_fields_json'] ?? [];
                $builder->add('hero_form_fields', HiddenType::class, [
                    'required' => false,
                    'data' => is_array($hero_fields_data) ? json_encode($hero_fields_data) : (string) $hero_fields_data,
                ]);

                $builder->add('show_form', CheckboxType::class, [
                    'label' => 'Show Contact Form',
                    'required' => false,
                    'data' => filter_var($this->get($existingData, 'show_form', false), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
                ]);

                $builder
                    ->add('hero_image_url', TextType::class, [
                        'label' => 'Hero Image URL',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'hero_image_url', $this->get($existingData, 'imageUrl'))),
                    ])
                    ->add('mobile_image_url', TextType::class, [
                        'label' => 'Mobile Image URL',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'mobile_image_url')),
                    ])
                    ->add('image_alt_text', TextType::class, [
                        'label' => 'Image Alt Text',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'image_alt_text')),
                    ])
                    ->add('show_image', CheckboxType::class, [
                        'label' => 'Show Image',
                        'required' => false,
                        'data' => filter_var($this->get($existingData, 'show_image', true), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
                    ])
                    ->add('left_image', TextType::class, [
                        'label' => 'Left Image URL',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'left_image')),
                    ])
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
                        'data' => $this->get($existingData, 'layout_type', 'centered'),
                        'placeholder' => 'Select layout',
                        'help' => 'How the hero content and media are arranged. "Form Only" shows just a lead form with no image.',
                    ])
                    ->add('text_alignment', ChoiceType::class, [
                        'label' => 'Text Alignment',
                        'required' => false,
                        'choices' => [
                            'Left' => 'left',
                            'Center' => 'center',
                            'Right' => 'right',
                        ],
                        'data' => $this->get($existingData, 'text_alignment', 'center'),
                        'placeholder' => 'Select alignment',
                        'help' => 'Align the hero text (Left, Center, or Right) inside its column.',
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
                        'data' => $this->get($existingData, 'content_width', 'medium'),
                        'placeholder' => 'Select width',
                        'help' => 'Maximum width of the hero text column. "Full Width" spans the whole container.',
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
                        'data' => $this->get($existingData, 'section_height', 'medium'),
                        'placeholder' => 'Select height',
                        'help' => 'Vertical space of the hero. "Auto" sizes to content; larger values add more breathing room.',
                    ])
                    ->add('vertical_alignment', ChoiceType::class, [
                        'label' => 'Vertical Alignment',
                        'required' => false,
                        'choices' => [
                            'Top' => 'top',
                            'Center' => 'center',
                            'Bottom' => 'bottom',
                        ],
                        'data' => $this->get($existingData, 'vertical_alignment', 'center'),
                        'placeholder' => 'Select alignment',
                        'help' => 'Vertically position the content (Top, Center, Bottom) when the section is taller than its content.',
                    ])
                    ->add('column_gap', TextType::class, [
                        'label' => 'Column Gap (px)',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'column_gap', '30')),
                    ])
                    ->add('background_color', ColorType::class, [
                        'label' => 'Background Color',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'background_color', $this->get($existingData, 'backgroundColor'))),
                    ])
                    ->add('background_gradient', TextType::class, [
                        'label' => 'Background Gradient (CSS)',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'background_gradient')),
                        'attr' => ['placeholder' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
                    ])
                    ->add('hero_text_color', ColorType::class, [
                        'label' => 'Hero Text Color',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'hero_text_color', $this->get($existingData, 'textColor'))),
                    ])
                    ->add('title_color', ColorType::class, [
                        'label' => 'Title Color',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'title_color', $this->get($existingData, 'titleColor'))),
                    ])
                    ->add('subtitle_color', ColorType::class, [
                        'label' => 'Subtitle Color',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'subtitle_color', $this->get($existingData, 'subtitleColor'))),
                    ])
                    ->add('description_color', ColorType::class, [
                        'label' => 'Description Color',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'description_color')),
                    ])
                    ->add('card_background_color', ColorType::class, [
                        'label' => 'Card Background Color',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'card_background_color')),
                    ])
                    ->add('border_radius', TextType::class, [
                        'label' => 'Border Radius (px)',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'border_radius')),
                    ])
                    ->add('box_shadow', CheckboxType::class, [
                        'label' => 'Show Box Shadow',
                        'required' => false,
                        'data' => $this->get($existingData, 'box_shadow', false),
                    ]);

                $this->addSpacingFields($builder, $existingData, [
                    'paddingTop' => '60',
                    'paddingBottom' => '60',
                ]);

                $this->addButtonFields($builder, 'primary', $existingData, 'primary');
                $this->addButtonFields($builder, 'secondary', $existingData, 'outline');
                break;

            case 'body':
                $builder
                    ->add('content', TextareaType::class, [
                        'label' => 'Content',
                        'required' => true,
                        'data' => $this->ensureString($this->get($existingData, 'content')),
                        'attr' => ['rows' => 10],
                    ]);
                break;

            case 'image':
                $builder
                    ->add('imageUrl', TextType::class, [
                        'label' => 'Image URL',
                        'required' => true,
                        'data' => $this->ensureString($this->get($existingData, 'imageUrl')),
                    ])
                    ->add('alt', TextType::class, [
                        'label' => 'Alt Text',
                        'required' => true,
                        'data' => $this->ensureString($this->get($existingData, 'alt')),
                    ])
                    ->add('caption', TextareaType::class, [
                        'label' => 'Caption',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'caption')),
                    ]);
                break;

            case 'cards':
            case 'cards_premium':
                $builder
                    ->add('sectionTitle', TextType::class, [
                        'label' => 'Section Title',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'sectionTitle')),
                    ])
                    ->add('cards', CollectionType::class, [
                        'entry_type' => CardType::class,
                        'entry_options' => ['label' => false],
                        'allow_add' => true,
                        'allow_delete' => true,
                        'prototype' => true,
                        'data' => $this->get($existingData, 'cards', []),
                        'label' => 'Cards',
                    ])
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
                        'data' => $this->get($existingData, 'cardLayout', 'grid-3'),
                        'placeholder' => 'Select layout',
                        'help' => 'How cards are arranged: the number of columns (grid-2/3/4), stacked (vertical), side-by-side (horizontal), or centered single column.',
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
                        'data' => $this->get($existingData, 'cardStyle', 'standard'),
                        'placeholder' => 'Select style',
                        'help' => 'Card visual style: shape (rounded / square / oval) and treatment (bordered outline or shadowed lifted card).',
                    ]);

                $builder
                    ->add('backgroundColor', ColorType::class, [
                        'label' => 'Section Background Color',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'backgroundColor')),
                    ])
                    ->add('textColor', ColorType::class, [
                        'label' => 'Section Text Color',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'textColor')),
                    ])
                    ->add('titleColor', ColorType::class, [
                        'label' => 'Section Title Color',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'titleColor')),
                    ])
                    ->add('subtitleColor', ColorType::class, [
                        'label' => 'Section Subtitle Color',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'subtitleColor')),
                    ])
                    ->add('cardBackgroundColor', ColorType::class, [
                        'label' => 'Card Background Color',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'cardBackgroundColor')),
                    ])
                    ->add('cardTitleColor', ColorType::class, [
                        'label' => 'Card Title Color',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'cardTitleColor')),
                    ])
                    ->add('cardTextColor', ColorType::class, [
                        'label' => 'Card Text Color',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'cardTextColor')),
                    ])
                    ->add('cardBorderColor', ColorType::class, [
                        'label' => 'Card Border Color',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'cardBorderColor')),
                    ])
                    ->add('cardShadow', CheckboxType::class, [
                        'label' => 'Show Card Shadow',
                        'required' => false,
                        'data' => $this->get($existingData, 'cardShadow', false),
                    ])
                    ->add('cardBorderRadius', TextType::class, [
                        'label' => 'Card Border Radius (px)',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'cardBorderRadius')),
                    ]);

                $this->addButtonFields($builder, '', $existingData, 'primary');
                $this->addTextAlignmentChoice($builder, 'textAlignment', 'Text Alignment', $existingData, 'center');
                $this->addSpacingFields($builder, $existingData);
                break;

            case 'faq':
                $builder
                    ->add('sectionTitle', TextType::class, [
                        'label' => 'Section Title',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'sectionTitle')),
                    ])
                    ->add('sectionSubtitle', TextareaType::class, [
                        'label' => 'Section Subtitle',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'sectionSubtitle')),
                    ])
                    ->add('items', CollectionType::class, [
                        'entry_type' => FaqItemType::class,
                        'entry_options' => ['label' => false],
                        'allow_add' => true,
                        'allow_delete' => true,
                        'prototype' => true,
                        'data' => $this->get($existingData, 'items', []),
                        'label' => 'FAQ Items',
                    ]);

                $this->addColorField($builder, 'backgroundColor', 'Background Color', $existingData);
                $this->addColorField($builder, 'textColor', 'Text Color', $existingData);
                $this->addColorField($builder, 'titleColor', 'Title Color', $existingData);
                $this->addColorField($builder, 'itemBackgroundColor', 'Item Background Color', $existingData);
                $this->addColorField($builder, 'itemBorderColor', 'Item Border Color', $existingData);
                $this->addColorField($builder, 'activeColor', 'Active/Open Item Color', $existingData);
                $this->addTextAlignmentChoice($builder, 'textAlignment', 'Text Alignment', $existingData, 'center');
                $this->addSpacingFields($builder, $existingData);
                break;

            case 'form':
                $builder
                    ->add('section_title', TextType::class, [
                        'label' => 'Section Title',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'section_title')),
                    ])
                    ->add('section_subtitle', TextareaType::class, [
                        'label' => 'Section Subtitle',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'section_subtitle')),
                    ])
                    ->add('form_title', TextType::class, [
                        'label' => 'Form Title',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'form_title', $this->get($existingData, 'title'))),
                    ])
                    ->add('form_description', TextareaType::class, [
                        'label' => 'Form Description',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'form_description')),
                    ])
                    ->add('submit_button_text', TextType::class, [
                        'label' => 'Submit Button Text',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'submit_button_text', $this->get($existingData, 'submitText', 'Submit'))),
                    ])
                    ->add('success_message', TextareaType::class, [
                        'label' => 'Success Message',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'success_message', $this->get($existingData, 'successMessage', "Thank you! Your message has been sent."))),
                    ]);

                $this->addHiddenLegacyField($builder, 'title', $existingData);
                $this->addHiddenLegacyField($builder, 'submitText', $existingData);
                $this->addHiddenLegacyField($builder, 'successMessage', $existingData);

                $form_fields_data = $existingData['form_fields'] ?? $existingData['fields'] ?? [];
                $builder->add('form_fields', HiddenType::class, [
                    'required' => false,
                    'data' => is_array($form_fields_data) ? json_encode($form_fields_data) : (string) $form_fields_data,
                ]);

                $builder
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
                        'data' => $this->get($existingData, 'form_type', 'contact'),
                        'placeholder' => 'Select form type',
                        'help' => 'The form\'s purpose, used for labelling/analytics. It does not change the fields shown — use "Field Visibility" for that.',
                    ])
                    ->add('form_key', TextType::class, [
                        'label' => 'Form Key / ID',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'form_key', $this->get($existingData, 'form_id'))),
                        'attr' => ['placeholder' => 'e.g., contact-form-001'],
                    ])
                    ->add('show_name_field', CheckboxType::class, [
                        'label' => 'Show Name Field',
                        'required' => false,
                        'data' => $this->get($existingData, 'show_name_field', true),
                    ])
                    ->add('show_email_field', CheckboxType::class, [
                        'label' => 'Show Email Field',
                        'required' => false,
                        'data' => $this->get($existingData, 'show_email_field', true),
                    ])
                    ->add('show_phone_field', CheckboxType::class, [
                        'label' => 'Show Phone Field',
                        'required' => false,
                        'data' => $this->get($existingData, 'show_phone_field', false),
                    ])
                    ->add('show_message_field', CheckboxType::class, [
                        'label' => 'Show Message Field',
                        'required' => false,
                        'data' => $this->get($existingData, 'show_message_field', false),
                    ])
                    ->add('show_company_field', CheckboxType::class, [
                        'label' => 'Show Company Field',
                        'required' => false,
                        'data' => $this->get($existingData, 'show_company_field', false),
                    ])
                    ->add('show_checkbox_consent', CheckboxType::class, [
                        'label' => 'Show Consent Checkbox',
                        'required' => false,
                        'data' => $this->get($existingData, 'show_checkbox_consent', false),
                    ])
                    ->add('redirect_url_after_submit', TextType::class, [
                        'label' => 'Redirect URL After Submit',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'redirect_url_after_submit')),
                        'attr' => ['placeholder' => '/thank-you'],
                    ])
                    ->add('store_submissions', CheckboxType::class, [
                        'label' => 'Store Submissions',
                        'required' => false,
                        'data' => $this->get($existingData, 'store_submissions', true),
                    ])
                    ->add('form_layout', ChoiceType::class, [
                        'label' => 'Form Layout',
                        'required' => false,
                        'choices' => [
                            'Centered' => 'centered',
                            'Full Width' => 'full_width',
                            'Split with Image' => 'split_with_image',
                            'Split with Text' => 'split_with_text',
                        ],
                        'data' => $this->get($existingData, 'form_layout', 'centered'),
                        'placeholder' => 'Select layout',
                        'help' => 'Overall arrangement. "Split" places the form beside an image or text column (add the image in the Media tab).',
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
                        'data' => $this->get($existingData, 'form_width', 'medium'),
                        'placeholder' => 'Select width',
                        'help' => 'Maximum width of the form card within the section.',
                    ])
                    ->add('form_alignment', ChoiceType::class, [
                        'label' => 'Form Alignment',
                        'required' => false,
                        'choices' => [
                            'Left' => 'left',
                            'Center' => 'center',
                            'Right' => 'right',
                        ],
                        'data' => $this->get($existingData, 'form_alignment', 'center'),
                        'placeholder' => 'Select alignment',
                        'help' => 'Horizontal alignment of the form card within the section.',
                    ])
                    ->add('show_icon_above_title', CheckboxType::class, [
                        'label' => 'Show Icon Above Title',
                        'required' => false,
                        'data' => $this->get($existingData, 'show_icon_above_title', false),
                    ])
                    ->add('side_image_url', TextType::class, [
                        'label' => 'Side Image URL',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'side_image_url')),
                    ])
                    ->add('image_alt_text', TextType::class, [
                        'label' => 'Image Alt Text',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'image_alt_text')),
                    ])
                    ->add('show_image', CheckboxType::class, [
                        'label' => 'Show Image',
                        'required' => false,
                        'data' => in_array($this->get($existingData, 'show_image', true), [true, 'true', '1', 'yes', 'on'], true),
                    ]);

                $this->addColorField($builder, 'section_background_color', 'Section Background Color', $existingData, '', [], 'backgroundColor');
                $this->addColorField($builder, 'form_card_background_color', 'Form Card Background Color', $existingData);
                $this->addColorField($builder, 'title_color', 'Title Color', $existingData, '', [], 'titleColor');
                $this->addColorField($builder, 'subtitle_color', 'Subtitle Color', $existingData, '', [], 'subtitleColor');
                $this->addColorField($builder, 'label_color', 'Label Color', $existingData);
                $this->addColorField($builder, 'input_background_color', 'Input Background Color', $existingData);
                $this->addColorField($builder, 'input_text_color', 'Input Text Color', $existingData);
                $this->addColorField($builder, 'input_border_color', 'Input Border Color', $existingData);

                $builder->add('input_border_radius', TextType::class, [
                    'label' => 'Input Border Radius (px)',
                    'required' => false,
                    'data' => $this->ensureString($this->get($existingData, 'input_border_radius')),
                ]);

                $this->addColorField($builder, 'button_background_color', 'Button Background Color', $existingData, '', [], 'buttonBackgroundColor');
                $this->addColorField($builder, 'button_text_color', 'Button Text Color', $existingData, '', [], 'buttonTextColor');
                $this->addColorField($builder, 'button_border_color', 'Button Border Color', $existingData, '', [], 'buttonBorderColor');

                $builder->add('button_border_radius', TextType::class, [
                    'label' => 'Button Border Radius (px)',
                    'required' => false,
                    'data' => $this->ensureString($this->get($existingData, 'button_border_radius', $this->get($existingData, 'buttonBorderRadius'))),
                ]);

                $this->addSpacingFields($builder, $existingData, [
                    'paddingTop' => '60',
                    'paddingBottom' => '60',
                ]);

                $builder->add('box_shadow', CheckboxType::class, [
                    'label' => 'Show Box Shadow',
                    'required' => false,
                    'data' => $this->get($existingData, 'box_shadow', false),
                ]);

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
                        'data' => $this->get($existingData, 'container_width', '1140px'),
                        'help' => 'Maximum width of the content container. Wider shows more columns; narrower keeps content centered and focused.',
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
                        'data' => $this->get($existingData, 'layout_type', 'classic'),
                        'help' => 'Footer column arrangement: Classic (3), Modern (4), Simple (2), or Minimal (1) column.',
                    ])
                    ->add('stack_on_mobile', ChoiceType::class, [
                        'label' => 'Stack on Mobile',
                        'required' => false,
                        'choices' => [
                            'Yes - Stack vertically' => true,
                            'No - Keep inline' => false,
                        ],
                        'data' => isset($existingData['stack_on_mobile']) ? (bool) $existingData['stack_on_mobile'] : true,
                        'help' => 'On small screens, stack the columns vertically (Yes) instead of keeping them side-by-side.',
                    ])
                    ->add('show_divider', ChoiceType::class, [
                        'label' => 'Show Divider',
                        'required' => false,
                        'choices' => [
                            'Yes' => true,
                            'No' => false,
                        ],
                        'data' => isset($existingData['show_divider']) ? (bool) $existingData['show_divider'] : true,
                        'help' => 'Show a thin top border line above the footer to separate it from the section above.',
                    ]);
                break;

            case 'cta':
                $builder
                    ->add('title', TextType::class, [
                        'label' => 'Title',
                        'required' => true,
                        'data' => $this->ensureString($this->get($existingData, 'title')),
                    ])
                    ->add('text', TextareaType::class, [
                        'label' => 'Text',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'text')),
                    ])
                    ->add('buttonText', TextType::class, [
                        'label' => 'Button Text',
                        'required' => true,
                        'data' => $this->ensureString($this->get($existingData, 'buttonText', 'Learn More')),
                    ])
                    ->add('buttonUrl', TextType::class, [
                        'label' => 'Button URL',
                        'required' => true,
                        'data' => $this->ensureString($this->get($existingData, 'buttonUrl')),
                    ])
                    ->add('backgroundColor', ColorType::class, [
                        'label' => 'CTA Background Color',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'backgroundColor')),
                    ])
                    ->add('textColor', ColorType::class, [
                        'label' => 'CTA Text Color',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'textColor')),
                    ])
                    ->add('titleColor', ColorType::class, [
                        'label' => 'CTA Title Color',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'titleColor')),
                    ]);

                $this->addButtonFields($builder, '', $existingData, 'primary');
                $this->addTextAlignmentChoice($builder, 'textAlignment', 'Text Alignment', $existingData, 'center');
                $this->addSpacingFields($builder, $existingData);
                break;

            case 'footer':
                $builder
                    // Content Tab Fields
                    ->add('company_name', TextType::class, [
                        'label' => 'Company Name',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'company_name')),
                    ])
                    ->add('company_description', TextareaType::class, [
                        'label' => 'Company Description',
                        'required' => false,
                        'attr' => ['rows' => 3],
                        'data' => $this->ensureString($this->get($existingData, 'company_description')),
                    ])
                    ->add('useful_links', TextareaType::class, [
                        'label' => 'Useful Links (Label|/url per line)',
                        'required' => false,
                        'attr' => ['rows' => 4, 'placeholder' => "Home|/\nAbout|/about\nContact|/contact"],
                        'data' => $this->ensureString($this->get($existingData, 'useful_links')),
                    ])
                    ->add('address', TextareaType::class, [
                        'label' => 'Address',
                        'required' => false,
                        'attr' => ['rows' => 2],
                        'data' => $this->ensureString($this->get($existingData, 'address')),
                    ])
                    ->add('phone', TextType::class, [
                        'label' => 'Phone',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'phone')),
                    ])
                    ->add('email', EmailType::class, [
                        'label' => 'Email',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'email')),
                    ])
                    ->add('copyright_text', TextType::class, [
                        'label' => 'Copyright Text',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'copyright_text')),
                    ])
                    ->add('ctaText', TextType::class, [
                        'label' => 'CTA Text',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'ctaText')),
                    ])
                    ->add('ctaUrl', TextType::class, [
                        'label' => 'CTA URL',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'ctaUrl')),
                    ]);

                $builder
                    ->add('background_color', ColorType::class, [
                        'label' => 'Background Color',
                        'required' => false,
                        'attr' => ['class' => 'color-picker-input'],
                        'data' => $this->ensureString($this->get($existingData, 'background_color', '#1a1a2e')),
                    ])
                    ->add('text_color', ColorType::class, [
                        'label' => 'Text Color',
                        'required' => false,
                        'attr' => ['class' => 'color-picker-input'],
                        'data' => $this->ensureString($this->get($existingData, 'text_color', '#ffffff')),
                    ])
                    ->add('heading_color', ColorType::class, [
                        'label' => 'Heading Color',
                        'required' => false,
                        'attr' => ['class' => 'color-picker-input'],
                        'data' => $this->ensureString($this->get($existingData, 'heading_color', '#ffffff')),
                    ])
                    ->add('link_color', ColorType::class, [
                        'label' => 'Link Color',
                        'required' => false,
                        'attr' => ['class' => 'color-picker-input'],
                        'data' => $this->ensureString($this->get($existingData, 'link_color', '#b8b8b8')),
                    ])
                    ->add('link_hover_color', ColorType::class, [
                        'label' => 'Link Hover Color',
                        'required' => false,
                        'attr' => ['class' => 'color-picker-input'],
                        'data' => $this->ensureString($this->get($existingData, 'link_hover_color', '#4a90e2')),
                    ])
                    ->add('border_top_color', ColorType::class, [
                        'label' => 'Border Top Color',
                        'required' => false,
                        'attr' => ['class' => 'color-picker-input'],
                        'data' => $this->ensureString($this->get($existingData, 'border_top_color', '#333333')),
                    ])
                    ->add('padding_top', TextType::class, [
                        'label' => 'Padding Top (px)',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'padding_top', '60')),
                    ])
                    ->add('padding_bottom', TextType::class, [
                        'label' => 'Padding Bottom (px)',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'padding_bottom', '40')),
                    ])
                    ->add('margin_top', TextType::class, [
                        'label' => 'Margin Top (px)',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'margin_top', '0')),
                    ])
                    ->add('margin_bottom', TextType::class, [
                        'label' => 'Margin Bottom (px)',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'margin_bottom', '0')),
                    ])
                    ->add('title_font_size', TextType::class, [
                        'label' => 'Title Font Size (px)',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'title_font_size', '18')),
                    ])
                    ->add('text_font_size', TextType::class, [
                        'label' => 'Text Font Size (px)',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'text_font_size', '14')),
                    ])
                    ->add('link_font_size', TextType::class, [
                        'label' => 'Link Font Size (px)',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'link_font_size', '14')),
                    ])
                    ->add('text_alignment', ChoiceType::class, [
                        'label' => 'Text Alignment',
                        'required' => false,
                        'choices' => [
                            'Left' => 'left',
                            'Center' => 'center',
                            'Right' => 'right',
                        ],
                        'data' => $this->get($existingData, 'text_alignment', 'left'),
                        'help' => 'Align the footer text (Left, Center, or Right) within its columns.',
                    ])
                    ->add('column_gap', TextType::class, [
                        'label' => 'Column Gap (px)',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'column_gap', '30')),
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
                        'help' => 'Number of columns in the footer grid (applies when "Layout Type" is not Minimal).',
                    ])
                    ->add('border_radius', TextType::class, [
                        'label' => 'Border Radius (px)',
                        'required' => false,
                        'data' => $this->ensureString($this->get($existingData, 'border_radius', '8')),
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
                        'data' => $this->get($existingData, 'box_shadow', 'none'),
                        'help' => 'Drop shadow applied to the footer block. Choose "None" for a flat look.',
                    ]);

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
                        'data' => $this->get($existingData, 'container_width', '1140px'),
                        'help' => 'Maximum width of the footer content container.',
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
                        'data' => $this->get($existingData, 'layout_type', 'classic'),
                        'help' => 'Footer column arrangement: Classic (3), Modern (4), Simple (2), or Minimal (1) column.',
                    ])
                    ->add('stack_on_mobile', ChoiceType::class, [
                        'label' => 'Stack on Mobile',
                        'required' => false,
                        'choices' => [
                            'Yes - Stack vertically' => true,
                            'No - Keep inline' => false,
                        ],
                        'data' => isset($existingData['stack_on_mobile']) ? (bool) $existingData['stack_on_mobile'] : true,
                        'help' => 'On small screens, stack the columns vertically (Yes) instead of keeping them side-by-side.',
                    ])
                    ->add('show_divider', ChoiceType::class, [
                        'label' => 'Show Divider',
                        'required' => false,
                        'choices' => [
                            'Yes' => true,
                            'No' => false,
                        ],
                        'data' => isset($existingData['show_divider']) ? (bool) $existingData['show_divider'] : true,
                        'help' => 'Show a thin top border line above the footer to separate it from the section above.',
                    ]);
                break;
        }

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($type) {
            $data = $event->getData();
            if (!is_array($data)) {
                return;
            }

            $data = self::stripPrototypeKeys($data);

            if ($type === 'form' && isset($data['form_fields']) && is_string($data['form_fields'])) {
                $decoded = json_decode($data['form_fields'], true);
                $data['form_fields'] = is_array($decoded) ? $decoded : [];
                $event->setData($data);
            }

            if ($type === 'hero' && isset($data['hero_form_fields']) && is_string($data['hero_form_fields'])) {
                $decoded = json_decode($data['hero_form_fields'], true);
                $data['hero_form_fields'] = is_array($decoded) ? $decoded : [];
                $event->setData($data);
            }
        });
    }

    private static function stripPrototypeKeys(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            if ($key === '__name__') {
                continue;
            }
            $clean[$key] = is_array($value) ? self::stripPrototypeKeys($value) : $value;
        }

        return $clean;
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

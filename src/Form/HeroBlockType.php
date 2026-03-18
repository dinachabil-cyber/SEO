<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HeroBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $blockType = $options['block_type'] ?? 'title';

        $builder
            ->add('type', HiddenBlockType::class, [
                'data' => $blockType,
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'Enable this block',
                'required' => false,
                'data' => $options['data']['enabled'] ?? true,
            ]);

        // Title block configuration
        if ($blockType === 'title') {
            $builder
                ->add('text', TextType::class, [
                    'label' => 'Title Text',
                    'required' => true,
                    'data' => $options['data']['text'] ?? '',
                ])
                ->add('tag', ChoiceType::class, [
                    'label' => 'HTML Tag',
                    'required' => false,
                    'choices' => [
                        'H1' => 'h1',
                        'H2' => 'h2',
                        'H3' => 'h3',
                        'H4' => 'h4',
                    ],
                    'data' => $options['data']['tag'] ?? 'h1',
                ])
                ->add('alignment', ChoiceType::class, [
                    'label' => 'Alignment',
                    'required' => false,
                    'choices' => [
                        'Left' => 'left',
                        'Center' => 'center',
                        'Right' => 'right',
                    ],
                    'data' => $options['data']['alignment'] ?? 'center',
                ])
                ->add('color', ColorType::class, [
                    'label' => 'Text Color',
                    'required' => false,
                    'data' => $options['data']['color'] ?? '',
                ])
                ->add('size', ChoiceType::class, [
                    'label' => 'Font Size',
                    'required' => false,
                    'choices' => [
                        'Small' => 'small',
                        'Medium' => 'medium',
                        'Large' => 'large',
                        'X-Large' => 'xlarge',
                        'Display' => 'display',
                    ],
                    'data' => $options['data']['size'] ?? 'large',
                ]);
        }

        // Subtitle block configuration
        if ($blockType === 'subtitle') {
            $builder
                ->add('content', TextareaType::class, [
                    'label' => 'Subtitle Content',
                    'required' => false,
                    'data' => $options['data']['content'] ?? '',
                    'attr' => ['rows' => 2],
                ])
                ->add('alignment', ChoiceType::class, [
                    'label' => 'Alignment',
                    'required' => false,
                    'choices' => [
                        'Left' => 'left',
                        'Center' => 'center',
                        'Right' => 'right',
                    ],
                    'data' => $options['data']['alignment'] ?? 'center',
                ])
                ->add('color', ColorType::class, [
                    'label' => 'Text Color',
                    'required' => false,
                    'data' => $options['data']['color'] ?? '',
                ]);
        }

        // Text block configuration
        if ($blockType === 'text') {
            $builder
                ->add('content', TextareaType::class, [
                    'label' => 'Text Content',
                    'required' => false,
                    'data' => $options['data']['content'] ?? '',
                    'attr' => ['rows' => 4],
                ])
                ->add('alignment', ChoiceType::class, [
                    'label' => 'Alignment',
                    'required' => false,
                    'choices' => [
                        'Left' => 'left',
                        'Center' => 'center',
                        'Right' => 'right',
                    ],
                    'data' => $options['data']['alignment'] ?? 'center',
                ])
                ->add('color', ColorType::class, [
                    'label' => 'Text Color',
                    'required' => false,
                    'data' => $options['data']['color'] ?? '',
                ]);
        }

        // Badge block configuration
        if ($blockType === 'badge') {
            $builder
                ->add('text', TextType::class, [
                    'label' => 'Badge Text',
                    'required' => true,
                    'data' => $options['data']['text'] ?? '',
                ])
                ->add('background_color', ColorType::class, [
                    'label' => 'Background Color',
                    'required' => false,
                    'data' => $options['data']['background_color'] ?? '#007bff',
                ])
                ->add('text_color', ColorType::class, [
                    'label' => 'Text Color',
                    'required' => false,
                    'data' => $options['data']['text_color'] ?? '#ffffff',
                ]);
        }

        // Image block configuration
        if ($blockType === 'image') {
            $builder
                ->add('image_url', TextType::class, [
                    'label' => 'Image URL',
                    'required' => true,
                    'data' => $options['data']['image_url'] ?? '',
                ])
                ->add('alt_text', TextType::class, [
                    'label' => 'Alt Text',
                    'required' => false,
                    'data' => $options['data']['alt_text'] ?? '',
                ])
                ->add('position', ChoiceType::class, [
                    'label' => 'Position',
                    'required' => false,
                    'choices' => [
                        'Left' => 'left',
                        'Right' => 'right',
                        'Center' => 'center',
                    ],
                    'data' => $options['data']['position'] ?? 'right',
                ])
                ->add('border_radius', TextType::class, [
                    'label' => 'Border Radius (px)',
                    'required' => false,
                    'data' => $options['data']['border_radius'] ?? '0',
                ])
                ->add('shadow', CheckboxType::class, [
                    'label' => 'Show Shadow',
                    'required' => false,
                    'data' => $options['data']['shadow'] ?? false,
                ]);
        }

        // Button block configuration
        if ($blockType === 'button') {
            $builder
                ->add('text', TextType::class, [
                    'label' => 'Button Text',
                    'required' => true,
                    'data' => $options['data']['text'] ?? '',
                ])
                ->add('url', TextType::class, [
                    'label' => 'Button URL',
                    'required' => false,
                    'data' => $options['data']['url'] ?? '#',
                ])
                ->add('style', ChoiceType::class, [
                    'label' => 'Button Style',
                    'required' => false,
                    'choices' => [
                        'Primary' => 'primary',
                        'Secondary' => 'secondary',
                        'Outline' => 'outline',
                        'Ghost' => 'ghost',
                        'Danger' => 'danger',
                        'Success' => 'success',
                    ],
                    'data' => $options['data']['style'] ?? 'primary',
                ])
                ->add('background_color', ColorType::class, [
                    'label' => 'Background Color',
                    'required' => false,
                    'data' => $options['data']['background_color'] ?? '',
                ])
                ->add('text_color', ColorType::class, [
                    'label' => 'Text Color',
                    'required' => false,
                    'data' => $options['data']['text_color'] ?? '',
                ])
                ->add('border_color', ColorType::class, [
                    'label' => 'Border Color',
                    'required' => false,
                    'data' => $options['data']['border_color'] ?? '',
                ])
                ->add('border_radius', TextType::class, [
                    'label' => 'Border Radius (px)',
                    'required' => false,
                    'data' => $options['data']['border_radius'] ?? '4',
                ])
                ->add('size', ChoiceType::class, [
                    'label' => 'Button Size',
                    'required' => false,
                    'choices' => [
                        'Small' => 'sm',
                        'Medium' => 'md',
                        'Large' => 'lg',
                    ],
                    'data' => $options['data']['size'] ?? 'md',
                ]);
        }

        // Secondary button block configuration
        if ($blockType === 'secondary_button') {
            $builder
                ->add('text', TextType::class, [
                    'label' => 'Button Text',
                    'required' => true,
                    'data' => $options['data']['text'] ?? '',
                ])
                ->add('url', TextType::class, [
                    'label' => 'Button URL',
                    'required' => false,
                    'data' => $options['data']['url'] ?? '#',
                ])
                ->add('style', ChoiceType::class, [
                    'label' => 'Button Style',
                    'required' => false,
                    'choices' => [
                        'Primary' => 'primary',
                        'Secondary' => 'secondary',
                        'Outline' => 'outline',
                        'Ghost' => 'ghost',
                        'Danger' => 'danger',
                        'Success' => 'success',
                    ],
                    'data' => $options['data']['style'] ?? 'outline',
                ])
                ->add('background_color', ColorType::class, [
                    'label' => 'Background Color',
                    'required' => false,
                    'data' => $options['data']['background_color'] ?? '',
                ])
                ->add('text_color', ColorType::class, [
                    'label' => 'Text Color',
                    'required' => false,
                    'data' => $options['data']['text_color'] ?? '',
                ])
                ->add('border_color', ColorType::class, [
                    'label' => 'Border Color',
                    'required' => false,
                    'data' => $options['data']['border_color'] ?? '',
                ])
                ->add('border_radius', TextType::class, [
                    'label' => 'Border Radius (px)',
                    'required' => false,
                    'data' => $options['data']['border_radius'] ?? '4',
                ])
                ->add('size', ChoiceType::class, [
                    'label' => 'Button Size',
                    'required' => false,
                    'choices' => [
                        'Small' => 'sm',
                        'Medium' => 'md',
                        'Large' => 'lg',
                    ],
                    'data' => $options['data']['size'] ?? 'md',
                ]);
        }

        // Form block configuration (optional inside hero)
        if ($blockType === 'form') {
            $builder
                ->add('form_id', TextType::class, [
                    'label' => 'Form ID / Key',
                    'required' => false,
                    'data' => $options['data']['form_id'] ?? '',
                    'attr' => ['placeholder' => 'e.g., contact-form-001'],
                ])
                ->add('layout_mode', ChoiceType::class, [
                    'label' => 'Layout Mode',
                    'required' => false,
                    'choices' => [
                        'Stacked' => 'stacked',
                        'Inline' => 'inline',
                        'Card' => 'card',
                    ],
                    'data' => $options['data']['layout_mode'] ?? 'stacked',
                ])
                ->add('card_background_color', ColorType::class, [
                    'label' => 'Card Background Color',
                    'required' => false,
                    'data' => $options['data']['card_background_color'] ?? '#ffffff',
                ]);
        }

        // Icon block configuration
        if ($blockType === 'icon') {
            $builder
                ->add('icon_name', TextType::class, [
                    'label' => 'Icon Name (Bootstrap Icons)',
                    'required' => true,
                    'data' => $options['data']['icon_name'] ?? '',
                    'attr' => ['placeholder' => 'e.g., bi-star, bi-envelope'],
                ])
                ->add('icon_size', ChoiceType::class, [
                    'label' => 'Icon Size',
                    'required' => false,
                    'choices' => [
                        'Small' => 'sm',
                        'Medium' => 'md',
                        'Large' => 'lg',
                        'X-Large' => 'xlarge',
                        '2X' => '2x',
                    ],
                    'data' => $options['data']['icon_size'] ?? 'md',
                ])
                ->add('icon_color', ColorType::class, [
                    'label' => 'Icon Color',
                    'required' => false,
                    'data' => $options['data']['icon_color'] ?? '',
                ])
                ->add('alignment', ChoiceType::class, [
                    'label' => 'Alignment',
                    'required' => false,
                    'choices' => [
                        'Left' => 'left',
                        'Center' => 'center',
                        'Right' => 'right',
                    ],
                    'data' => $options['data']['alignment'] ?? 'center',
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'block_type' => 'title',
            'data' => [],
        ]);
    }
}

class HiddenBlockType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data' => 'title',
            'mapped' => false,
        ]);
    }

    public function getParent(): string
    {
        return \Symfony\Component\Form\Extension\Core\Type\HiddenType::class;
    }
}

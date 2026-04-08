<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fieldType = $options['field_type'] ?? 'text';

        $builder
            ->add('type', HiddenFormFieldType::class, [
                'data' => $fieldType,
            ])
            ->add('label', TextType::class, [
                'label' => 'Field Label',
                'required' => true,
                'data' => $options['data']['label'] ?? '',
            ])
            ->add('name', TextType::class, [
                'label' => 'Field Name (variable)',
                'required' => true,
                'data' => $options['data']['name'] ?? '',
                'attr' => ['placeholder' => 'e.g., first_name, email'],
            ])
            ->add('placeholder', TextType::class, [
                'label' => 'Placeholder Text',
                'required' => false,
                'data' => $options['data']['placeholder'] ?? '',
            ])
            ->add('required', CheckboxType::class, [
                'label' => 'Required Field',
                'required' => false,
                'data' => $options['data']['required'] ?? false,
            ])
            ->add('width', ChoiceType::class, [
                'label' => 'Field Width',
                'required' => false,
                'choices' => [
                    'Full Width' => 'full',
                    'Half Width (1/2)' => 'half',
                    'One Third (1/3)' => 'third',
                    'Two Thirds (2/3)' => 'two-thirds',
                ],
                'data' => $options['data']['width'] ?? 'full',
            ])
            ->add('default_value', TextType::class, [
                'label' => 'Default Value',
                'required' => false,
                'data' => $options['data']['default_value'] ?? '',
            ])
            ->add('help_text', TextType::class, [
                'label' => 'Help Text',
                'required' => false,
                'data' => $options['data']['help_text'] ?? '',
                'attr' => ['placeholder' => 'Text shown below the field'],
            ]);

        // Add options for select, radio, checkbox types
        if (in_array($fieldType, ['select', 'radio', 'checkbox'])) {
            $builder->add('options', TextareaType::class, [
                'label' => 'Options (one per line)',
                'required' => false,
                'data' => $options['data']['options'] ?? '',
                'attr' => [
                    'rows' => 4,
                    'placeholder' => "Option 1\nOption 2\nOption 3",
                ],
            ]);
        }

        // Add rows for textarea
        if ($fieldType === 'textarea') {
            $builder->add('rows', ChoiceType::class, [
                'label' => 'Number of Rows',
                'required' => false,
                'choices' => [
                    '2 rows' => '2',
                    '3 rows' => '3',
                    '4 rows' => '4',
                    '5 rows' => '5',
                    '6 rows' => '6',
                ],
                'data' => $options['data']['rows'] ?? '4',
            ]);
        }

        // Add validation pattern for certain types
        if (in_array($fieldType, ['email', 'tel', 'number'])) {
            $builder->add('validation_pattern', TextType::class, [
                'label' => 'Validation Pattern (Regex)',
                'required' => false,
                'data' => $options['data']['validation_pattern'] ?? '',
                'attr' => ['placeholder' => 'e.g., ^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'],
            ]);
        }

        // Add min/max for number fields
        if ($fieldType === 'number') {
            $builder
                ->add('min_value', TextType::class, [
                    'label' => 'Minimum Value',
                    'required' => false,
                    'data' => $options['data']['min_value'] ?? '',
                ])
                ->add('max_value', TextType::class, [
                    'label' => 'Maximum Value',
                    'required' => false,
                    'data' => $options['data']['max_value'] ?? '',
                ]);
        }

        // Add min/max for date fields
        if ($fieldType === 'date') {
            $builder
                ->add('min_date', TextType::class, [
                    'label' => 'Minimum Date',
                    'required' => false,
                    'data' => $options['data']['min_date'] ?? '',
                    'attr' => ['placeholder' => 'YYYY-MM-DD'],
                ])
                ->add('max_date', TextType::class, [
                    'label' => 'Maximum Date',
                    'required' => false,
                    'data' => $options['data']['max_date'] ?? '',
                    'attr' => ['placeholder' => 'YYYY-MM-DD'],
                ]);
        }

        // Add CSS class for customization
        $builder
            ->add('css_class', TextType::class, [
                'label' => 'Custom CSS Class',
                'required' => false,
                'data' => $options['data']['css_class'] ?? '',
                'attr' => ['placeholder' => 'e.g., form-control-lg'],
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'Enable this field',
                'required' => false,
                'data' => $options['data']['enabled'] ?? true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'field_type' => 'text',
            'data' => [],
        ]);
    }
}

class HiddenFormFieldType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data' => 'text',
            'mapped' => false,
        ]);
    }

    public function getParent(): string
    {
        return \Symfony\Component\Form\Extension\Core\Type\HiddenType::class;
    }
}

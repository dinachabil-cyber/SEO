<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Individual Hero field configuration
 * Each field has: label, name/key, type, default value, category
 */
class HeroFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fieldType = $options['field_type'] ?? 'text';
        $fieldCategory = $options['field_category'] ?? 'content';

        $builder
            ->add('type', HiddenHeroFieldType::class, [
                'data' => $fieldType,
            ])
            ->add('category', HiddenHeroFieldCategoryType::class, [
                'data' => $fieldCategory,
            ])
            ->add('label', TextType::class, [
                'label' => 'Field Label',
                'required' => true,
                'data' => $options['data']['label'] ?? '',
                'attr' => ['placeholder' => 'e.g., Hero Title'],
            ])
            ->add('name', TextType::class, [
                'label' => 'Field Name (Key)',
                'required' => true,
                'data' => $options['data']['name'] ?? '',
                'attr' => ['placeholder' => 'e.g., hero_title, primary_button_text'],
            ])
            ->add('default_value', TextType::class, [
                'label' => 'Default Value',
                'required' => false,
                'data' => $options['data']['default_value'] ?? '',
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Category/Tab',
                'required' => false,
                'choices' => [
                    'Content' => 'content',
                    'Media' => 'media',
                    'Style' => 'style',
                    'Buttons' => 'buttons',
                    'Layout' => 'layout',
                ],
                'data' => $options['data']['category'] ?? 'content',
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'Enable this field',
                'required' => false,
                'data' => $options['data']['enabled'] ?? true,
            ]);

        // Add options for select type
        if ($fieldType === 'select') {
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

        // Add placeholder for text/textarea types
        if (in_array($fieldType, ['text', 'textarea', 'email', 'tel', 'url', 'number'])) {
            $builder->add('placeholder', TextType::class, [
                'label' => 'Placeholder Text',
                'required' => false,
                'data' => $options['data']['placeholder'] ?? '',
            ]);
        }

        // Add help text
        $builder->add('help_text', TextType::class, [
            'label' => 'Help Text',
            'required' => false,
            'data' => $options['data']['help_text'] ?? '',
            'attr' => ['placeholder' => 'Optional description'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'field_type' => 'text',
            'field_category' => 'content',
            'data' => [],
        ]);
    }
}

class HiddenHeroFieldType extends AbstractType
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

class HiddenHeroFieldCategoryType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data' => 'content',
            'mapped' => false,
        ]);
    }

    public function getParent(): string
    {
        return \Symfony\Component\Form\Extension\Core\Type\HiddenType::class;
    }
}
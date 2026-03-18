<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Dynamic collection type for hero blocks using JSON data
 */
class HeroBlocksType extends AbstractType
{
    public const BLOCK_TYPES = [
        'title' => 'Title',
        'subtitle' => 'Subtitle',
        'text' => 'Text',
        'badge' => 'Badge',
        'image' => 'Image',
        'button' => 'Button',
        'secondary_button' => 'Secondary Button',
        'form' => 'Form (Optional)',
        'icon' => 'Icon',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('blocks_json', HiddenType::class, [
            'label' => false,
            'required' => false,
            'mapped' => false,
            'data' => json_encode($options['data'] ?? []),
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['blocks'] = $options['data'] ?? [];
        $view->vars['block_types'] = self::BLOCK_TYPES;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data' => [],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'hero_blocks';
    }
}

/**
 * Dynamic collection type for form fields using JSON data
 */
class FormFieldsType extends AbstractType
{
    public const FIELD_TYPES = [
        'text' => 'Text Input',
        'email' => 'Email',
        'tel' => 'Phone',
        'textarea' => 'Text Area',
        'select' => 'Dropdown (Select)',
        'radio' => 'Radio Buttons',
        'checkbox' => 'Checkbox',
        'number' => 'Number',
        'date' => 'Date',
        'hidden' => 'Hidden Field',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('fields_json', HiddenType::class, [
            'label' => false,
            'required' => false,
            'mapped' => false,
            'data' => json_encode($options['data'] ?? []),
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['fields'] = $options['data'] ?? [];
        $view->vars['field_types'] = self::FIELD_TYPES;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data' => [],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'form_fields';
    }
}

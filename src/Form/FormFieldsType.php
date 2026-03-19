<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

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

<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Standalone Form section field builder type.
 * 
 * This type is used for the 'form' section type to pass field configuration
 * to the Twig template. The actual data persistence happens via the 
 * top-level form_fields_json hidden field in PageSectionType.
 * 
 * This type does NOT create any hidden fields - it's purely a view-level
 * type that passes data to the template for rendering.
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

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        // Pass fields data to the template
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
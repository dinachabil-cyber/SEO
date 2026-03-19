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



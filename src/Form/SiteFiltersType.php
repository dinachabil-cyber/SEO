<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteFiltersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('creator', SearchType::class, [
                'label' => 'Creator',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Search by creator name',
                ],
            ])
            ->add('technology', SearchType::class, [
                'label' => 'Technology',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Search by technology',
                ],
            ])
            ->add('hosting', SearchType::class, [
                'label' => 'Hosting',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Search by hosting',
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'required' => false,
                'choices' => [
                    'All' => '',
                    'Draft' => 'Draft',
                    'In Progress' => 'In Progress',
                    'Published' => 'Published',
                    'Suspended' => 'Suspended',
                    'Archived' => 'Archived',
                ],
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('domain', SearchType::class, [
                'label' => 'Domain',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Search by domain',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}

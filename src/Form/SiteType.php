<?php

namespace App\Form;

use App\Entity\Site;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Site Name',
                'required' => true,
            ])
            ->add('domain', TextType::class, [
                'label' => 'Domain',
                'required' => true,
            ])
            ->add('defaultLocale', ChoiceType::class, [
                'label' => 'Default Locale',
                'choices' => [
                    'French' => 'fr',
                    'English' => 'en',
                    'Spanish' => 'es',
                ],
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Site::class,
        ]);
    }
}

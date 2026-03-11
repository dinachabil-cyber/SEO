<?php

namespace App\Form;

use App\Entity\Site;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class SiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('domain', TextType::class, [
                'label' => 'Domain',
                'required' => false,
                'constraints' => [
                    new Regex(
                        pattern: '/^(?:https?:\/\/)?(?:www\.)?[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                        message: 'Please enter a valid domain (e.g., example.com)'
                    ),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'example.com',
                ],
                'help' => 'Optional. Include http:// or https:// if needed',
            ])
            ->add('defaultLocale', ChoiceType::class, [
                'label' => 'Default Locale',
                'choices' => [
                    'French' => 'fr',
                    'English' => 'en',
                    'Spanish' => 'es',
                    'German' => 'de',
                ],
                'constraints' => [
                    new NotBlank(message: 'Please select a default locale'),
                ],
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Active',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                ],
            ])
            ->add('hebergement', TextType::class, [
                'label' => 'Hébergement',
                'required' => false,
                'constraints' => [
                    new Length(max: 255, maxMessage: 'Hébergement cannot exceed {{ limit }} characters'),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter hébergement information',
                ],
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

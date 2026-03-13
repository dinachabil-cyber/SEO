<?php

namespace App\Form;

use App\Entity\Site;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
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
            ->add('hosting', TextType::class, [
                'label' => 'Hosting',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter hosting information',
                ],
            ])
            ->add('databaseName', TextType::class, [
                'label' => 'Database Name',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter database name',
                ],
            ])
            ->add('databasePassword', TextType::class, [
                'label' => 'Database Password',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter database password',
                ],
            ])
            ->add('technology', TextType::class, [
                'label' => 'Technology',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter technology stack',
                ],
            ])
            ->add('publishedAt', DateTimeType::class, [
                'label' => 'Published At',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Active',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'required' => true,
                'choices' => [
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
            ->add('companyName', TextType::class, [
                'label' => 'Company Name',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter company name',
                ],
            ])
            ->add('address', TextType::class, [
                'label' => 'Address',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter address',
                ],
            ])
            ->add('phone', TextType::class, [
                'label' => 'Phone',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter phone number',
                ],
            ])
            ->add('email', TextType::class, [
                'label' => 'Email',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter email address',
                ],
            ])
            ->add('legalRepresentative', TextType::class, [
                'label' => 'Legal Representative',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter legal representative name',
                ],
            ])
            ->add('registrationNumber', TextType::class, [
                'label' => 'Registration Number',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter registration number',
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

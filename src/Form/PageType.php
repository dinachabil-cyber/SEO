<?php

namespace App\Form;

use App\Entity\Page;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'constraints' => [
                    new NotBlank(message: 'Please enter a slug'),
                    new Regex(
                        pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                        message: 'Slug must contain only lowercase letters, numbers, and hyphens'
                    ),
                    new Length(max: 255, maxMessage: 'Slug cannot exceed {{ limit }} characters'),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'example-page',
                    'pattern' => '[a-z0-9-]+',
                ],
                'help' => 'Use lowercase letters, numbers, and hyphens only',
            ])
            ->add('metaTitle', TextType::class, [
                'label' => 'Meta Title',
                'constraints' => [
                    new NotBlank(message: 'Please enter a meta title'),
                    new Length(
                        max: 70,
                        maxMessage: 'Meta title cannot exceed {{ limit }} characters',
                        min: 60,
                        minMessage: 'Meta title should be at least {{ limit }} characters'
                    ),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Page meta title for SEO',
                    'maxlength' => 70,
                ],
                'help' => '60-70 characters recommended for best SEO results',
            ])
            ->add('metaDescription', TextareaType::class, [
                'label' => 'Meta Description',
                'constraints' => [
                    new NotBlank(message: 'Please enter a meta description'),
                    new Length(
                        max: 170,
                        maxMessage: 'Meta description cannot exceed {{ limit }} characters',
                        min: 160,
                        minMessage: 'Meta description should be at least {{ limit }} characters'
                    ),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Page meta description for SEO',
                    'maxlength' => 170,
                    'rows' => 3,
                ],
                'help' => '160-170 characters recommended for best SEO results',
            ])
            ->add('h1', TextType::class, [
                'label' => 'H1 Heading',
                'constraints' => [
                    new NotBlank(message: 'Please enter an H1 heading'),
                    new Length(max: 255, maxMessage: 'H1 heading cannot exceed {{ limit }} characters'),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Main page heading',
                ],
            ])
            ->add('isPublished', CheckboxType::class, [
                'label' => 'Published',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                ],
                'help' => 'Published pages will be visible to visitors',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
        ]);
    }
}

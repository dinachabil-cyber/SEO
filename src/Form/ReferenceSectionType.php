<?php

namespace App\Form;

use App\Entity\ReferenceSection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ReferenceSectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Reference Name',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter a name for this reference',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Section Type',
                'choices' => array_combine(ReferenceSection::ALLOWED_TYPES, ReferenceSection::ALLOWED_TYPES),
                'required' => true,
                'attr' => [
                    'class' => 'form-select',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReferenceSection::class,
        ]);
    }
}

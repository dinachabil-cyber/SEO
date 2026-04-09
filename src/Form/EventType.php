<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Site;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $sites = $options['sites'] ?? [];
        $users = $options['users'] ?? [];
        $defaultStart = $options['default_start'] ?? null;
        $defaultEnd = $options['default_end'] ?? null;

        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Please enter a title'),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter event title',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Please enter a description'),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Enter event description',
                ],
            ])
            ->add('startAt', DateTimeType::class, [
                'label' => 'Start Date & Time',
                'required' => true,
                'widget' => 'single_text',
                'data' => $defaultStart ?? new \DateTimeImmutable(),
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('endAt', DateTimeType::class, [
                'label' => 'End Date & Time',
                'required' => true,
                'widget' => 'single_text',
                'data' => $defaultEnd ?? (new \DateTimeImmutable())->modify('+1 hour'),
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('location', TextType::class, [
                'label' => 'Location',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter location (optional)',
                ],
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Please enter a slug'),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'e.g., team-meeting-april-2026',
                ],
                'help' => 'URL-friendly identifier (e.g., team-meeting-april-8-2026)',
            ]);

        if (!empty($sites)) {
            $builder->add('site', EntityType::class, [
                'label' => 'Site',
                'required' => true,
                'class' => Site::class,
                'choices' => $sites,
                'choice_label' => 'domain',
                'placeholder' => 'Select a site',
                'attr' => [
                    'class' => 'form-select',
                ],
                'constraints' => [
                    new NotBlank(message: 'Please select a site'),
                ],
            ]);
        }

        if (!empty($users)) {
            $builder->add('assignedUsers', EntityType::class, [
                'label' => 'Team Members',
                'required' => false,
                'class' => User::class,
                'choices' => $users,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'class' => 'team-members-checkboxes',
                ],
                'help' => 'Select team members who will be working on this event',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'sites' => [],
            'users' => [],
            'default_start' => null,
            'default_end' => null,
        ]);
    }
}

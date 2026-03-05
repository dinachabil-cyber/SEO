<?php

namespace App\Form;

use App\Entity\PageSection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PageSectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => array_combine(PageSection::ALLOWED_TYPES, PageSection::ALLOWED_TYPES),
                'label' => 'Section Type',
                'placeholder' => 'Choose a section type',
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
        ;

        // Dynamic fields based on type
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $section = $event->getData();
            $form = $event->getForm();

            if ($section && $section->getType()) {
                $this->addDynamicFields($form, $section->getType(), $section->getData());
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (isset($data['type'])) {
                $this->addDynamicFields($form, $data['type'], $data['data'] ?? []);
            }
        });
    }

    public static function addDynamicFields($form, $type, $existingData = [])
    {
        $form->add('data', SectionDataType::class, [
            'type' => $type,
            'existing_data' => $existingData,
            'label' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PageSection::class,
        ]);
    }
}

class SectionDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $type = $options['type'];
        $existingData = $options['existing_data'];

        switch ($type) {
            case 'header':
                $builder
                    ->add('brandText', TextType::class, [
                        'label' => 'Brand Text',
                        'required' => true,
                        'data' => $existingData['brandText'] ?? '',
                    ])
                    ->add('logoUrl', TextType::class, [
                        'label' => 'Logo URL',
                        'required' => false,
                        'data' => $existingData['logoUrl'] ?? '',
                    ])
                    ->add('menuItems', TextareaType::class, [
                        'label' => 'Menu Items (Label|/url per line)',
                        'required' => false,
                        'data' => $existingData['menuItems'] ?? '',
                    ])
                    ->add('ctaText', TextType::class, [
                        'label' => 'CTA Text',
                        'required' => true,
                        'data' => $existingData['ctaText'] ?? '',
                    ])
                    ->add('ctaUrl', TextType::class, [
                        'label' => 'CTA URL',
                        'required' => true,
                        'data' => $existingData['ctaUrl'] ?? '',
                    ])
                    ->add('background', ColorType::class, [
                        'label' => 'Background Color',
                        'required' => false,
                        'data' => $existingData['background'] ?? '',
                    ]);
                break;

            case 'hero':
                $builder
                    ->add('title', TextType::class, [
                        'label' => 'Title',
                        'required' => true,
                        'data' => $existingData['title'] ?? '',
                    ])
                    ->add('subtitle', TextareaType::class, [
                        'label' => 'Subtitle',
                        'required' => false,
                        'data' => $existingData['subtitle'] ?? '',
                    ])
                    ->add('imageUrl', TextType::class, [
                        'label' => 'Image URL',
                        'required' => false,
                        'data' => $existingData['imageUrl'] ?? '',
                    ])
                    ->add('ctaText', TextType::class, [
                        'label' => 'CTA Text',
                        'required' => true,
                        'data' => $existingData['ctaText'] ?? '',
                    ])
                    ->add('ctaUrl', TextType::class, [
                        'label' => 'CTA URL',
                        'required' => true,
                        'data' => $existingData['ctaUrl'] ?? '',
                    ])
                    ->add('showForm', CheckboxType::class, [
                        'label' => 'Show Form',
                        'required' => false,
                        'data' => $existingData['showForm'] ?? false,
                    ]);
                break;

            case 'body':
                $builder
                    ->add('content', TextareaType::class, [
                        'label' => 'Content',
                        'required' => true,
                        'data' => $existingData['content'] ?? '',
                        'attr' => ['rows' => 10],
                    ]);
                break;

            case 'image':
                $builder
                    ->add('imageUrl', TextType::class, [
                        'label' => 'Image URL',
                        'required' => true,
                        'data' => $existingData['imageUrl'] ?? '',
                    ])
                    ->add('alt', TextType::class, [
                        'label' => 'Alt Text',
                        'required' => true,
                        'data' => $existingData['alt'] ?? '',
                    ])
                    ->add('caption', TextareaType::class, [
                        'label' => 'Caption',
                        'required' => false,
                        'data' => $existingData['caption'] ?? '',
                    ]);
                break;

            case 'cards':
                $builder
                    ->add('sectionTitle', TextType::class, [
                        'label' => 'Section Title',
                        'required' => false,
                        'data' => $existingData['sectionTitle'] ?? '',
                    ])
                    ->add('cards', CollectionType::class, [
                        'entry_type' => CardType::class,
                        'entry_options' => ['label' => false],
                        'allow_add' => true,
                        'allow_delete' => true,
                        'prototype' => true,
                        'data' => $existingData['cards'] ?? [],
                        'label' => 'Cards',
                    ]);
                break;

            case 'faq':
                $builder
                    ->add('sectionTitle', TextType::class, [
                        'label' => 'Section Title',
                        'required' => false,
                        'data' => $existingData['sectionTitle'] ?? '',
                    ])
                    ->add('items', CollectionType::class, [
                        'entry_type' => FaqItemType::class,
                        'entry_options' => ['label' => false],
                        'allow_add' => true,
                        'allow_delete' => true,
                        'prototype' => true,
                        'data' => $existingData['items'] ?? [],
                        'label' => 'FAQ Items',
                    ]);
                break;

            case 'form':
                $builder
                    ->add('title', TextType::class, [
                        'label' => 'Form Title',
                        'required' => true,
                        'data' => $existingData['title'] ?? '',
                    ])
                    ->add('fields', ChoiceType::class, [
                        'label' => 'Fields',
                        'required' => true,
                        'multiple' => true,
                        'expanded' => true,
                        'choices' => [
                            'Name' => 'name',
                            'Email' => 'email',
                            'Phone' => 'phone',
                            'Message' => 'message',
                        ],
                        'data' => $existingData['fields'] ?? [],
                    ])
                    ->add('submitText', TextType::class, [
                        'label' => 'Submit Button Text',
                        'required' => true,
                        'data' => $existingData['submitText'] ?? 'Submit',
                    ])
                    ->add('successMessage', TextareaType::class, [
                        'label' => 'Success Message',
                        'required' => false,
                        'data' => $existingData['successMessage'] ?? '',
                    ]);
                break;

            case 'cta':
                $builder
                    ->add('title', TextType::class, [
                        'label' => 'Title',
                        'required' => true,
                        'data' => $existingData['title'] ?? '',
                    ])
                    ->add('text', TextareaType::class, [
                        'label' => 'Text',
                        'required' => false,
                        'data' => $existingData['text'] ?? '',
                    ])
                    ->add('buttonText', TextType::class, [
                        'label' => 'Button Text',
                        'required' => true,
                        'data' => $existingData['buttonText'] ?? 'Learn More',
                    ])
                    ->add('buttonUrl', TextType::class, [
                        'label' => 'Button URL',
                        'required' => true,
                        'data' => $existingData['buttonUrl'] ?? '',
                    ]);
                break;

            case 'footer':
                $builder
                    ->add('text', TextareaType::class, [
                        'label' => 'Footer Text',
                        'required' => true,
                        'data' => $existingData['text'] ?? '',
                    ])
                    ->add('links', TextareaType::class, [
                        'label' => 'Links (Label|/url per line)',
                        'required' => false,
                        'data' => $existingData['links'] ?? '',
                    ])
                    ->add('phone', TextType::class, [
                        'label' => 'Phone',
                        'required' => false,
                        'data' => $existingData['phone'] ?? '',
                    ])
                    ->add('email', EmailType::class, [
                        'label' => 'Email',
                        'required' => false,
                        'data' => $existingData['email'] ?? '',
                    ]);
                break;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'type' => '',
            'existing_data' => [],
        ]);
    }
}

class CardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Title'])
            ->add('description', TextareaType::class, ['label' => 'Description', 'attr' => ['rows' => 3]])
            ->add('imageUrl', TextType::class, ['label' => 'Image URL', 'required' => false])
            ->add('linkUrl', TextType::class, ['label' => 'Link URL', 'required' => false]);
    }
}

class FaqItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', TextType::class, ['label' => 'Question'])
            ->add('answer', TextareaType::class, ['label' => 'Answer', 'attr' => ['rows' => 3]]);
    }
}

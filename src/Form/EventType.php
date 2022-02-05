<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Choisissez un titre',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner un titre.'
                    ]),
                    new Length([
                        'min' => 1,
                        'max' => 150,
                        'minMessage' => 'Le titre doit contenir au moins {{ limit }} caractère',
                        'maxMessage' => 'Le titre doit contenir au maximum {{ limit }} caractères',

                    ])
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu de l\'annonce',
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner un contenu.'
                    ]),
                    new Length([
                        'min' => 10,
                        'max' => 5000,
                        'minMessage' => 'Le contenu doit contenir au moins {{ limit }} caractère',
                        'maxMessage' => 'Le contenu doit contenir au maximum {{ limit }} caractères',
                    ])
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Envoyer',
                'attr' => [
                    'class' => 'btn btn-darkblue col-12'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use FOS\CKEditorBundle\Form\Type\CKEditorType;

class CreateCommentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', CKEditorType::class, [
                'label' => false,
                // utilisation du bundle exercise/hmtlpurifier pour contrer faille XSS
                'purify_html' => true,
                'attr' => [
                    'class' => 'col-12',
                    'rows' => 10,
                    'placeholder' => 'Laissez votre commentaire...'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner un contenu',
                    ]),
                    new Length([
                        'min' => 1,
                        'max' => 2000,
                        'minMessage' => 'Le commentaire doit contenir au moins {{ limit }} caractère(s)',
                        'maxMessage' => 'Le commentaire doit contenir au maximum {{ limit }} caractères',
                    ]),
                ],
            ])

            ->add('save', SubmitType::class, [
                'label' => 'Publier la réponse',
                'attr' => [
                    'class' => 'btn btn-darkblue col-12',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}

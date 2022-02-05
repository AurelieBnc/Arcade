<?php

namespace App\Form;

use App\Entity\Forum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use FOS\CKEditorBundle\Form\Type\CKEditorType;

class ForumFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Ecrivez un titre'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner un contenu',
                    ]),
                    new Length([
                        'min' => 1,
                        'max' => 150,
                        'minMessage' => 'Le titre doit contenir au moins {{ limit }} caractère(s)',
                        'maxMessage' => 'Le titre doit contenir au maximum {{ limit }} caractères',
                    ]),
                ],
            ])

            ->add('content', CKEditorType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'rows' => 10,
                ],

                // Liste des contraintes du champ
                'constraints' => [

                    // Ne doit pas être vide
                    new NotBlank([
                        'message' => 'Merci de renseigner votre message' // Message d'erreur si cette contrainte n'est pas respectée
                    ]),

                    // Doit avoir une certaine taille
                    new Length([
                        'min' => 10, // Taille minimum autorisée
                        'minMessage' => 'Le message doit contenir au moins {{ limit }} caractères',   // Message d'erreur si plus petit
                        'max' => 20000,   // Taille maximum autorisée
                        'maxMessage' => 'Le message doit contenir au maximum {{ limit }} caractères',  // Message d'erreur si plus grand
                    ]),
                ]
            ])

            ->add('save', SubmitType::class, [
                'label' => 'Publier',
                'attr' => [
                    'class' => 'btn btn-darkblue col-12',
                ],
            ])
        ;
    }
}


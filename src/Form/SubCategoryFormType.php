<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class SubCategoryFormType extends AbstractType
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
                        'max' => 15,
                        'minMessage' => 'Le titre doit contenir au moins {{ limit }} caractère(s)',
                        'maxMessage' => 'Le titre doit contenir au maximum {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Sélectionnez une photo',
                'constraints' => [
                    new File([
                        // Taille maximum de 1Mo
                        'maxSize' => '1M',
    
                        // jpg et png uniquement
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
    
                        // Message d'erreur en cas de fichier au type non autorisé
                        'mimeTypesMessage' => 'L\'image doit être de type jpg ou png',
    
                        // Message en cas de fichier trop gros
                        'maxSizeMessage' => 'Fichier trop volumineux ({{ size }} {{ suffix }}). La taille maximum autorisée est {{ limit }}{{ suffix }}',
                    ]),
                    new NotBlank([
                        // Message en cas de formulaire envoyé sans fichier
                        'message' => 'Vous devez sélectionner un fichier',
                    ])
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

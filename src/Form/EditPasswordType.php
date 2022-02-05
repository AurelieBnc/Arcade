<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class EditPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('oldPass', PasswordType::class, [
                'label' => false,
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'Mot de passe actuel'
                ],
                'constraints' => [
                    new UserPassword,
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Le titre doit contenir au minimum {{ limit }} caractères',
                        'maxMessage' => 'Le titre doit contenir au maximum {{ limit }} caractères',
                    ]),
                    new NotBlank([
                        'message' => 'Merci de renseigner un mot de passe',
                    ]),
                ]
            ])

            ->add('pass', PasswordType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Nouveau mot de passe'
                ],
                'constraints' => [
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Le titre doit contenir au minimum {{ limit }} caractères',
                        'maxMessage' => 'Le titre doit contenir au maximum {{ limit }} caractères',
                    ]),
                    new NotBlank([
                        'message' => 'Merci de renseigner un mot de passe',
                    ]),
                    new Regex([
                        'pattern' => '/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[ !\"\#\$%&\'\(\)*+,\-.\/:;<=>?@[\\^\]_`\{|\}~])^.{8,4096}$/',
                        'message' => 'Votre mot de passe doit contenir au minimum 8 caractère avec obligatoirement une minuscule, une majuscule, un chiffre et un caractère spécial'
                    ]),
                ]
            ])

            ->add('confirm-pass' , PasswordType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Confirmer le mot de passe'
                ],
                'constraints' => [
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Le titre doit contenir au minimum {{ limit }} caractères',
                        'maxMessage' => 'Le titre doit contenir au maximum {{ limit }} caractères',
                    ]),
                    new NotBlank([
                        'message' => 'Merci de confirmer le mot de passe',
                    ]),
                    new Regex([
                        'pattern' => '/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[ !\"\#\$%&\'\(\)*+,\-.\/:;<=>?@[\\^\]_`\{|\}~])^.{8,4096}$/',
                        'message' => 'Votre mot de passe doit contenir au minimum 8 caractère avec obligatoirement une minuscule, une majuscule, un chiffre et un caractère spécial'
                    ]),
                ],
            ])

            ->add('save', SubmitType::class, [
                'label' => "Enregistrer",
                'attr'  => [
                    'class' => 'btn btn-secondary bg-lightblue col-12'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}

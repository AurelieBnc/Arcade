<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;



class EditEmailType extends AbstractType
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

            ->add('mail', EmailType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Nouvelle adresse email'
                ],
                'constraints' => [
                    new Email([
                        'message' => 'L\'adresse email {{ value }} n\'est pas une adresse email valide'
                    ]),
                    new NotBlank([
                        'message' => 'Merci de renseigner une adresse mail',
                    ]),
                ]
            ])

            ->add('confirm-mail' , EmailType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Confirmer l\'adresse email'
                ],
                'constraints' => [
                    new Email([
                        'message' => 'L\'adresse email {{ value }} n\'est pas une adresse email valide'
                    ]),
                    new NotBlank([
                        'message' => 'Merci de confirmer l\'adresse mail',
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

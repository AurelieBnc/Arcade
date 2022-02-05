<?php

namespace App\Form;

use App\Entity\Forum;
use App\Entity\SubCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoveForumType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subCategory', EntityType::class, [
                'label' => 'Deplacer le forum vers',
                'attr' => [
                    'class' => 'form-select'
                ],
                'class' => SubCategory::class,
                'choice_label' => 'title'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Déplacer',
                'attr' => [
                    'class' => 'btn btn-darkblue col-12'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Forum::class,
        ]);
    }
}

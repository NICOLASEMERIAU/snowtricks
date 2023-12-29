<?php

namespace App\Form;

use App\Entity\Commentary;
use App\Entity\Trick;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentaryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextType::class, options: [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Ajoute ton commentaire',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
            ])
            ->add('trick', EntityType::class, [
                'class' => Trick::class,
'choice_label' => 'id',
            ])
            ->add('created_by', EntityType::class, [
                'class' => User::class,
'choice_label' => 'id',
            ])
            ->add(child: 'Commenter', type: SubmitType::class, options: [
                'attr' => [
                    'class' => 'btn btn-primary mt-4'
                ],
                'label' => 'Commenter'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentary::class,
        ]);
    }
}

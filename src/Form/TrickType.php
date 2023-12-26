<?php

namespace App\Form;

use App\Entity\Trick;
use App\Entity\TricksGroup;
use App\Entity\User;
use App\Repository\TricksGroupRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                  'class' => 'form-control',
                  'minlength' => '2',
                  'maxlength' => '50'
                ],
                'label' => 'Nom du trick',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                'constraints' => [
                    new Assert\Length(['min' => 2, 'max' => 100]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('description', TextType::class, options: [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Description du trick',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
            ])
            ->add('created_at')
            ->add('updated_at')
            ->add('trickgroup', EntityType::class, options :[
                'class' => TricksGroup::class,
                'choice_label' => 'name_group',
                'query_builder' => function (TricksGroupRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.name_group', 'ASC');
                },
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Groupe du trick',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
            ])
            ->add('createdBy', EntityType::class, options :[
                'class' => User::class,
                'choice_label' => 'username',
            ])
            ->add(child: 'Editer', type: SubmitType::class, options: [
                'attr' => [
                    'class' => 'btn btn-primary mt-4'
                ],
                'label' => 'Editer un trick'
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}

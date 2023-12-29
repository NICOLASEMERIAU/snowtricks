<?php

namespace App\Form;

use App\Entity\Trick;
use App\Entity\TricksGroup;
use App\Entity\User;
use App\Repository\TricksGroupRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;

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
            ->add(child: 'mainimage')
            ->add('mainimageFile', FileType::class, [
                'label' => 'Votre image principale du trick (jpeg, jpg, png uniquement)',
                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using attributes
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png'
                        ],
                        'mimeTypesMessage' => 'Les fichiers jpeg, jpg et png sont autorisés',
                    ])
                ],
            ])
            ->add('images', type: CollectionType::class, options: [
                'entry_type' => ImageType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
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

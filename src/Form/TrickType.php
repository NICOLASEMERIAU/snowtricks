<?php

namespace App\Form;

use App\Entity\Image;
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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotNull;

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Trick|null $trick */
        $trick = $options['data'] ?? null;
        $isEdit = $trick && $trick->getId();

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
            ->add('description', TextareaType::class, options: [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Description du trick',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                'constraints' => [
                    new Assert\Length(['min' => 2]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('tricksGroup', EntityType::class, options :[
                'class' => TricksGroup::class,
                'choice_label' => 'name',
                'query_builder' => function (TricksGroupRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.name', 'ASC');
                },
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Groupe du trick',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
            ]);

        $imageConstraints = [
            new File([
                'maxSize' => '5M',
                'mimeTypes' => [
                    'image/jpeg',
                    'image/jpg',
                    'image/png'
                ],
                'mimeTypesMessage' => 'Les fichiers jpeg, jpg et png sont autorisés',
            ])
        ];

        if (!$isEdit || !$trick->getImageName()) {
            $imageConstraints[] = new NotNull([
                'message' => 'Une image est requise pour créer ce trick',
            ]);
        }

        $builder
            ->add('mainImageFile', FileType::class, [
                'label' => 'Votre image principale du trick (jpeg, jpg, png uniquement)',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                // unmapped means that this field is not associated to any entity property
                'mapped' => false,
                'attr' => [
                    'class' => 'btn btn-primary mt-4'
                ],
                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using attributes
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => $imageConstraints

            ])
            ->add('images', type: CollectionType::class, options: [
                'mapped' => false,
                'entry_type' => ImageType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
            ])
            ->add('videos', type: CollectionType::class, options: [
                'entry_type' => VideoType::class,
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

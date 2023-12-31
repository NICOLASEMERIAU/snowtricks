<?php

namespace App\Form;

use App\Entity\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints as Assert;


class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', FileType::class, [
                'label' => false,
                'mapped' => false,
                'multiple' => true,
                'required' => false,
                'constraints' => [
                    new All([
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png'
                        ],
                        'mimeTypesMessage' => 'Les fichiers jpeg, jpg et png sont autorisés',
                    ])]),
                    new Assert\Valid()
                ],
            ])
//            ->add('name', TextType::class, [
//                'attr' => [
//                    'class' => 'form-control',
//                    'minlength' => '2',
//                    'maxlength' => '50'
//                ],
//                'label' => 'Nom de la photo',
//                'label_attr' => [
//                    'class' => 'form-label mt-4'
//                ],
//                'constraints' => [
//                    new Assert\Length(['min' => 2, 'max' => 100]),
//                    new Assert\NotBlank()
//                ]
//            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }
}

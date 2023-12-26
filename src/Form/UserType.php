<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Adresse email manquante.']),
                    new Email(['message' => 'Adresse email invalide.']),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Length([
                        'min' => 1,
                        'minMessage' => 'Le mot de passe doit faire au moins {{ limit }} caractères.',
                        'max' => 255,
                        'maxMessage' => 'Le mot de passe ne doit pas dépasser {{ limit }} caractères.',
                    ])
                ],
            ])
            ->add('username', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Pseudo manquant.']),
                ],
            ])
            ->add(child: 'Valider', type: SubmitType::class, options: [
                'attr' => [
                    'class' => 'btn btn-primary mt-4'
                ],
                'label' => 'Valider'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

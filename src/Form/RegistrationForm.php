<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class RegistrationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor ingrese un correo electrónico',
                    ]),
                    new Email([
                        'message' => 'El correo electrónico no es válido',
                    ]),
                ],
            ])
            ->add('username', TextType::class, [
                'label' => 'Nombre de usuario',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor ingrese un nombre de usuario',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Su nombre de usuario debe tener al menos {{ limit }} caracteres',
                        'max' => 50,
                        'maxMessage' => 'Su nombre de usuario no puede tener más de {{ limit }} caracteres',
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Debe aceptar los términos.',
                    ]),
                ],
            ])
          ->add('plainPassword', PasswordType::class, [
    // instead of being set onto the object directly,
    // this is read and encoded in the controller
    'mapped' => false,
    'attr' => ['autocomplete' => 'new-password'],
    'constraints' => [
        new NotBlank([
            'message' => 'Por favor ingrese una contraseña',
        ]),
        new Length([
            'min' => 6,
            'minMessage' => 'Su contraseña debe tener al menos {{ limit }} caracteres',
            // max length allowed by Symfony for security reasons
            'max' => 4096,
        ]),
    ],
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

<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class PasswordResetRequestForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir votre adresse email',
                    ]),
                    new Email([
                        'message' => 'Veuillez saisir une adresse email valide',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Entrez votre adresse email'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}

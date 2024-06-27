<?php

/**
 * Registration form.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class RegistrationFormType.
 *
 * Defines the registration form with email, password, and password confirmation fields.
 */
class RegistrationFormType extends AbstractType
{
    /**
     * Builds the registration form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options An array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'label.email',
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'label.password',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 6]),
                ],
            ])
            ->add('confirmPassword', PasswordType::class, [
                'label' => 'label.password_confirm',
                'constraints' => [
                    new NotBlank(),
                ],
                'mapped' => false,
            ]);
    }

    /**
     * Configures the options for this form type.
     *
     * @param OptionsResolver $resolver The options resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}

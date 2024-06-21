<?php

/**
 * User type.
 */

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserType.
 *
 * This class defines the form type for user entity.
 */
class UserType extends AbstractType
{
    /**
     * Build the user form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options Additional options for the form
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email', EmailType::class)
        ->add('plainPassword', PasswordType::class, [
            'required' => false,
            'mapped' => false,
            'label' => 'Nowe Hasło (pozostaw puste, jeśli nie chcesz zmieniać hasła)',
        ])
        ->add('roles', ChoiceType::class, [
            'choices' => [
                'Użytkownik' => 'ROLE_USER',
                'Administrator' => 'ROLE_ADMIN',
            ],
            'multiple' => true,
            'expanded' => true,
        ]);
    }

    /**
     * Configure the options for the user form.
     *
     * @param OptionsResolver $resolver The options resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

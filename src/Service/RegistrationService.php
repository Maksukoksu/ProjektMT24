<?php

/**
 * Registration service.
 */

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class RegistrationService.
 *
 * Implements user registration logic.
 */
class RegistrationService implements RegistrationServiceInterface
{
    /**
     * RegistrationService constructor.
     *
     * @param UserPasswordHasherInterface $passwordHasher The password hasher
     * @param UserRepository              $userRepository The user repository
     */
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher, private readonly UserRepository $userRepository)
    {
    }

    /**
     * Registers a new user.
     *
     * @param string $email         The email of the user
     * @param string $plainPassword The plain password of the user
     *
     * @return User The registered user
     */
    public function registerUser(string $email, string $plainPassword): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_USER']);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->userRepository->save($user, true);

        return $user;
    }
}

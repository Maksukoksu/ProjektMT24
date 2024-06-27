<?php

/**
 * Registration service interface.
 */

namespace App\Service;

use App\Entity\User;

/**
 * Interface RegistrationServiceInterface.
 *
 * Defines the contract for user registration services.
 */
interface RegistrationServiceInterface
{
    /**
     * Registers a new user.
     *
     * @param string $email         The email of the user
     * @param string $plainPassword The plain password of the user
     *
     * @return User The registered user
     */
    public function registerUser(string $email, string $plainPassword): User;
}

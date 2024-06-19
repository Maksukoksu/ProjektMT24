<?php
/**
 * Change information service interface.
 */

namespace App\Service;

use App\Entity\User;

/**
 * Interface ChangeInfoServiceInterface.
 */
interface ChangeInfoServiceInterface
{
    /**
     * Change Information.
     *
     * @param User  $user User
     * @param array $data Data with new user information
     */
    public function changeInfo(User $user, array $data): void;
}

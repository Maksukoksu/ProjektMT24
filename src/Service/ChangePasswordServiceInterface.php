<?php
/**
 * Change password service interface.
 */

namespace App\Service;

use App\Entity\User;

/**
 * Interface ChangePasswordServiceInterface.
 */
interface ChangePasswordServiceInterface
{
    /**
     * Change Password.
     *
     * @param User   $user        User
     * @param string $oldPassword Old password
     * @param string $newPassword New password
     */
    public function changePassword(User $user, string $oldPassword, string $newPassword): void;
}

<?php
/**
 * Change Information service.
 */

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ChangeInfoService.
 */
class ChangeInfoService implements ChangeInfoServiceInterface
{
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager Entity Manager Interface
     */
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Change Information.
     *
     * @param User  $user User
     * @param array $data Data with new user information
     */
    public function changeInfo(User $user, array $data): void
    {
        $user->setEmail($data['email']);
        $this->entityManager->flush();
    }
}

<?php
/**
 * Admin User controller.
 */


namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\Type\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserController
 */
class UserController extends AbstractController
{
    /**
     * List all users.
     *
     * @Route("/admin/users", name="admin_user_list")
     *
     * @param UserRepository $userRepository
     *
     * @return Response
     */

    #[Route('/admin/users', name: 'admin_user_list')]
    public function list(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $userRepository->findAll();

        return $this->render('admin/user_list.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * Edit a user.
     *
     * @Route("/admin/user/edit/{id}", name="admin_user_edit")
     *
     * @param User                        $user
     * @param Request                     $request
     * @param EntityManagerInterface      $entityManager
     * @param UserPasswordHasherInterface $passwordHasher
     *
     * @return Response
     */

    #[Route('/admin/user/edit/{id}', name: 'admin_user_edit')]
    public function edit(User $user, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $encodedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($encodedPassword);
            }

            $entityManager->flush();

            return $this->redirectToRoute('admin_user_list');
        }

        return $this->render('admin/user_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

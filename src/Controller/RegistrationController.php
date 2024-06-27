<?php

/**
 * Registration controller.
 */

namespace App\Controller;

use App\Form\Type\RegistrationFormType;
use App\Service\RegistrationServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RegistrationController.
 *
 * Handles user registration actions.
 */
class RegistrationController extends AbstractController
{
    /**
     * RegistrationController constructor.
     *
     * @param RegistrationServiceInterface $registrationService The registration service
     */
    public function __construct(private readonly RegistrationServiceInterface $registrationService)
    {
    }

    /**
     * Register action.
     *
     * @param Request $request The HTTP request
     *
     * @return Response The HTTP response
     */
    #[\Symfony\Component\Routing\Attribute\Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $email = $data['email'];
            $plainPassword = $data['plainPassword'];
            $confirmPassword = $form->get('confirmPassword')->getData();

            if ($plainPassword !== $confirmPassword) {
                $this->addFlash('error', 'Passwords do not match.');

                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            $this->registrationService->registerUser($email, $plainPassword);
            $this->addFlash('success', 'Registration successful!');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}

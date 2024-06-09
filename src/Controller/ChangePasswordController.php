<?php
/**
 * Change password controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\ChangePasswordType;
use App\Service\ChangePasswordService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ChangePasswordController.
 */
class ChangePasswordController extends AbstractController
{
    /**
     * Change Password Service.
     */
    private ChangePasswordService $changePasswordService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * Constructor.
     *
     * @param ChangePasswordService $changePasswordService Change Password Service
     * @param TranslatorInterface   $translator            Translator
     */
    public function __construct(ChangePasswordService $changePasswordService, TranslatorInterface $translator)
    {
        $this->changePasswordService = $changePasswordService;
        $this->translator = $translator;
    }

    /**
     * ChangePassword action.
     *
     * @param Request $request HTTP Request
     *
     * @return Response HTTP response
     */
    #[Route('/change-password', name: 'user_change_password', methods: 'GET|POST')]
    public function changePassword(Request $request, ChangePasswordService $changePasswordService): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $oldPassword = $form->get('oldPassword')->getData(); // Pobierz stare hasło z formularza
            $newPassword = $form->get('newPassword')->getData(); // Pobierz nowe hasło z formularza

            try {
                $changePasswordService->changePassword($user, $oldPassword, $newPassword);

                $this->addFlash('success', $this->translator->trans('message.password_changed'));

                return $this->redirectToRoute('wallet_index');
            } catch (\Exception $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('user/changePassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}

<?php
/**
 * Change info controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\ChangeInfoType;
use App\Service\ChangeInfoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ChangeInfoController.
 */
class ChangeInfoController extends AbstractController
{
    /**
     * Change info service.
     */
    private ChangeInfoService $changeInfoService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * Constructor.
     *
     * @param ChangeInfoService   $changeInfoService Change Info Service
     * @param TranslatorInterface $translator        Translator
     */
    public function __construct(ChangeInfoService $changeInfoService, TranslatorInterface $translator)
    {
        $this->changeInfoService = $changeInfoService;
        $this->translator = $translator;
    }

    /**
     * ChangeEmail action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/change-email', name: 'user_change_email', methods: 'GET|POST')]
    public function changeEmail(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ChangeInfoType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->changeInfoService->changeInfo($user, $form->getData());

            $this->addFlash(
                'success',
                $this->translator->trans('message.email_changed')
            );

            return $this->redirectToRoute('wallet_index');
        }

        return $this->render('user/changeInfo.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * ChangeInfo action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/change-info', name: 'user_change_info', methods: 'GET|POST')]
    public function changeInfo(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ChangeInfoType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->changeInfoService->changeInfo($user, $form->getData());

            $this->addFlash(
                'success',
                $this->translator->trans('message.info_changed')
            );

            return $this->redirectToRoute('wallet_index');
        }

        return $this->render('user/changeInfo.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

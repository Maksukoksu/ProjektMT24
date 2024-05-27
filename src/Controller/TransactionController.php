<?php
/**
 * Transaction controller.
 */

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Form\Type\TransactionType;
use App\Repository\WalletRepository;
use App\Service\TransactionServiceInterface;
use App\Service\WalletService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class TransactionController.
 */
#[Route('/transaction')]
class TransactionController extends AbstractController
{
    /**
     * Transaction service.
     */
    private TransactionServiceInterface $taskService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * Wallet Repository.
     */
    private WalletRepository $walletRepository;

    /**
     * Constructor.
     *
     * @param TransactionServiceInterface $taskService      Transaction service
     * @param TranslatorInterface         $translator       Translator
     * @param WalletRepository            $walletRepository Wallet Repository
     */
    public function __construct(TransactionServiceInterface $taskService, TranslatorInterface $translator, WalletRepository $walletRepository)
    {
        $this->taskService = $taskService;
        $this->translator = $translator;
        $this->walletRepository = $walletRepository;
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP Request
     *
     * @return Response HTTP response
     */
    #[Route(name: 'transaction_index', methods: 'GET')]
    public function index(Request $request): Response
    {
        $filters = $this->getFilters($request);
        $pagination = $this->taskService->getPaginatedList(
            $request->query->getInt('page', 1),
            $filters
        );

        return $this->render('transaction/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Create action.
     *
     * @param Request       $request       HTTP request
     * @param WalletService $walletService Wallet Service
     * @param Wallet|null   $wallet        Wallet
     *
     * @return Response HTTP response
     */
    #[Route('/create/{wallet?}', name: 'transaction_create', methods: 'GET|POST')]
    public function create(Request $request, WalletService $walletService, Wallet $wallet = null): Response
    {
        $task = new Transaction();

        if (null !== $wallet) {
            $walletEntity = $this->walletRepository->find($wallet);
            if (null !== $walletEntity) {
                $task->setWallet($walletEntity);
            }
        }

        $form = $this->createForm(
            TransactionType::class,
            $task,
            [
                'action' => $this->generateUrl('transaction_create'),
            ]
        );
        $form->handleRequest($request);

        $referer = $request->headers->get('referer');

        if ($form->isSubmitted() && $form->isValid()) {
            // Now that the form is submitted and valid, the Transaction has a Wallet associated
            if (!$walletService->canAcceptTransaction($task->getWallet(), $task->getAmount())) {
                $this->addFlash(
                    'warning',
                    $this->translator->trans('message.transaction_not_possible')
                );

                return $this->render('transaction/create.html.twig', ['form' => $form->createView()]);
            }

            $this->taskService->save($task);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('transaction_index');
        }

        return $this->render(
            'transaction/create.html.twig',
            [
                'form' => $form->createView(),
                'referer' => $referer,
                'wallet_id' => null !== $wallet ? $wallet : null,
            ]
        );
    }

    /**
     * Edit action.
     *
     * @param Request       $request       HTTP request
     * @param Transaction   $task          Transaction entity
     * @param WalletService $walletService WalletService
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'transaction_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    public function edit(Request $request, Transaction $task, WalletService $walletService): Response
    {
        $form = $this->createForm(
            TransactionType::class,
            $task,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('transaction_edit', ['id' => $task->getId()]),
            ]
        );
        $form->handleRequest($request);

        $referer = $request->headers->get('referer');

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if the transaction can be accepted based on wallet's balance
            if (!$walletService->canAcceptTransaction($task->getWallet(), $form->get('amount')->getData(), $task->getAmount())) {
                $this->addFlash(
                    'warning',
                    $this->translator->trans('message.transaction_not_possible')
                );

                return $this->render('transaction/edit.html.twig', ['form' => $form->createView(), 'transaction' => $task]);
            }

            $this->taskService->save($task, $form->get('amount')->getData());

            $this->addFlash(
                'success',
                $this->translator->trans('message.edited_successfully')
            );

            return $this->redirectToRoute('transaction_index');
        }

        return $this->render(
            'transaction/edit.html.twig',
            [
                'form' => $form->createView(),
                'transaction' => $task,
                'referer' => $referer,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request       $request       HTTP request
     * @param Transaction   $task          Transaction entity
     * @param WalletService $walletService Wallet Service
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'transaction_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    public function delete(Request $request, Transaction $task, WalletService $walletService): Response
    {
        $form = $this->createForm(FormType::class, $task, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('transaction_delete', ['id' => $task->getId()]),
        ]);
        $form->handleRequest($request);

        $referer = $request->headers->get('referer');

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$walletService->canAcceptTransaction($task->getWallet(), -$task->getAmount())) {
                $this->addFlash(
                    'warning',
                    $this->translator->trans('message.transaction_not_possible')
                );

                return $this->render('transaction/delete.html.twig', ['form' => $form->createView()]);
            }

            // Reverse the transaction amount in the wallet's balance
            $walletService->updateBalance($task->getWallet(), -$task->getAmount());
            $this->taskService->delete($task);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('transaction_index');
        }

        return $this->render(
            'transaction/delete.html.twig',
            [
                'form' => $form->createView(),
                'transaction' => $task,
                'referer' => $referer,
            ]
        );
    }

    /**
     * Get filters from request.
     *
     * @param Request $request HTTP request
     *
     * @return array<string, int> Array of filters
     *
     * @psalm-return array{category_id: int}
     */
    private function getFilters(Request $request): array
    {
        $filters = [];
        $filters['category_id'] = $request->query->getInt('filters_category_id');

        return $filters;
    }
}

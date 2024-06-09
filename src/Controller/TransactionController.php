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
    private TransactionServiceInterface $transactionService;

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
     * @param TransactionServiceInterface $transactionService      Transaction service
     * @param TranslatorInterface         $translator       Translator
     * @param WalletRepository            $walletRepository Wallet Repository
     */
    public function __construct(TransactionServiceInterface $transactionService, TranslatorInterface $translator, WalletRepository $walletRepository)
    {
        $this->transactionService = $transactionService;
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
        $pagination = $this->transactionService->getPaginatedList(
            $request->query->getInt('page', 1),
            $filters
        );

        return $this->render('transaction/index.html.twig', [
            'pagination' => $pagination,
        ]);
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
        $transaction = new Transaction();

        if (null !== $wallet) {
            $walletEntity = $this->walletRepository->find($wallet);
            if (null !== $walletEntity) {
                $transaction->setWallet($walletEntity);
            }
        }

        $form = $this->createForm(
            TransactionType::class,
            $transaction,
            [
                'action' => $this->generateUrl('transaction_create'),
            ]
        );
        $form->handleRequest($request);

        $referer = $request->headers->get('referer');

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$walletService->canAcceptTransaction($transaction->getWallet(), $transaction->getAmount())) {
                $this->addFlash(
                    'warning',
                    $this->translator->trans('message.transaction_not_possible')
                );

                return $this->render('transaction/create.html.twig', ['form' => $form->createView()]);
            }

            $this->transactionService->save($transaction);

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
     * @param Transaction   $transaction   Transaction entity
     * @param WalletService $walletService WalletService
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/edit', name: 'transaction_edit', requirements: ['id' => '[1-9]\d*'], methods: 'GET|PUT')]
    public function edit(Request $request, Transaction $transaction, WalletService $walletService): Response
    {
        $form = $this->createForm(
            TransactionType::class,
            $transaction,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('transaction_edit', ['id' => $transaction->getId()]),
            ]
        );
        $form->handleRequest($request);

        $referer = $request->headers->get('referer');

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$walletService->canAcceptTransaction($transaction->getWallet(), $form->get('amount')->getData(), $transaction->getAmount())) {
                $this->addFlash(
                    'warning',
                    $this->translator->trans('message.transaction_not_possible')
                );

                return $this->render('transaction/edit.html.twig', ['form' => $form->createView(), 'transaction' => $transaction]);
            }

            $this->transactionService->save($transaction, $form->get('amount')->getData());

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
                'transaction' => $transaction,
                'referer' => $referer,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request       $request       HTTP request
     * @param Transaction   $transaction          Transaction entity
     * @param WalletService $walletService Wallet Service
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'transaction_delete', requirements: ['id' => '[1-9]\d*'], methods: 'GET|DELETE')]
    public function delete(Request $request, Transaction $transaction, WalletService $walletService): Response
    {
        $form = $this->createForm(FormType::class, $transaction, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('transaction_delete', ['id' => $transaction->getId()]),
        ]);
        $form->handleRequest($request);

        $referer = $request->headers->get('referer');

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$walletService->canAcceptTransaction($transaction->getWallet(), -$transaction->getAmount())) {
                $this->addFlash(
                    'warning',
                    $this->translator->trans('message.transaction_not_possible')
                );

                return $this->render('transaction/delete.html.twig', ['form' => $form->createView()]);
            }

            // Reverse the transaction amount in the wallet's balance
            $walletService->updateBalance($transaction->getWallet(), -$transaction->getAmount());
            $this->transactionService->delete($transaction);

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
                'transaction' => $transaction,
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
        $filters['tag_id'] = $request->query->getInt('filters_tag_id');
        return $filters;
    }
}


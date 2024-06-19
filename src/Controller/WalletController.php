<?php
/**
 * Wallet controller.
 */

namespace App\Controller;

use App\Entity\Wallet;
use App\Form\Type\TransactionFilterType;
use App\Form\Type\WalletType;
use App\Repository\TransactionRepository;
use App\Service\WalletServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class WalletController.
 */
#[Route('/wallet')]
class WalletController extends AbstractController
{
    /**
     * Wallet service.
     */
    private WalletServiceInterface $walletService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * Constructor.
     *
     * @param WalletServiceInterface $walletService Wallet service
     * @param TranslatorInterface    $translator    Translator
     */
    public function __construct(WalletServiceInterface $walletService, TranslatorInterface $translator)
    {
        $this->walletService = $walletService;
        $this->translator = $translator;
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP Request
     *
     * @return Response HTTP response
     */
    #[Route(name: 'wallet_index', methods: 'GET')]
    public function index(Request $request): Response
    {
        $pagination = $this->walletService->getPaginatedList(
            $request->query->getInt('page', 1)
        );

        return $this->render('wallet/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Show action.
     *
     * @param Request               $request               HTTP Request
     * @param Wallet                $wallet                Wallet entity
     * @param TransactionRepository $transactionRepository Transaction Repository
     *
     * @return Response HTTP response
     */
    #[Route('/{id}', name: 'wallet_show', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'POST'])]
    public function show(Request $request, Wallet $wallet, TransactionRepository $transactionRepository): Response
    {
        $filterForm = $this->createForm(TransactionFilterType::class);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $dateFrom = $filterForm->get('dateFrom')->getData();
            $dateTo = $filterForm->get('dateTo')->getData();

            $transactions = $transactionRepository->findTransactionsForWalletByDateRange($wallet, $dateFrom, $dateTo);
        } else {
            $transactions = $wallet->getTransactions();
        }

        return $this->render('wallet/show.html.twig', [
            'wallet' => $wallet,
            'filterForm' => $filterForm->createView(),
            'transactions' => $transactions,
        ]);
    }

    /**
     * Create action.
     *
     * @param Request $request Request
     *
     * @return Response HTTP response
     */
    #[Route('/create', name: 'wallet_create', methods: 'GET|POST')]
    public function create(Request $request): Response
    {
        $wallet = new Wallet();
        $form = $this->createForm(WalletType::class, $wallet);
        $form->handleRequest($request);

        $referer = $request->headers->get('referer');

        if ($form->isSubmitted() && $form->isValid()) {
            $this->walletService->save($wallet);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('wallet_index');
        }

        return $this->render(
            'wallet/create.html.twig',
            [
                'form' => $form->createView(),
                'referer' => $referer,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Wallet  $wallet  Wallet
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/delete', name: 'wallet_delete', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'DELETE'])]
    public function delete(Request $request, Wallet $wallet): Response
    {
        if (!$this->walletService->canBeDeleted($wallet)) {
            $this->addFlash(
                'warning',
                $this->translator->trans('message.wallet_contains_tasks')
            );

            return $this->redirectToRoute('wallet_index');
        }

        $form = $this->createForm(
            FormType::class,
            $wallet,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('wallet_delete', ['id' => $wallet->getId()]),
            ]
        );
        $form->handleRequest($request);

        $referer = $request->headers->get('referer');

        if ($form->isSubmitted() && $form->isValid()) {
            $this->walletService->delete($wallet);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('wallet_index');
        }

        return $this->render(
            'wallet/delete.html.twig',
            [
                'form' => $form->createView(),
                'wallet' => $wallet,
                'referer' => $referer,
            ]
        );
    }
}

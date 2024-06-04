<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\Type\TagType;
use App\Repository\TagRepository;
use App\Service\TagServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/tag")
 */
#[Route('/tag')]
#[IsGranted('ROLE_ADMIN')]
class TagController extends AbstractController
{
    private TagServiceInterface $TagService;
    private TranslatorInterface $translator;

    /**
     * Constructor.
     *
     * @param TagServiceInterface $TagService Tag service
     * @param TranslatorInterface $translator Translator service
     */
    public function __construct(TagServiceInterface $TagService, TranslatorInterface $translator)
    {
        $this->TagService = $TagService;
        $this->translator = $translator;
    }

    /**
     * @Route("/", name="tag_index", methods={"GET"})
     *
     * @param TagRepository $TagRepository
     * @return Response
     */
    #[Route('/', name: 'tag_index', methods: ['GET'])]
    public function index(TagRepository $TagRepository): Response
    {
        return $this->render('tag/index.html.twig', [
            'tags' => $TagRepository->findAll(),
        ]);
    }

    /**
     * @Route("/create", name="tag_create", methods={"GET", "POST"})
     *
     * @param Request $request
     * @return Response
     */
    #[Route('/create', name: 'tag_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->TagService->createTag($tag);

            return $this->redirectToRoute('tag_index');
        }

        return $this->render('tag/create.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="tag_show", methods={"GET"})
     *
     * @param Tag $tag
     * @return Response
     */
    #[Route('/{id}', name: 'tag_show', methods: ['GET'])]
    public function show(Tag $tag): Response
    {
        return $this->render('tag/show.html.twig', [
            'tag' => $tag,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="tag_edit", methods={"GET", "POST"})
     *
     * @param Request $request
     * @param Tag $tag
     * @return Response
     */
    #[Route('/{id}/edit', name: 'tag_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tag $tag): Response
    {
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->TagService->updateTag($tag);

            return $this->redirectToRoute('tag_index');
        }

        return $this->render('tag/edit.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="tag_confirm_delete", methods={"GET"})
     *
     * @param Tag $tag
     * @return Response
     * @param Request $request
     */
    #[Route('/{id}/delete', name: 'tag_confirm_delete', requirements: ['id' => '[1-9]\d*'], methods: ['GET'])]
    public function confirmDelete(Request $request, Tag $tag): Response
    {
        $form = $this->createForm(
            FormType::class,
            $tag,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('tag_delete', ['id' => $tag->getId()]),
            ]
        );

        return $this->render('tag/delete.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="tag_delete", methods={"POST"})
     *
     * @param Request $request
     * @param Tag $tag
     * @return Response
     */
    #[Route('/{id}/delete', name: 'tag_delete', requirements: ['id' => '[1-9]\d*'], methods: ['DELETE'])]
    public function delete(Request $request, Tag $tag): Response
    {
        $form = $this->createForm(
            FormType::class,
            $tag,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl('tag_delete', ['id' => $tag->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->TagService->deleteTag($tag);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('tag_index');
        }

        return $this->render(
            'tag/delete.html.twig',
            [
                'form' => $form->createView(),
                'tag' => $tag,
            ]
        );
    }
}
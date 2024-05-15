<?php
/**
 * CategoryController
 */

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class CategoryController.
 */
#[Route('/category')]
class CategoryController extends AbstractController
{
    /**
     * Index action.
     *
     * @param Request              $request            HTTP Request
     * @param CategoryRepository   $categoryRepository Category repository
     * @param PaginatorInterface   $paginator          Paginator
     *
     * @return Response HTTP response
     */
    #[Route(name: 'category_index', methods: ['GET'])]
    public function index(Request $request, CategoryRepository $categoryRepository, PaginatorInterface $paginator): Response
    {
        // Pobierz wszystkie kategorie z bazy danych
        $categories = $categoryRepository->findAll();

        // Utwórz paginację na podstawie wszystkich kategorii
        $pagination = $paginator->paginate(
            $categories,
            $request->query->getInt('page', 1),
            CategoryRepository::PAGINATOR_ITEMS_PER_PAGE
        );

        // Przekazuj listę kategorii do szablonu Twig
        return $this->render('category/index.html.twig', [
            'categories' => $pagination // Tutaj poprawiliśmy nazwę zmiennej na 'categories'
        ]);
    }

    /**
     * Show action.
     *
     * @param Category $category Category entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'category_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET']
    )]
    public function show(Category $category): Response
    {
        return $this->render(
            'category/show.html.twig',
            ['category' => $category]
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\PostSort;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Support\Pagination;
use Smarty;

class CategoryController
{
    public function __construct(
        private readonly Smarty             $smarty,
        private readonly CategoryRepository $categoryRepo,
        private readonly PostRepository     $postRepo,
        private readonly int                $perPage,
    ) {
    }

    public function show(int $categoryId): void
    {
        $category = $this->categoryRepo->findById($categoryId);
        if ($category === null) {
            http_response_code(404);
            $this->smarty->assign('pageTitle', 'Категория не найдена');
            $this->smarty->display('not_found.tpl');
            return;
        }

        $sort = PostSort::fromRequestValue($_GET['sort'] ?? null);

        $rawPage = $_GET['page'] ?? 1;
        $page    = (is_scalar($rawPage) && is_numeric($rawPage)) ? (int) $rawPage : 1;
        if ($page < 1) {
            $page = 1;
        }

        $total      = $this->postRepo->countByCategory($categoryId);
        $totalPages = max(1, (int) ceil($total / $this->perPage));

        if ($page > $totalPages) {
            http_response_code(404);
            $this->smarty->assign('pageTitle', 'Страница не найдена');
            $this->smarty->display('not_found.tpl');
            return;
        }

        $posts      = $this->postRepo->findByCategory($categoryId, $sort, $page, $this->perPage);
        $pagination = Pagination::build($page, $totalPages, "/category/{$categoryId}", $sort->value);

        $this->smarty->assign('pageTitle',  $category['name']);
        $this->smarty->assign('category',   $category);
        $this->smarty->assign('posts',      $posts);
        $this->smarty->assign('sort',       $sort->value);
        $this->smarty->assign('page',       $page);
        $this->smarty->assign('totalPages', $totalPages);
        $this->smarty->assign('total',      $total);
        $this->smarty->assign('pagination', $pagination);
        $this->smarty->display('category.tpl');
    }
}
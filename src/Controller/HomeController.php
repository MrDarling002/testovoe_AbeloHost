<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PostRepository;
use Smarty;

class HomeController
{
    public function __construct(
        private readonly Smarty         $smarty,
        private readonly PostRepository $postRepo,
    ) {
    }

    public function index(): void
    {
        $categories = $this->postRepo->findLatestGroupedByCategory(3);

        $this->smarty->assign('pageTitle',  'Главная');
        $this->smarty->assign('categories', $categories);
        $this->smarty->display('home.tpl');
    }
}
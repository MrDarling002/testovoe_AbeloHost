<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PostRepository;
use Smarty;

class PostController
{
    public function __construct(
        private readonly Smarty         $smarty,
        private readonly PostRepository $postRepo,
    ) {
    }

    public function show(int $postId): void
    {
        $post = $this->postRepo->findPublishedById($postId);
        if ($post === null) {
            http_response_code(404);
            $this->smarty->assign('pageTitle', 'Статья не найдена');
            $this->smarty->display('not_found.tpl');
            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
            $this->postRepo->incrementViews($postId);
            $post['views_count']++;
        }

        $categories = $this->postRepo->findCategoriesByPostId($postId);
        $similar    = $this->postRepo->findSimilar($postId, 3);

        $title = $post['title'];
        $post['breadcrumb_title'] = mb_strlen($title, 'UTF-8') > 40
            ? rtrim(mb_substr($title, 0, 39, 'UTF-8')) . '…'
            : $title;

        $this->smarty->assign('pageTitle',  $post['title']);
        $this->smarty->assign('post',       $post);
        $this->smarty->assign('categories', $categories);
        $this->smarty->assign('similar',    $similar);
        $this->smarty->display('post.tpl');
    }
}
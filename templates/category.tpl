{extends file="layouts/main.tpl"}

{block name="content"}
    <nav class="breadcrumbs">
        <a href="/">Главная</a> &rarr; <span>{$category.name}</span>
    </nav>
    <h1 class="page-title">{$category.name}</h1>
    {if $category.description}
        <p class="category-desc">{$category.description}</p>
    {/if}
    <div class="sort-bar">
        <span>Сортировка:</span>
        <a href="/category/{$category.id}?sort=date&page=1"
           class="sort-bar__link {if $sort === 'date'}sort-bar__link--active{/if}">По дате</a>
        <a href="/category/{$category.id}?sort=views&page=1"
           class="sort-bar__link {if $sort === 'views'}sort-bar__link--active{/if}">По просмотрам</a>
    </div>
    {if empty($posts)}
        <p class="empty-state">В этой категории пока нет опубликованных статей.</p>
    {else}
        <div class="post-grid">
            {foreach $posts as $post}
                {include file="partials/post_card.tpl" post=$post}
            {/foreach}
        </div>
        <div class="pagination-info">
            Страница {$page} из {$totalPages} (всего статей: {$total})
        </div>
        {include file="partials/pagination.tpl" pagination=$pagination}
    {/if}
{/block}
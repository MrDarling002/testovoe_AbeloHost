{extends file="layouts/main.tpl"}

{block name="content"}
    <h1 class="page-title">Последние статьи по категориям</h1>
    {if empty($categories)}
        <p class="empty-state">Пока нет опубликованных статей.</p>
    {else}
        {foreach $categories as $item}
            <section class="category-section">
                <div class="category-section__header">
                    <div>
                        <h2 class="category-section__title">
                            <a href="/category/{$item.category.id}">{$item.category.name}</a>
                        </h2>
                        {if $item.category.description}
                            <p class="category-section__desc">{$item.category.description}</p>
                        {/if}
                    </div>
                    <a href="/category/{$item.category.id}" class="btn btn--outline">Все статьи</a>
                </div>
                <div class="post-grid">
                    {foreach $item.posts as $post}
                        {include file="partials/post_card.tpl" post=$post}
                    {/foreach}
                </div>
            </section>
        {/foreach}
    {/if}
{/block}
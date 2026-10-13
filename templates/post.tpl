{extends file="layouts/main.tpl"}

{block name="content"}
    <nav class="breadcrumbs">
        <a href="/">Главная</a>
        {if !empty($categories)}
            &rarr; <a href="/category/{$categories[0].id}">{$categories[0].name}</a>
        {/if}
        &rarr; <span>{$post.breadcrumb_title}</span>
    </nav>
    <article class="post-full">
        <h1 class="post-full__title">{$post.title}</h1>
        <div class="post-full__meta">
            <time datetime="{$post.published_at_iso}">{$post.published_at_display}</time>
            <span class="post-full__views">👁 {$post.views_count}</span>
        </div>
        {if $post.image}
            <div class="post-full__image">
                <img src="{$post.image}" alt="{$post.title}" width="800" height="450">
            </div>
        {/if}
        {if $post.description}
            <p class="post-full__lead">{$post.description}</p>
        {/if}
        <div class="post-full__content">
            {$post.content|escape:'html'|nl2br nofilter}
        </div>
        {if !empty($categories)}
            <div class="post-full__categories">
                <span>Категории:</span>
                {foreach $categories as $cat}
                    <a href="/category/{$cat.id}" class="tag">{$cat.name}</a>
                {/foreach}
            </div>
        {/if}
    </article>
    <section class="similar-section">
        <h2 class="similar-section__title">Похожие статьи</h2>
        {if empty($similar)}
            <p class="empty-state">Похожих статей пока нет.</p>
        {else}
            <div class="post-grid">
                {foreach $similar as $sp}
                    {include file="partials/post_card.tpl" post=$sp}
                {/foreach}
            </div>
        {/if}
    </section>
{/block}
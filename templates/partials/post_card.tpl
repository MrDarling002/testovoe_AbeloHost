<article class="post-card">
    {if $post.image}
        <div class="post-card__image">
            <img src="{$post.image}" alt="{$post.title}" loading="lazy" width="400" height="225">
        </div>
    {/if}
    <div class="post-card__body">
        <h3 class="post-card__title">
            <a href="/post/{$post.id}">{$post.title}</a>
        </h3>
        {if $post.description}
            <p class="post-card__desc">{$post.description}</p>
        {/if}
        <div class="post-card__meta">
            <time datetime="{$post.published_at_iso}">{$post.published_at_date}</time>
            <span class="post-card__views">👁 {$post.views_count}</span>
        </div>
    </div>
</article>
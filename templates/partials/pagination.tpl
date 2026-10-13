{if !empty($pagination)}
<nav class="pagination" aria-label="Навигация по страницам">
    {foreach $pagination as $item}
        {if $item.active}
            <span class="pagination__link pagination__link--active" aria-current="page">{$item.label}</span>
        {elseif $item.url}
            <a class="pagination__link" href="{$item.url}">{$item.label}</a>
        {else}
            <span class="pagination__ellipsis">{$item.label}</span>
        {/if}
    {/foreach}
</nav>
{/if}
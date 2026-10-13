<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$pageTitle|default:'Блог'} — Мой Блог</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a href="/" class="site-header__logo">Мой Блог</a>
            <nav class="site-header__nav">
                <a href="/">Главная</a>
            </nav>
        </div>
    </header>
    <main class="site-main">
        <div class="container">
            {block name="content"}{/block}
        </div>
    </main>
    <footer class="site-footer">
        <div class="container">
            <p>&copy; {$smarty.now|date_format:"%Y"} Мой Блог</p>
        </div>
    </footer>
</body>
</html>
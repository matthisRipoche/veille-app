<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="color-scheme" content="light dark">
  <title>{{ $title ?? 'Accueil' }} · Veille</title>
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<header class="site-header">
  <div class="wrap header-inner">
    <a class="brand" href="{{ route('home') }}">Veille<span class="brand-dot">.</span></a>
    <nav aria-label="Navigation principale">
      <a href="{{ route('home') }}">Accueil</a>
      <a href="{{ route('reports.index') }}">Tous les rapports</a>
    </nav>
  </div>
</header>
<main class="wrap">
@yield('content')
</main>
<footer class="site-footer">
  <div class="wrap">Bilans quotidiens de veille technologique</div>
</footer>
</body>
</html>

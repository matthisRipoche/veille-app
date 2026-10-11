@extends('layouts.app', ['title' => $report->title])

@section('content')
<p class="back"><a href="{{ route('reports.index') }}">← Tous les rapports</a></p>
<article class="report">
  <header class="report-header">
    <time class="card-date" datetime="{{ $report->slug() }}">{{ $report->date->translatedFormat('l j F Y') }}</time>
    <h1>{{ $report->title }}</h1>
    @if ($report->summary !== '')<p class="lead">{{ $report->summary }}</p>@endif
  </header>
  <div class="prose">{!! $html !!}</div>
</article>
<nav class="pagination" aria-label="Rapports voisins">
  @if ($prev)
    <a class="button" rel="prev" href="{{ route('reports.show', $prev->slug()) }}">← {{ $prev->date->translatedFormat('D j M') }}</a>
  @else
    <span class="button disabled" aria-disabled="true">←</span>
  @endif
  @if ($next)
    <a class="button" rel="next" href="{{ route('reports.show', $next->slug()) }}">{{ $next->date->translatedFormat('D j M') }} →</a>
  @else
    <span class="button disabled" aria-disabled="true">→</span>
  @endif
</nav>
@endsection

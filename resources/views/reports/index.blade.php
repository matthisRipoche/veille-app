@extends('layouts.app', ['title' => 'Tous les rapports'])

@section('content')
<h1>Tous les rapports</h1>
<p class="muted">{{ $total }} rapport{{ $total > 1 ? 's' : '' }}, du plus récent au plus ancien</p>
@if ($reports->isEmpty())
  <p class="empty">Aucun rapport pour le moment.</p>
@else
  <ul class="rows">
    @foreach ($reports as $report)
      <li class="row">
        <a class="row-link" href="{{ route('reports.show', $report->slug()) }}">
          <time class="row-date" datetime="{{ $report->slug() }}">{{ $report->date->translatedFormat('l j F Y') }}</time>
          <span class="row-title">{{ $report->title }}</span>
          @if ($report->summary !== '')<span class="row-summary">{{ $report->summary }}</span>@endif
        </a>
      </li>
    @endforeach
  </ul>
  @if ($reports->lastPage() > 1)
    <nav class="pagination" aria-label="Pagination">
      @if ($reports->currentPage() > 1)
        <a class="button" rel="prev" href="{{ $reports->previousPageUrl() }}">← Plus récents</a>
      @else
        <span class="button disabled" aria-disabled="true">← Plus récents</span>
      @endif
      <span class="page-info">Page {{ $reports->currentPage() }} sur {{ $reports->lastPage() }}</span>
      @if ($reports->hasMorePages())
        <a class="button" rel="next" href="{{ $reports->nextPageUrl() }}">Plus anciens →</a>
      @else
        <span class="button disabled" aria-disabled="true">Plus anciens →</span>
      @endif
    </nav>
  @endif
@endif
@endsection

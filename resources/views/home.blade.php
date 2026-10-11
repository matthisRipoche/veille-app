@extends('layouts.app', ['title' => 'Accueil'])

@section('content')
<h1>Les 7 derniers jours</h1>
@if ($reports->isEmpty())
  <p class="empty">Aucun rapport pour le moment. Le premier arrivera après la prochaine exécution de la veille.</p>
@else
  <ul class="cards">
    @foreach ($reports as $report)
      <li class="card{{ $loop->first ? ' card-latest' : '' }}">
        <a class="card-link" href="{{ route('reports.show', $report->slug()) }}">
          <time class="card-date" datetime="{{ $report->slug() }}">{{ $report->date->translatedFormat('l j F Y') }}</time>
          <h2 class="card-title">{{ $report->title }}</h2>
          @if ($report->summary !== '')<p class="card-summary">{{ $report->summary }}</p>@endif
        </a>
      </li>
    @endforeach
  </ul>
  @if ($total > $reports->count())
    <p class="more"><a class="button" href="{{ route('reports.index') }}">Voir tous les rapports ({{ $total }})</a></p>
  @endif
@endif
@endsection

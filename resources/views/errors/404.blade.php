@extends('layouts.app', ['title' => 'Introuvable'])

@section('content')
<h1>Page introuvable</h1>
<p class="empty">Cette page n'existe pas.</p>
<p><a class="button" href="{{ route('home') }}">Retour à l'accueil</a></p>
@endsection

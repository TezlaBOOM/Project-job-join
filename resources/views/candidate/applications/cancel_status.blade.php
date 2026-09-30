@extends('layouts.app')

@section('title', $title)

@section('content')
<div style="max-width: 580px; margin: 3rem auto; text-align: center;">
    <div class="card" style="padding: 2.5rem 2rem;">
        <div style="font-size: 2.5rem; margin-bottom: 1rem;" aria-hidden="true">
            @if($type === 'success')
                ✅
            @elseif($type === 'already_cancelled')
                ℹ️
            @else
                ⚠️
            @endif
        </div>

        <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--color-text); margin-bottom: 1rem;">
            {{ $title }}
        </h1>

        <p style="font-size: 1.05rem; color: var(--color-text-muted); line-height: 1.6; margin-bottom: 2rem;">
            {{ $message }}
        </p>

        <div>
            <a href="{{ route('public.offers.index') }}" class="btn btn-primary">
                Przejdź do listy ofert pracy
            </a>
        </div>
    </div>
</div>
@endsection

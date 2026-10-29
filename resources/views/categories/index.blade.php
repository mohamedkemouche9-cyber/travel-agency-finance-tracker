{{--
    View: category management.
    Global default categories are read-only; the user's own categories can
    be added, edited (inline) and deleted. Receives $categories.
--}}
@extends('layouts.app')

@php
    $own = $categories->filter(fn ($c) => $c->user_id !== null);
    $globals = $categories->filter(fn ($c) => $c->user_id === null);
@endphp

@section('content')
<div class="page-head">
    <div>
        <h1>Categories</h1>
        <p class="page-sub">Organise your transactions with default and personal categories.</p>
    </div>
</div>

{{-- =========================== ADD NEW =============================== --}}
<div class="card">
    <div class="card-head"><h3>Add a category</h3></div>
    <form method="post" action="{{ route('categories.add') }}" class="add-cat-form" data-validate novalidate>
        @csrf
        <div class="add-cat-grid">
            <input class="input" type="text" name="name" placeholder="Category name (e.g. Pets)"
                   required minlength="2" maxlength="40">
            <input class="color-input" type="color" name="color" value="#6366f1"
                   title="Pick a colour" aria-label="Category colour">
            <button class="btn btn-primary" type="submit">Add category</button>
        </div>
    </form>
</div>

<div class="grid-2">
    {{-- ========================= YOUR OWN ============================= --}}
    <div class="card">
        <div class="card-head">
            <h3>Your categories</h3>
            <span class="card-note">{{ $own->count() }} item(s)</span>
        </div>

        @if ($own->isNotEmpty())
            <ul class="cat-list">
                @foreach ($own as $c)
                    <li class="cat-row" id="cat-{{ $c->category_id }}">
                        <div class="cat-main">
                            <span class="cat-pill" style="--c:{{ $c->color }}">{{ $c->name }}</span>
                            <span class="muted">{{ $c->tx_count }} transaction(s)</span>
                            <span class="cat-actions">
                                <button class="icon-btn" type="button" data-toggle-edit="{{ $c->category_id }}" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                </button>
                                <form method="post" action="{{ route('categories.delete', $c->category_id) }}"
                                      class="inline-form js-confirm"
                                      data-confirm="Delete category '{{ $c->name }}'?">
                                    @csrf
                                    <button class="icon-btn danger" type="submit" title="Delete">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            </span>
                        </div>

                        {{-- Inline edit form (hidden until the pencil is clicked) --}}
                        <form method="post" action="{{ route('categories.update', $c->category_id) }}"
                              class="cat-edit" data-edit-form="{{ $c->category_id }}" hidden>
                            @csrf
                            <input class="input" type="text" name="name" value="{{ $c->name }}"
                                   required minlength="2" maxlength="40">
                            <input class="color-input" type="color" name="color" value="{{ $c->color }}">
                            <button class="btn btn-primary btn-sm" type="submit">Save</button>
                            <button class="btn btn-ghost btn-sm" type="button" data-toggle-edit="{{ $c->category_id }}">Cancel</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="muted">You have not created any personal categories yet.
               Use the form above - for example "Gym", "Pets" or "Side project".</p>
        @endif
    </div>

    {{-- ========================== DEFAULTS ============================ --}}
    <div class="card">
        <div class="card-head">
            <h3>Default categories</h3>
            <span class="card-note">available to everyone · read-only</span>
        </div>
        <ul class="cat-list">
            @foreach ($globals as $c)
                <li class="cat-row">
                    <div class="cat-main">
                        <span class="cat-pill" style="--c:{{ $c->color }}">{{ $c->name }}</span>
                        <span class="muted">{{ $c->tx_count }} transaction(s)</span>
                        <span class="cat-actions">
                            <svg class="lock-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection

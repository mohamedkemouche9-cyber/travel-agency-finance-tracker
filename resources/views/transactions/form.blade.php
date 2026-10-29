{{--
    View: add/edit transaction form (shared).
    Receives $mode ('add'|'edit'), $tx (model or null), $categories.
--}}
@extends('layouts.app')

@php
    // Current value resolution: submitted value -> existing record -> default
    $valType     = old('type',             $tx->type             ?? 'expense');
    $valAmount   = old('amount',           $tx->amount           ?? '');
    $valDate     = old('transaction_date', $tx->transaction_date ?? date('Y-m-d'));
    $valCategory = old('category_id',      $tx->category_id      ?? '');
    $valDesc     = old('description',      $tx->description      ?? '');

    $action = $mode === 'edit'
        ? route('transactions.edit', $tx->transaction_id)
        : route('transactions.add');
@endphp

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $mode === 'edit' ? 'Edit transaction' : 'Add transaction' }}</h1>
        <p class="page-sub">{{ $mode === 'edit' ? 'Update the details below.' : 'Record a new income or expense.' }}</p>
    </div>
    <a class="btn btn-ghost" href="{{ route('transactions') }}">← Back to list</a>
</div>

<div class="card form-card">
    <form method="post" action="{{ $action }}" data-validate novalidate>
        @csrf

        {{-- Type: income / expense segmented control --}}
        <div class="form-group{{ $errors->has('type') ? ' has-error' : '' }}">
            <label>Type</label>
            <div class="segment" role="radiogroup" aria-label="Transaction type">
                <label class="segment-opt seg-expense {{ $valType === 'expense' ? 'on' : '' }}">
                    <input type="radio" name="type" value="expense" {{ $valType === 'expense' ? 'checked' : '' }}>
                    Expense
                </label>
                <label class="segment-opt seg-income {{ $valType === 'income' ? 'on' : '' }}">
                    <input type="radio" name="type" value="income" {{ $valType === 'income' ? 'checked' : '' }}>
                    Income
                </label>
            </div>
            @error('type')<span class="field-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-row">
            <div class="form-group{{ $errors->has('amount') ? ' has-error' : '' }}">
                <label for="amount">Amount</label>
                <div class="input-wrap">
                    <span class="input-prefix">{{ auth()->user()->currency }}</span>
                    <input class="input with-prefix" type="number" id="amount" name="amount"
                           value="{{ $valAmount }}" step="0.01" min="0.01" max="999999.99"
                           placeholder="0.00" required>
                </div>
                @error('amount')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group{{ $errors->has('transaction_date') ? ' has-error' : '' }}">
                <label for="transaction_date">Date</label>
                <input class="input" type="date" id="transaction_date" name="transaction_date"
                       value="{{ $valDate }}" max="{{ date('Y-m-d') }}" required>
                @error('transaction_date')<span class="field-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-group{{ $errors->has('category_id') ? ' has-error' : '' }}">
            <label for="category_id">Category</label>
            <select class="input" id="category_id" name="category_id" required>
                <option value="" disabled {{ $valCategory === '' ? 'selected' : '' }}>Choose a category...</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->category_id }}"
                        {{ (string) $c->category_id === (string) $valCategory ? 'selected' : '' }}>
                        {{ $c->name }}{{ $c->user_id === null ? '' : ' (yours)' }}
                    </option>
                @endforeach
            </select>
            @error('category_id')<span class="field-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
            <label for="description">Description <span class="muted">(optional)</span></label>
            <input class="input" type="text" id="description" name="description"
                   value="{{ $valDesc }}" maxlength="255"
                   placeholder="e.g. Weekly groceries at Tesco">
            @error('description')<span class="field-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                {{ $mode === 'edit' ? 'Save changes' : 'Add transaction' }}
            </button>
            <a class="btn btn-ghost" href="{{ route('transactions') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection

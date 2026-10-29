{{--
    View: transactions list - filters, totals, table, pagination.
    Receives $rows (paginator), $sumIncome, $sumExpense, $filters, $categories.
--}}
@extends('layouts.app')

@php
    $exportUrl = route('transactions.export').'?'.http_build_query(array_filter($filters));
@endphp

@section('content')
<div class="page-head">
    <div>
        <h1>Transactions</h1>
        <p class="page-sub">{{ $rows->total() }} record(s) match your filter</p>
    </div>
    <div class="head-actions">
        <a class="btn btn-ghost" href="{{ $exportUrl }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-ico"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export CSV
        </a>
        <a class="btn btn-primary" href="{{ route('transactions.add') }}">+ Add transaction</a>
    </div>
</div>

{{-- ============================== FILTERS ============================= --}}
<form class="card filter-bar" method="get" action="{{ route('transactions') }}">
    <div class="filter-grid">
        <label>
            <span>Search</span>
            <input class="input" type="text" name="q" value="{{ $filters['q'] }}"
                   placeholder="Description or category...">
        </label>
        <label>
            <span>Type</span>
            <select class="input" name="type">
                <option value="">All</option>
                <option value="income"  {{ $filters['type'] === 'income' ? 'selected' : '' }}>Income</option>
                <option value="expense" {{ $filters['type'] === 'expense' ? 'selected' : '' }}>Expense</option>
            </select>
        </label>
        <label>
            <span>Category</span>
            <select class="input" name="category_id">
                <option value="">All</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->category_id }}"
                        {{ (string) $c->category_id === (string) $filters['category_id'] ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </label>
        <label>
            <span>From</span>
            <input class="input" type="date" name="date_from" value="{{ $filters['date_from'] }}">
        </label>
        <label>
            <span>To</span>
            <input class="input" type="date" name="date_to" value="{{ $filters['date_to'] }}">
        </label>
        <div class="filter-actions">
            <button class="btn btn-primary" type="submit">Apply</button>
            <a class="btn btn-ghost" href="{{ route('transactions') }}">Clear</a>
        </div>
    </div>
</form>

{{-- =============================== TABLE ============================== --}}
<div class="card table-card">
    <div class="totals-strip">
        <span>Income: <strong class="pos">{{ money($sumIncome) }}</strong></span>
        <span>Expenses: <strong class="neg">{{ money($sumExpense) }}</strong></span>
        <span>Net: <strong class="{{ $sumIncome - $sumExpense < 0 ? 'neg' : 'pos' }}">
            {{ money($sumIncome - $sumExpense) }}</strong></span>
    </div>

    @if ($rows->isNotEmpty())
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th class="num">Amount</th>
                        <th class="actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $t)
                        <tr>
                            <td class="nowrap">{{ nice_date($t->transaction_date) }}</td>
                            <td class="desc-cell">{{ $t->description ?: '—' }}</td>
                            <td><span class="cat-pill" style="--c:{{ $t->category->color }}">{{ $t->category->name }}</span></td>
                            <td>
                                <span class="type-badge {{ $t->type === 'income' ? 'type-income' : 'type-expense' }}">
                                    {{ $t->type === 'income' ? 'Income' : 'Expense' }}
                                </span>
                            </td>
                            <td class="num {{ $t->type === 'income' ? 'pos' : 'neg' }}">
                                {{ $t->type === 'income' ? '+' : '-' }}{{ money((float) $t->amount) }}
                            </td>
                            <td class="actions-col">
                                <a class="icon-btn" href="{{ route('transactions.edit', $t->transaction_id) }}" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                </a>
                                <form method="post" action="{{ route('transactions.delete', $t->transaction_id) }}"
                                      class="inline-form js-confirm"
                                      data-confirm="Delete this {{ $t->type }} of {{ money((float) $t->amount) }}?">
                                    @csrf
                                    <button class="icon-btn danger" type="submit" title="Delete">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($rows->hasPages())
            <nav class="pagination" aria-label="Pagination">
                @if ($rows->onFirstPage())
                @else
                    <a class="btn btn-ghost btn-sm" href="{{ $rows->previousPageUrl() }}">← Previous</a>
                @endif
                <span class="page-info">Page {{ $rows->currentPage() }} of {{ $rows->lastPage() }}</span>
                @if ($rows->hasMorePages())
                    <a class="btn btn-ghost btn-sm" href="{{ $rows->nextPageUrl() }}">Next →</a>
                @endif
            </nav>
        @endif

    @else
        <div class="empty-state">
            <h3>No transactions found</h3>
            <p>Try different filters, or add your first transaction.</p>
            <a class="btn btn-primary" href="{{ route('transactions.add') }}">+ Add transaction</a>
        </div>
    @endif
</div>
@endsection

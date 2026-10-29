{{--
    View: monthly budgets with progress bars.
    Receives $rows, $month (YYYY-MM), $monthLabel, $prevMonth, $nextMonth,
    $totalBudget, $totalSpent.
--}}
@extends('layouts.app')

@php $remaining = $totalBudget - $totalSpent; @endphp

@section('content')
<div class="page-head">
    <div>
        <h1>Budgets</h1>
        <p class="page-sub">Set a monthly limit per category and track how you are doing.</p>
    </div>
    <div class="month-nav">
        <a class="btn btn-ghost btn-sm" href="{{ route('budgets', ['m' => $prevMonth]) }}">←</a>
        <span class="month-label">{{ $monthLabel }}</span>
        <a class="btn btn-ghost btn-sm" href="{{ route('budgets', ['m' => $nextMonth]) }}">→</a>
    </div>
</div>

@if ($totalBudget > 0)
    <section class="stat-grid stat-grid-3">
        <div class="stat-card">
            <div><span class="stat-label">Total budgeted</span>
                 <span class="stat-value">{{ money($totalBudget) }}</span></div>
        </div>
        <div class="stat-card">
            <div><span class="stat-label">Spent (budgeted categories)</span>
                 <span class="stat-value neg">{{ money($totalSpent) }}</span></div>
        </div>
        <div class="stat-card">
            <div><span class="stat-label">Remaining</span>
                 <span class="stat-value {{ $remaining < 0 ? 'neg' : 'pos' }}">{{ money($remaining) }}</span></div>
        </div>
    </section>
@endif

<div class="card">
    <div class="card-head">
        <h3>Category budgets - {{ $monthLabel }}</h3>
        <span class="card-note">leave a field empty (or 0) for no budget</span>
    </div>

    <form method="post" action="{{ route('budgets.save') }}">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">

        <div class="budget-list">
            @foreach ($rows as $r)
                @php
                    $budget = $r->budget_amount !== null ? (float) $r->budget_amount : null;
                    $spent = (float) $r->spent;
                    $ratio = ($budget && $budget > 0) ? $spent / $budget : null;
                @endphp
                <div class="budget-row">
                    <span class="cat-pill" style="--c:{{ $r->color }}">{{ $r->name }}</span>

                    <div class="budget-progress">
                        @if ($ratio !== null)
                            <div class="progress">
                                <span class="progress-fill {{ $ratio > 1 ? 'over' : ($ratio >= 0.8 ? 'warn' : '') }}"
                                      style="width: {{ min(100, round($ratio * 100)) }}%"></span>
                            </div>
                            <span class="budget-note {{ $ratio > 1 ? 'neg' : '' }}">
                                {{ money($spent) }} of {{ money($budget) }}
                                ({{ round($ratio * 100) }}%)
                            </span>
                        @elseif ($spent > 0)
                            <span class="budget-note muted">{{ money($spent) }} spent · no budget set</span>
                        @else
                            <span class="budget-note muted">no activity</span>
                        @endif
                    </div>

                    <div class="input-wrap budget-input">
                        <span class="input-prefix">{{ auth()->user()->currency }}</span>
                        <input class="input with-prefix" type="number"
                               name="amounts[{{ $r->category_id }}]"
                               value="{{ $budget !== null ? number_format($budget, 2, '.', '') : '' }}"
                               step="0.01" min="0" max="999999.99" placeholder="—">
                    </div>
                </div>
            @endforeach
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save budgets</button>
        </div>
    </form>
</div>
@endsection

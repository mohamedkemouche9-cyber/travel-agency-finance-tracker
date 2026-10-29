{{--
    View: dashboard - stat cards, charts, forecast + what-if simulator,
    insights, budget alerts and recent transactions.
    Receives $summary, $insights, $recent, $alerts, $monthName.
--}}
@extends('layouts.app')

@php
    $income = (float) $summary['month_income'];
    $expense = (float) $summary['month_expense'];
    $rate = $income > 0 ? max(0, ($income - $expense) / $income * 100) : 0;
@endphp

@section('content')
<div class="page-head">
    <div>
        <h1>Dashboard</h1>
        <p class="page-sub">Your money at a glance - {{ $monthName }}</p>
    </div>
    <a class="btn btn-primary" href="{{ route('transactions.add') }}">+ Add transaction</a>
</div>

{{-- ============================ STAT CARDS ============================ --}}
<section class="stat-grid">
    <div class="stat-card">
        <span class="stat-icon icon-balance">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
        </span>
        <div>
            <span class="stat-label">Total balance</span>
            <span class="stat-value {{ $summary['balance'] < 0 ? 'neg' : '' }}">{{ money($summary['balance']) }}</span>
        </div>
    </div>

    <div class="stat-card">
        <span class="stat-icon icon-income">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
        </span>
        <div>
            <span class="stat-label">Income · this month</span>
            <span class="stat-value pos">{{ money($income) }}</span>
        </div>
    </div>

    <div class="stat-card">
        <span class="stat-icon icon-expense">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 17 13.5 8.5 8.5 13.5 2 7"/><polyline points="16 17 22 17 22 11"/></svg>
        </span>
        <div>
            <span class="stat-label">Expenses · this month</span>
            <span class="stat-value neg">{{ money($expense) }}</span>
        </div>
    </div>

    <div class="stat-card">
        <span class="stat-icon icon-rate">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
        </span>
        <div>
            <span class="stat-label">Savings rate</span>
            <span class="stat-value">{{ number_format($rate, 1) }}%</span>
        </div>
    </div>
</section>

@if ($recent->isEmpty())
    {{-- Friendly empty state for brand-new accounts --}}
    <section class="card empty-state">
        <h3>Let's get started!</h3>
        <p>You have no transactions yet. Add your first income or expense and
           the dashboard will come to life with charts and insights.</p>
        <a class="btn btn-primary" href="{{ route('transactions.add') }}">Add your first transaction</a>
    </section>
@endif

{{-- ============================== CHARTS ============================== --}}
<section class="grid-2">
    <div class="card chart-card">
        <div class="card-head">
            <h3>Cash flow</h3>
            <span class="card-note">income vs expenses, by month</span>
        </div>
        <div class="chart-box"><canvas id="cashflowChart" height="230"></canvas></div>
    </div>

    <div class="card chart-card">
        <div class="card-head">
            <h3>Spending by category</h3>
            <span class="card-note">{{ $monthName }}</span>
        </div>
        <div class="chart-box doughnut-box"><canvas id="categoryChart" height="230"></canvas></div>
        <p class="chart-empty" id="categoryEmpty" hidden>No expenses recorded this month yet.</p>
    </div>
</section>

{{-- ====================== FORECAST + WHAT-IF ========================= --}}
<section class="card chart-card forecast-card">
    <div class="card-head">
        <h3>Financial forecast</h3>
        <span class="badge" id="forecastConfidence" title="R² - how well the trend fits your history">confidence: -</span>
    </div>
    <p class="card-note">
        Projection of the next 3 months based on a linear-regression trend of
        your monthly history. Dashed lines are predictions, not promises!
    </p>
    <div class="chart-box"><canvas id="forecastChart" height="240"></canvas></div>

    <div class="whatif" id="whatifPanel">
        <div class="whatif-head">
            <h4>What-if simulator</h4>
            <button class="btn btn-ghost btn-sm" id="whatifReset" type="button">Reset</button>
        </div>
        <div class="whatif-grid">
            <label class="whatif-field">
                <span>Extra monthly income</span>
                <input type="number" id="wiIncome" class="input" value="0" step="10" min="-100000" max="100000">
            </label>
            <label class="whatif-field">
                <span>Extra monthly spending</span>
                <input type="number" id="wiExpense" class="input" value="0" step="10" min="-100000" max="100000">
            </label>
            <label class="whatif-field">
                <span>One-off purchase (month 1)</span>
                <input type="number" id="wiOneOff" class="input" value="0" step="50" min="0" max="1000000">
            </label>
        </div>
        <div class="whatif-out" id="whatifOut"></div>
    </div>
</section>

{{-- ==================== INSIGHTS + RECENT ============================ --}}
<section class="grid-2">
    <div class="card">
        <div class="card-head"><h3>Insights</h3></div>

        @if ($insights)
            <ul class="insight-list">
                @foreach ($insights as $i)
                    <li>{!! $i /* built and escaped in the controller */ !!}</li>
                @endforeach
            </ul>
        @else
            <p class="muted">Add a few transactions and smart insights will appear here.</p>
        @endif

        @if ($alerts)
            <h4 class="mini-title">Budget alerts</h4>
            @foreach ($alerts as $a)
                @php
                    $ratio = min(1.5, (float) $a->ratio);
                    $pct = round($ratio * 100);
                @endphp
                <div class="budget-alert">
                    <div class="budget-alert-row">
                        <span class="cat-pill" style="--c:{{ $a->color }}">{{ $a->name }}</span>
                        <span class="{{ $ratio > 1 ? 'neg' : '' }}">
                            {{ money((float) $a->spent) }} / {{ money((float) $a->budget_amount) }}
                        </span>
                    </div>
                    <div class="progress">
                        <span class="progress-fill {{ $ratio > 1 ? 'over' : ($ratio >= 0.8 ? 'warn' : '') }}"
                              style="width: {{ min(100, $pct) }}%"></span>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div class="card">
        <div class="card-head">
            <h3>Recent transactions</h3>
            <a class="muted-link" href="{{ route('transactions') }}">View all →</a>
        </div>

        @if ($recent->isNotEmpty())
            <ul class="tx-list">
                @foreach ($recent as $t)
                    <li>
                        <span class="dot" style="background:{{ $t->category->color }}"></span>
                        <span class="tx-meta">
                            <span class="tx-name">{{ $t->description ?: $t->category->name }}</span>
                            <span class="tx-sub">{{ $t->category->name }} · {{ nice_date($t->transaction_date) }}</span>
                        </span>
                        <span class="tx-amount {{ $t->type === 'income' ? 'pos' : 'neg' }}">
                            {{ $t->type === 'income' ? '+' : '-' }}{{ money((float) $t->amount) }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="muted">Nothing here yet.</p>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/vendor/chart.umd.js') }}"></script>
<script src="{{ asset('js/dashboard.js') }}"></script>
@endpush

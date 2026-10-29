<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ============================================================================
 *  DashboardController - the analytics home page.
 * ----------------------------------------------------------------------------
 *  Assembles the headline figures, spending insights, budget alerts and the
 *  most recent transactions. The charts themselves are drawn client-side
 *  (public/js/dashboard.js) from JSON served by ApiController.
 * ============================================================================
 */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $userId = (int) $request->user()->user_id;
        $ym = date('Y-m');

        $summary = Transaction::summary($userId);

        // ---------- human-readable insights ----------
        $insights = [];

        $topCats = Transaction::categoryTotals($userId, $ym);
        if ($topCats->isNotEmpty()) {
            $insights[] = 'Your biggest spending category this month is <strong>'
                .e($topCats[0]->name).'</strong> at <strong>'
                .money((float) $topCats[0]->total).'</strong>.';
        }

        $top = Transaction::topExpense($userId, $ym);
        if ($top !== null) {
            $insights[] = 'Largest single expense: <strong>'.money((float) $top->amount)
                .'</strong> ('.e($top->category->name)
                .($top->description ? ', '.e($top->description) : '').').';
        }

        $prev = $summary['prev_expense'];
        $curE = $summary['month_expense'];
        if ($prev > 0) {
            $change = (($curE - $prev) / $prev) * 100;
            $direction = $change >= 0 ? 'more' : 'less';
            $insights[] = 'So far you have spent <strong>'.number_format(abs($change), 1)
                .'% '.$direction.'</strong> than last month at this point... '
                .($change >= 0 ? 'keep an eye on it.' : 'nice work!');
        }

        $income = $summary['month_income'];
        if ($income > 0) {
            $rate = (($income - $curE) / $income) * 100;
            $insights[] = 'Savings rate this month: <strong>'.number_format(max($rate, 0), 1)
                .'%</strong> of income kept.';
        }

        return view('dashboard.index', [
            'pageTitle' => 'Dashboard',
            'summary' => $summary,
            'insights' => $insights,
            'recent' => Transaction::recentFor($userId, 6),
            'alerts' => Budget::alerts($userId, $ym),
            'monthName' => date('F Y'),
        ]);
    }
}

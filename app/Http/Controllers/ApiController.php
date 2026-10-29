<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\Forecast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ============================================================================
 *  ApiController - JSON endpoints consumed by the dashboard JavaScript.
 * ----------------------------------------------------------------------------
 *  GET /api/cashflow   : monthly income & expense series (bar chart)
 *  GET /api/categories : expense split by category for one month (doughnut)
 *  GET /api/forecast   : linear-regression projection of the next 3 months
 *                        + data for the interactive what-if simulator
 *  All endpoints sit behind the 'auth' middleware and return JSON only.
 * ============================================================================
 */
class ApiController extends Controller
{
    /**
     * Monthly income vs expense for (up to) the last 12 months.
     */
    public function cashflow(Request $request): JsonResponse
    {
        $series = Transaction::monthlyTotals((int) $request->user()->user_id, 12);

        return response()->json([
            'months' => array_column($series, 'ym'),
            'income' => array_column($series, 'income'),
            'expense' => array_column($series, 'expense'),
        ]);
    }

    /**
     * Expense split by category for one month (?m=YYYY-MM, default: current).
     */
    public function categories(Request $request): JsonResponse
    {
        $m = $request->query('m', '');
        if (! is_string($m) || preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $m) !== 1) {
            $m = date('Y-m');
        }

        $rows = Transaction::categoryTotals((int) $request->user()->user_id, $m);

        return response()->json([
            'month' => $m,
            'labels' => $rows->pluck('name'),
            'colors' => $rows->pluck('color'),
            'totals' => $rows->pluck('total')->map(fn ($t) => (float) $t),
        ]);
    }

    /**
     * Predict the next three months using least-squares linear regression
     * over the user's monthly history (see App\Services\Forecast).
     */
    public function forecast(Request $request): JsonResponse
    {
        $userId = (int) $request->user()->user_id;

        $series = Transaction::monthlyTotals($userId, 12);

        $income = array_column($series, 'income');
        $expense = array_column($series, 'expense');
        $net = array_map(
            fn (array $m): float => $m['income'] - $m['expense'],
            $series
        );

        /*
         * The current month is usually incomplete (e.g. the salary has not
         * arrived yet), so it would drag the trend down unfairly. It is
         * EXCLUDED from the regression fit but still shown in the history.
         */
        $isPartial = $series !== [] && end($series)['ym'] === date('Y-m') && count($series) > 1;
        $fitIncome = $isPartial ? array_slice($income, 0, -1) : $income;
        $fitExpense = $isPartial ? array_slice($expense, 0, -1) : $expense;
        $fitNet = $isPartial ? array_slice($net, 0, -1) : $net;

        // For a partial month we also predict the CURRENT month's full value
        // (one extra period) and drop it from the displayed forecast below.
        $ahead = $isPartial ? 4 : 3;
        $incomeF = Forecast::project($fitIncome, $ahead, true);
        $expenseF = Forecast::project($fitExpense, $ahead, true);
        $netF = Forecast::project($fitNet, $ahead, false);

        $predIncome = $isPartial ? array_slice($incomeF['predictions'], 1) : $incomeF['predictions'];
        $predExpense = $isPartial ? array_slice($expenseF['predictions'], 1) : $expenseF['predictions'];
        $predNet = $isPartial ? array_slice($netF['predictions'], 1) : $netF['predictions'];

        // Labels for the three future months
        $futureMonths = [];
        for ($i = 1; $i <= 3; $i++) {
            $futureMonths[] = date('Y-m', strtotime(date('Y-m-01')." +{$i} months"));
        }

        /*
         * Projected running balance. Start from today's balance plus, when
         * the month is partial, the expected REMAINDER of this month
         * (predicted full-month net minus what has already happened).
         */
        $balance = Transaction::summary($userId)['balance'];
        $running = $balance;
        if ($isPartial) {
            $running += $netF['predictions'][0] - (float) end($net);
        }
        $projected = [];
        foreach ($predNet as $p) {
            $running += $p;
            $projected[] = round($running, 2);
        }

        // Baseline (average of the last up-to-3 FULL months) for the what-if tool
        $lastIncome = array_slice($fitIncome, -3);
        $lastExpense = array_slice($fitExpense, -3);
        $avg = fn (array $a): float => count($a) ? round(array_sum($a) / count($a), 2) : 0.0;

        return response()->json([
            'history' => [
                'months' => array_column($series, 'ym'),
                'income' => $income,
                'expense' => $expense,
                'net' => $net,
            ],
            'forecast' => [
                'months' => $futureMonths,
                'income' => $predIncome,
                'expense' => $predExpense,
                'net' => $predNet,
            ],
            'model' => [
                'slope' => round($netF['model']['slope'], 2),
                'intercept' => round($netF['model']['intercept'], 2),
                'r2' => round($netF['model']['r2'], 3),
            ],
            'balance' => [
                'current' => round($balance, 2),
                'projected' => $projected,
            ],
            'baseline' => [
                'avg_income' => $avg($lastIncome),
                'avg_expense' => $avg($lastExpense),
            ],
        ]);
    }
}

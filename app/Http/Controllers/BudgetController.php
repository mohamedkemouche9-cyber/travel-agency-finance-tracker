<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ============================================================================
 *  BudgetController - monthly spending limits per category.
 * ----------------------------------------------------------------------------
 *  The page shows every category with its budget (if any) and the amount
 *  spent so far in the selected month, rendered as progress bars. Budgets
 *  power the "close to limit" alerts on the dashboard.
 * ============================================================================
 */
class BudgetController extends Controller
{
    public function index(Request $request): View
    {
        $userId = (int) $request->user()->user_id;
        $month = $this->readMonth($request);

        $rows = Budget::forMonth($userId, $month);

        $totalBudget = 0.0;
        $totalSpent = 0.0;
        foreach ($rows as $r) {
            if ($r->budget_amount !== null) {
                $totalBudget += (float) $r->budget_amount;
                $totalSpent += (float) $r->spent;
            }
        }

        return view('budgets.index', [
            'pageTitle' => 'Budgets',
            'rows' => $rows,
            'month' => $month,
            'monthLabel' => date('F Y', strtotime($month.'-01')),
            'prevMonth' => date('Y-m', strtotime($month.'-01 -1 month')),
            'nextMonth' => date('Y-m', strtotime($month.'-01 +1 month')),
            'totalBudget' => $totalBudget,
            'totalSpent' => $totalSpent,
        ]);
    }

    /**
     * Save all budget inputs for the month in one submission.
     */
    public function save(Request $request): RedirectResponse
    {
        $userId = (int) $request->user()->user_id;

        $month = (is_string($request->input('month'))
                  && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $request->input('month')))
            ? $request->input('month') : date('Y-m');

        $amounts = $request->input('amounts', []);
        if (! is_array($amounts)) {
            $amounts = [];
        }

        foreach ($amounts as $categoryId => $value) {
            $value = trim((string) $value);

            // Blank or zero clears the budget; otherwise must be a valid amount
            if ($value !== '' && (! is_numeric($value) || (float) $value < 0 || (float) $value > 999999.99)) {
                continue;
            }
            if (! Category::isUsableBy((int) $categoryId, $userId)) {
                continue;
            }

            Budget::put($userId, (int) $categoryId, $month, $value === '' ? 0.0 : (float) $value);
        }

        flash('success', 'Budgets saved for '.date('F Y', strtotime($month.'-01')).'.');

        return redirect()->route('budgets', ['m' => $month]);
    }

    /* ------------------------------- helpers -------------------------------- */

    private function readMonth(Request $request): string
    {
        $m = $request->query('m', '');

        return (is_string($m) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $m))
            ? $m : date('Y-m');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ============================================================================
 *  TransactionController - full CRUD for income & expense records.
 * ----------------------------------------------------------------------------
 *  index   : filterable, searchable, paginated table (+ totals of the filter)
 *  create/store  : add a transaction    (GET form / POST submit)
 *  edit/update   : change a transaction (GET form / POST submit)
 *  destroy : remove a transaction       (POST only, CSRF-protected)
 *  export  : download the filtered list as a CSV file
 * ============================================================================
 */
class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $userId = (int) $request->user()->user_id;
        $filters = $this->readFilters($request);

        $query = Transaction::ownedBy($userId)->filtered($filters);

        // Aggregates over the WHOLE filtered set (not just the current page)
        $sums = (clone $query)
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income'  THEN amount END), 0) AS sum_income")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount END), 0) AS sum_expense")
            ->first();

        $rows = $query->with('category')
            ->orderByDesc('transaction_date')
            ->orderByDesc('transaction_id')
            ->paginate(10)
            ->withQueryString();

        return view('transactions.index', [
            'pageTitle' => 'Transactions',
            'rows' => $rows,
            'sumIncome' => (float) $sums->sum_income,
            'sumExpense' => (float) $sums->sum_expense,
            'filters' => $filters,
            'categories' => Category::allForUser($userId),
        ]);
    }

    public function create(Request $request): View
    {
        return view('transactions.form', [
            'pageTitle' => 'Add transaction',
            'mode' => 'add',
            'categories' => Category::allForUser((int) $request->user()->user_id),
            'tx' => null,
        ]);
    }

    public function store(TransactionRequest $request): RedirectResponse
    {
        $request->user()->transactions()->create($request->transactionData());

        flash('success', 'Transaction added.');

        return redirect()->route('transactions');
    }

    public function edit(Request $request, int $id): View|RedirectResponse
    {
        $tx = Transaction::ownedBy((int) $request->user()->user_id)->find($id);

        if ($tx === null) {
            flash('error', 'Transaction not found.');

            return redirect()->route('transactions');
        }

        return view('transactions.form', [
            'pageTitle' => 'Edit transaction',
            'mode' => 'edit',
            'categories' => Category::allForUser((int) $request->user()->user_id),
            'tx' => $tx,
        ]);
    }

    public function update(TransactionRequest $request, int $id): RedirectResponse
    {
        $tx = Transaction::ownedBy((int) $request->user()->user_id)->find($id);

        if ($tx === null) {
            flash('error', 'Transaction not found.');

            return redirect()->route('transactions');
        }

        $tx->update($request->transactionData());

        flash('success', 'Transaction updated.');

        return redirect()->route('transactions');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $deleted = Transaction::ownedBy((int) $request->user()->user_id)
            ->whereKey($id)
            ->delete();

        flash($deleted ? 'success' : 'error',
            $deleted ? 'Transaction deleted.' : 'Transaction not found.');

        return redirect()->route('transactions');
    }

    /**
     * Stream the current filtered list as a CSV download.
     */
    public function export(Request $request): StreamedResponse
    {
        $userId = (int) $request->user()->user_id;
        $filters = $this->readFilters($request);

        $rows = Transaction::ownedBy($userId)
            ->filtered($filters)
            ->with('category')
            ->orderByDesc('transaction_date')
            ->orderByDesc('transaction_id')
            ->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Type', 'Category', 'Amount', 'Description'], ',', '"', '\\');

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->transaction_date,
                    $r->type,
                    self::csvSafe($r->category->name),
                    $r->amount,
                    self::csvSafe((string) $r->description),
                ], ',', '"', '\\');
            }
            fclose($out);
        }, 'transactions_'.date('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=utf-8']);
    }

    /* ------------------------------- helpers -------------------------------- */

    /**
     * Read and sanitise the list filters from the query string.
     */
    private function readFilters(Request $request): array
    {
        $date = fn (?string $v): string => (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) ? $v : '';

        return [
            'type' => (string) $request->query('type', ''),
            'category_id' => (string) $request->query('category_id', ''),
            'date_from' => $date($request->query('date_from')),
            'date_to' => $date($request->query('date_to')),
            'q' => trim((string) $request->query('q', '')),
        ];
    }

    /**
     * Neutralise spreadsheet formula injection (cells starting with = + - @).
     */
    private static function csvSafe(string $value): string
    {
        return preg_match('/^[=+\-@]/', $value) === 1 ? "'".$value : $value;
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

/**
 * ============================================================================
 *  TransactionRequest - shared server-side validation for add & edit.
 * ----------------------------------------------------------------------------
 *  Client-side JavaScript mirrors these rules for instant feedback, but the
 *  server ALWAYS re-validates: the browser can never be trusted. The
 *  category rule also checks OWNERSHIP: it must be a global category or one
 *  belonging to the logged-in user.
 * ============================================================================
 */
class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:income,expense'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999.99',
                'regex:/^\d+(\.\d{1,2})?$/'],
            'transaction_date' => ['required', 'date_format:Y-m-d',
                'before_or_equal:'.date('Y-m-d')],
            'category_id' => ['required', 'integer',
                function ($attribute, $value, $fail) {
                    if (! Category::isUsableBy((int) $value, (int) $this->user()->user_id)) {
                        $fail('Please choose a valid category.');
                    }
                }],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.gt' => 'Amount must be a positive number.',
            'amount.max' => 'Amount must not exceed 999,999.99.',
            'amount.regex' => 'Amount can have at most two decimal places.',
            'transaction_date.before_or_equal' => 'The date cannot be in the future.',
        ];
    }

    /** The validated fields in the shape the model expects. */
    public function transactionData(): array
    {
        $v = $this->validated();

        return [
            'type' => $v['type'],
            'amount' => number_format((float) $v['amount'], 2, '.', ''),
            'transaction_date' => $v['transaction_date'],
            'category_id' => (int) $v['category_id'],
            'description' => trim((string) ($v['description'] ?? '')) ?: null,
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ============================================================================
 *  CategoryController - manage spending/income categories.
 * ----------------------------------------------------------------------------
 *  Global default categories (Food, Rent, Salary, ...) are read-only.
 *  Users can create, rename, recolour and delete their OWN categories;
 *  a category that still has transactions attached cannot be deleted
 *  (enforced in code and by the ON DELETE RESTRICT foreign key).
 * ============================================================================
 */
class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        return view('categories.index', [
            'pageTitle' => 'Categories',
            'categories' => Category::allForUser((int) $request->user()->user_id),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = (int) $request->user()->user_id;

        $error = $this->validateCategory($request, $userId);
        if ($error !== null) {
            flash('error', $error);

            return redirect()->route('categories');
        }

        $name = $request->string('name')->trim()->value();

        Category::create([
            'user_id' => $userId,
            'name' => $name,
            'color' => $request->input('color'),
        ]);

        flash('success', 'Category "'.$name.'" created.');

        return redirect()->route('categories');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $userId = (int) $request->user()->user_id;

        $category = Category::where('category_id', $id)->where('user_id', $userId)->first();
        if ($category === null) {
            flash('error', 'Only your own categories can be edited.');

            return redirect()->route('categories');
        }

        $error = $this->validateCategory($request, $userId, $id);
        if ($error !== null) {
            flash('error', $error);

            return redirect()->route('categories');
        }

        $category->update([
            'name' => $request->string('name')->trim()->value(),
            'color' => $request->input('color'),
        ]);

        flash('success', 'Category updated.');

        return redirect()->route('categories');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $userId = (int) $request->user()->user_id;

        $category = Category::where('category_id', $id)->where('user_id', $userId)->first();
        if ($category === null) {
            flash('error', 'Only your own categories can be deleted.');

            return redirect()->route('categories');
        }

        try {
            $category->delete();
            flash('success', 'Category deleted.');
        } catch (QueryException) {
            // Foreign key (ON DELETE RESTRICT): transactions still reference it
            flash('error', 'This category still has transactions. Edit or delete those first.');
        }

        return redirect()->route('categories');
    }

    /* ------------------------------- helpers -------------------------------- */

    /**
     * Validate the category form; returns the first error message or null.
     * (The page uses flash banners rather than per-field errors, matching
     * the inline add/edit forms of the UI.)
     */
    private function validateCategory(Request $request, int $userId, int $excludeId = 0): ?string
    {
        $validator = validator($request->all(), [
            'name' => ['required', 'string', 'min:2', 'max:40'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ], [
            'color.regex' => 'Please pick a valid colour.',
        ]);

        if ($validator->fails()) {
            return $validator->errors()->first();
        }

        $name = $request->string('name')->trim()->value();
        if (Category::nameExists($name, $userId, $excludeId)) {
            return 'A category with this name already exists.';
        }

        return null;
    }
}

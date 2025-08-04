<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseCategoryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('expense_categories')],
            'stores' => 'nullable|array' // For the store assignments
        ]);

        $maxOrder = ExpenseCategory::max('order_column');
        $validatedData = [
            'name' => $validated['name'],
            'order_column' => $maxOrder + 1,
        ];

        $expenseCategory = ExpenseCategory::create($validatedData);

        // If stores were selected, attach them
        if (!empty($validated['stores'])) {
            $expenseCategory->stores()->attach($validated['stores']);
        }

        return redirect()->route('settings.index', ['tab' => 'expense-categories'])->with('success', 'Category created.');
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('expense_categories')->ignore($expenseCategory->id)],
            'stores' => 'nullable|array' // For the store assignments
        ]);

        $expenseCategory->update(['name' => $validated['name']]);

        // Use sync() to update the relationships
        $expenseCategory->stores()->sync($validated['stores'] ?? []);

        return redirect()->route('settings.index', ['tab' => 'expense-categories'])->with('success', 'Category updated.');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        $expenseCategory->delete();
        return redirect()->route('settings.index', ['tab' => 'expense-categories'])->with('success', 'Category deleted.');
    }

    public function updateOrder(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        foreach ($request->ids as $index => $id) {
            ExpenseCategory::where('id', $id)->update(['order_column' => $index]);
        }
        return response()->json(['status' => 'success']);
    }
}

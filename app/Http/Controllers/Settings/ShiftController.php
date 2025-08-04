<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShiftController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('shifts')],
            'stores' => 'nullable|array' // 'stores' will be an array of IDs
        ]);

        $maxOrder = Shift::max('order_column');
        $validatedData = [
            'name' => $validated['name'],
            'order_column' => $maxOrder + 1,
        ];

        $shift = Shift::create($validatedData);

        // If stores were selected, attach them to the new shift
        if (!empty($validated['stores'])) {
            $shift->stores()->attach($validated['stores']);
        }

        return redirect()->route('settings.index', ['tab' => 'shifts'])->with('success', 'Shift created.');
    }

    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('shifts')->ignore($shift->id)],
            'stores' => 'nullable|array' // 'stores' will be an array of IDs
        ]);

        $shift->update(['name' => $validated['name']]);

        // The sync() method is perfect for this. It adds/removes relationships
        // to match the exact array of IDs provided.
        $shift->stores()->sync($validated['stores'] ?? []);

        return redirect()->route('settings.index', ['tab' => 'shifts'])->with('success', 'Shift updated.');
    }
    public function destroy(Shift $shift)
    {
        $shift->delete();
        return redirect()->route('settings.index', ['tab' => 'shifts'])->with('success', 'Shift deleted.');
    }
    public function updateOrder(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        foreach ($request->ids as $i => $id) {
            Shift::where('id', $id)->update(['order_column' => $i]);
        }
        return response()->json(['status' => 'success']);
    }
}

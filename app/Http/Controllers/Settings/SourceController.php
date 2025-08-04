<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SourceController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('sources')],
            'stores' => 'nullable|array' // For the store assignments
        ]);

        $maxOrder = Source::max('order_column');
        $validatedData = [
            'name' => $validated['name'],
            'order_column' => $maxOrder + 1,
        ];

        $source = Source::create($validatedData);

        // If stores were selected, attach them
        if (!empty($validated['stores'])) {
            $source->stores()->attach($validated['stores']);
        }

        return redirect()->route('settings.index', ['tab' => 'sources'])->with('success', 'Source created.');
    }

    public function update(Request $request, Source $source)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('sources')->ignore($source->id)],
            'stores' => 'nullable|array' // For the store assignments
        ]);

        $source->update(['name' => $validated['name']]);

        // Use sync() to update the relationships
        $source->stores()->sync($validated['stores'] ?? []);

        return redirect()->route('settings.index', ['tab' => 'sources'])->with('success', 'Source updated.');
    }

    public function destroy(Source $source)
    {
        $source->delete();
        return redirect()->route('settings.index', ['tab' => 'sources'])->with('success', 'Source deleted.');
    }

    public function updateOrder(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        foreach ($request->ids as $index => $id) {
            Source::where('id', $id)->update(['order_column' => $index]);
        }
        return response()->json(['status' => 'success']);
    }
}

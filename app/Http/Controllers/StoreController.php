<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255|unique:stores,name', 'comments' => 'nullable|string']);
        $maxOrder = Store::max('order_column');
        $validated['order_column'] = $maxOrder + 1;
        Store::create($validated);
        return redirect()->route('settings.index', ['tab' => 'stores'])->with('success', 'Store created.');
    }
    public function update(Request $request, Store $store)
    {
        $validated = $request->validate(['name' => 'required|string|max:255|unique:stores,name,' . $store->id, 'comments' => 'nullable|string']);
        $store->update($validated);
        return redirect()->route('settings.index', ['tab' => 'stores'])->with('success', 'Store updated.');
    }
    public function destroy(Store $store)
    {
        $store->delete();
        return redirect()->route('settings.index', ['tab' => 'stores'])->with('success', 'Store deleted.');
    }
    public function updateOrder(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        foreach ($request->ids as $index => $id) {
            Store::where('id', $id)->update(['order_column' => $index]);
        }
        return response()->json(['status' => 'success']);
    }
}

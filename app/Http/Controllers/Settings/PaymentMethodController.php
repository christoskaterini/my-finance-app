<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentMethodController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('payment_methods')],
            'stores' => 'nullable|array' // For the store assignments
        ]);

        $maxOrder = PaymentMethod::max('order_column');
        $validatedData = [
            'name' => $validated['name'],
            'order_column' => $maxOrder + 1,
        ];

        $paymentMethod = PaymentMethod::create($validatedData);

        // If stores were selected, attach them
        if (!empty($validated['stores'])) {
            $paymentMethod->stores()->attach($validated['stores']);
        }

        return redirect()->route('settings.index', ['tab' => 'payment-methods'])->with('success', 'Payment Method created.');
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('payment_methods')->ignore($paymentMethod->id)],
            'stores' => 'nullable|array' // For the store assignments
        ]);

        $paymentMethod->update(['name' => $validated['name']]);

        // Use sync() to update the relationships
        $paymentMethod->stores()->sync($validated['stores'] ?? []);

        return redirect()->route('settings.index', ['tab' => 'payment-methods'])->with('success', 'Payment Method updated.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();
        return redirect()->route('settings.index', ['tab' => 'payment-methods'])->with('success', 'Payment Method deleted.');
    }

    public function updateOrder(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        foreach ($request->ids as $index => $id) {
            PaymentMethod::where('id', $id)->update(['order_column' => $index]);
        }
        return response()->json(['status' => 'success']);
    }
}

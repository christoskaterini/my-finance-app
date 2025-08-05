<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Store;
use App\Models\ExpenseCategory;
use App\Models\Shift;
use App\Models\Source;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function () {

            // 1. Create the Stores
            $store1 = Store::create(['name' => 'Κατάστημα 1']);
            $store2 = Store::create(['name' => 'Κατάστημα 2']);
            $store3 = Store::create(['name' => 'Κατάστημα 3']);
            $allStores = collect([$store1, $store2, $store3]);

            // 2. Create the Expense Categories
            $expenseCat1 = ExpenseCategory::create(['name' => 'Έξοδα 1']);
            $expenseCat2 = ExpenseCategory::create(['name' => 'Έξοδα 2']);
            $expenseCat3 = ExpenseCategory::create(['name' => 'Έξοδα 3']); // Corrected from 4 to 3 for consistency
            $allExpenseCategories = collect([$expenseCat1, $expenseCat2, $expenseCat3]);

            // 3. Create the Shifts
            $shift1 = Shift::create(['name' => 'Πρωί']);
            $shift2 = Shift::create(['name' => 'Απόγευμα']);
            $shift3 = Shift::create(['name' => 'Βράδυ']);
            $allShifts = collect([$shift1, $shift2, $shift3]);

            // 4. Create the Sources
            $source1 = Source::create(['name' => 'Ταμείο']);
            $source2 = Source::create(['name' => 'Delivery']);
            $allSources = collect([$source1, $source2]);

            // 5. Create the Payment Methods
            $paymentMethod1 = PaymentMethod::create(['name' => 'Μετρητά']);
            $paymentMethod2 = PaymentMethod::create(['name' => 'Κάρτα']);
            $paymentMethod3 = PaymentMethod::create(['name' => 'Σύνολο']);
            $allPaymentMethods = collect([$paymentMethod1, $paymentMethod2, $paymentMethod3]);

            // 6. Assign all categories to all stores (Many-to-Many relationships)
            foreach ($allStores as $store) {
                // Attach all expense categories to this store
                $store->expenseCategories()->attach($allExpenseCategories->pluck('id'));

                // Attach all shifts to this store
                $store->shifts()->attach($allShifts->pluck('id'));

                // Attach all sources to this store
                $store->sources()->attach($allSources->pluck('id'));

                // Attach all payment methods to this store
                $store->paymentMethods()->attach($allPaymentMethods->pluck('id'));
            }
        });
        // Seed the users table.
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'christoskanotidis@gmail.com',
            'role' => 'admin',
            'password' => 'password',
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'store_id',
        'transaction_date',
        'type',
        'amount',
        'notes',
        'expense_category_id',
        'shift_id',
        'source_id',
        'payment_method_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'store_id' => 'integer',
        'transaction_date' => 'date',
        'type' => 'string',
        'amount' => 'double',
        'expense_category_id' => 'integer',
        'shift_id' => 'integer',
        'source_id' => 'integer',
        'payment_method_id' => 'integer',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
    public function expenseCategory()
    {
        return $this->belongsTo(ExpenseCategory::class);
    }
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
    public function source()
    {
        return $this->belongsTo(Source::class);
    }
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Formated date in filter
    protected function transactionDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => (new \DateTime($value))->format('d/m/Y'),
            set: fn ($value) => (new \DateTime($value))->format('Y-m-d'),
        );
    }

    /**
     * Get the transaction date formatted for editing.
     *
     * @return string
     */
    public function getTransactionDateForEditAttribute(): string
    {
        return (new \DateTime($this->attributes['transaction_date']))->format('Y-m-d');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'comments', 'order_column'];
    public function expenseCategories()
    {
        return $this->belongsToMany(ExpenseCategory::class);
    }
    public function shifts()
    {
        return $this->belongsToMany(Shift::class);
    }
    public function sources()
    {
        return $this->belongsToMany(Source::class);
    }
    public function paymentMethods()
    {
        return $this->belongsToMany(PaymentMethod::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}

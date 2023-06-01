<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionCurrency extends Model
{
    use HasFactory;
    protected $table="transactioncurrency";
    protected $fillable = [
        'user_id',
        'currency_id',
        'transaction_type',
        'current_price',
        'quantity',
        'amount',
        'transaction_date',
    ];
    public function cryptoCurrency()
    {
        return $this->hasOne(CryptoCurrency::class,'id','currency_id');
    }
}

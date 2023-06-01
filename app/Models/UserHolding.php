<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHolding extends Model
{
    use HasFactory;
    protected $table="userholdings";
    public function cryptoCurrency()
    {
        return $this->hasOne(CryptoCurrency::class,'id','currency_id');
    }
}

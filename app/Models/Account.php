<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['id', 'balance'];
    protected $hidden = ['created_at', 'updated_at'];
    protected $casts = [
        'id' => 'string',
        'balance' => 'double'
    ];

    public function balance($account_id)
    {
        $account = Account::find($account_id);
        if ($account) {
            return $account->balance;
        } else {
            return false;
        }
    }
}

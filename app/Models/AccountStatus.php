<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountStatus extends Model
{
    protected $table = 'account_statuses';
    protected $primaryKey = 'Account_Status_ID';
    public $incrementing = true;  // <--- VERY IMPORTANT
    protected $keyType = 'int';

    protected $fillable = ['Status_Name'];

    public function users()
    {
        return $this->hasMany(User::class, 'Account_Status_ID', 'Account_Status_ID');
    }
}
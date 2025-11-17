<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'balance',
        'agency_number',
        'account_number',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'balance' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

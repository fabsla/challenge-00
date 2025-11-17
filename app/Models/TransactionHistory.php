<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionHistory extends Model
{
    use HasFactory;

    protected $table = 'transaction_history';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'account_id',
        'user_id',
        'type',
        'amount',
        'description',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'integer',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

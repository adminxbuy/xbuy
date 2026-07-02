<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'is_activated',
        'first_name',
        'last_name',
        'dob_day',
        'dob_month',
        'dob_year',
        'ssn_last_four'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Credit the wallet balance.
     */
    public function credit(float $amount, string $source, ?int $referenceId = null, ?string $description = null): WalletTransaction
    {
        return \DB::transaction(function () use ($amount, $source, $referenceId, $description) {
            $this->increment('balance', $amount);

            return $this->transactions()->create([
                'type' => 'credit',
                'amount' => $amount,
                'source' => $source,
                'reference_id' => $referenceId,
                'description' => $description
            ]);
        });
    }

    /**
     * Debit the wallet balance.
     */
    public function debit(float $amount, string $source, ?int $referenceId = null, ?string $description = null): WalletTransaction
    {
        return \DB::transaction(function () use ($amount, $source, $referenceId, $description) {
            if ($this->balance < $amount) {
                throw new \Exception("Insufficient wallet balance.");
            }

            $this->decrement('balance', $amount);

            return $this->transactions()->create([
                'type' => 'debit',
                'amount' => $amount,
                'source' => $source,
                'reference_id' => $referenceId,
                'description' => $description
            ]);
        });
    }
}

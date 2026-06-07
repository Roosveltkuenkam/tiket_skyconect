<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'provider',
        'payment_method',
        'campay_reference',
        'operator_reference',
        'amount',
        'phone',
        'status',
        'raw_response',
        'paid_at',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'paid_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function safeRawResponse()
    {
        return $this->sanitizeRawValue($this->raw_response ?: []);
    }

    private function sanitizeRawValue($value)
    {
        if (! is_array($value)) {
            return $value;
        }

        $safe = [];

        foreach ($value as $key => $item) {
            if ($this->isSensitiveRawKey($key)) {
                $safe[$key] = '[hidden]';
                continue;
            }

            $safe[$key] = $this->sanitizeRawValue($item);
        }

        return $safe;
    }

    private function isSensitiveRawKey($key)
    {
        foreach (['password', 'token', 'secret', 'key', 'authorization', 'signature', 'raw', 'phone'] as $sensitive) {
            if (stripos((string) $key, $sensitive) !== false) {
                return true;
            }
        }

        return false;
    }

    use HasFactory;
}

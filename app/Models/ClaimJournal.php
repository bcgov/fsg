<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClaimJournal extends Model
{
    protected $fillable = ['claim_id', 'updated_fields', 'old_values', 'new_values'];

    // Cast JSON fields to arrays automatically
    protected $casts = [
        'updated_fields' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimilarityScore extends Model
{
    protected $fillable = ['user_id', 'kandidat_id', 'skor'];

    protected function casts(): array
    {
        return [
            'skor' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kandidat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kandidat_id');
    }
}

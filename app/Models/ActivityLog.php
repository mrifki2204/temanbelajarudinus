<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Catat satu baris aktivitas. Panggil tepat setelah operasi DB sukses.
     *
     * @param  string       $action      Tag aksi, mis. 'fakultas.create'
     * @param  string       $description Keterangan manusiawi
     * @param  object|null  $subject     Model subjek (opsional)
     * @param  array        $properties  Data tambahan (opsional)
     */
    /**
     * Catat satu baris aktivitas. Panggil tepat setelah operasi DB sukses.
     *
     * Delegasikan pengambilan konteks HTTP (IP, user agent, user) ke
     * ActivityLogger (service) supaya model tidak bergantung pada Request.
     *
     * @param  string       $action      Tag aksi, mis. 'fakultas.create'
     * @param  string       $description Keterangan manusiawi
     * @param  object|null  $subject     Model subjek (opsional)
     * @param  array        $properties  Data tambahan (opsional)
     */
    public static function record(
        string $action,
        string $description,
        ?object $subject = null,
        array $properties = [],
    ): self {
        return app(\App\Services\ActivityLogger::class)->log(
            $action,
            $description,
            $subject,
            $properties,
        );
    }
}

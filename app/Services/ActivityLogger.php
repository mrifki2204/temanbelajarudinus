<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

/**
 * Mencatat baris aktivitas ke tabel activity_logs.
 *
 * Di-resolve dari container sehingga dependency Request di-inject — tidak
 * lagi di-resolve manual via app(Request::class) di dalam model (coupling
 * HTTP layer). Model ActivityLog::record() mendelegasikan ke sini.
 */
class ActivityLogger
{
    public function __construct(
        protected Request $request,
    ) {}

    /**
     * Catat satu baris aktivitas. Panggil tepat setelah operasi DB sukses.
     *
     * @param  string       $action      Tag aksi, mis. 'fakultas.create'
     * @param  string       $description Keterangan manusiawi
     * @param  object|null  $subject     Model subjek (opsional)
     * @param  array        $properties  Data tambahan (opsional)
     */
    public function log(
        string $action,
        string $description,
        ?object $subject = null,
        array $properties = [],
    ): ActivityLog {
        $user = $this->request->user();

        return ActivityLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject ? $subject->getKey() : null,
            'properties' => $properties ?: null,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
        ]);
    }
}

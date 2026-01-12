<?php

namespace App\Jobs;

use App\Models\ShortLink;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class ProcessRedirectCount implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private ShortLink $shortLink,
        private string $cacheKey
    )
    {
        $this->delay(now()->addMinutes(10));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cachedCount = Cache::get($this->cacheKey);
        if ($cachedCount !== null) {
            $this->shortLink->increment('clicks', $cachedCount);
            Cache::forget($this->cacheKey);
        }
    }
}

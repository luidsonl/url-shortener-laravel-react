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
        private string $code,
    )
    {
        $this->delay(now()->addMinutes(10));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cacheKey = "code:$$this->code:clicks";
        $cachedCount = Cache::get($cacheKey);
        if ($cachedCount !== null) {
            $shortLink = ShortLink::findByCode($this->code);
            if (!$shortLink) {
                return;
            }
            $shortLink->increment('clicks', $cachedCount);
            Cache::forget($cacheKey);
        }
    }
}

<?php

namespace App\Jobs;

use App\Models\ShortLink;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessRedirectCount implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private ShortLink $shortLink,
    )
    {
        $this->delay(now()->addMinutes(10));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}

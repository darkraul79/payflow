<?php

namespace App\Jobs;

use App\Models\Donation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessDonationPaymentJob implements ShouldQueue
{
    use Queueable;

    public Donation $donation;

    /**
     * Create a new job instance.
     */
    public function __construct(Donation $donation)
    {
        //
        $this->donation = $donation;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $this->donation->processPay();
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Submission;
use App\Services\SubmissionService;
use Illuminate\Console\Command;

class AutoSubmitExpiredSubmissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'submissions:auto-submit-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically submit expired timed submissions with draft code';

    /**
     * Execute the console command.
     */
    public function handle(SubmissionService $submissionService)
    {
        $this->info('Checking for expired submissions...');

        // Find submissions where:
        // 1. Timer has ended (ending_at is in the past)
        // 2. Not yet submitted (submitted_at is null)
        // 3. Has draft code to submit
        $expiredSubmissions = Submission::with(['activityLink.activity', 'user'])
            ->whereNotNull('ending_at')
            ->whereNull('submitted_at')
            ->whereNotNull('draft_code')
            ->where('ending_at', '<', now())
            ->get();

        $count = 0;

        foreach ($expiredSubmissions as $submission) {
            try {
                $this->info("Auto-submitting for user {$submission->user->name} (ID: {$submission->user_id})...");

                $submissionService->autoSubmitExpiredSubmission(
                    $submission->activityLink,
                    $submission->user,
                    $submission->draft_code
                );

                $count++;
            } catch (\Exception $e) {
                $this->error("Failed to auto-submit for user {$submission->user_id}: {$e->getMessage()}");
                logger()->error("Auto-submit failed for submission {$submission->id}: {$e->getMessage()}");
            }
        }

        $this->comment("Auto-submitted {$count} expired submissions.");

        logger()->info("Auto-submitted {$count} expired submissions.");

        return Command::SUCCESS;
    }
}

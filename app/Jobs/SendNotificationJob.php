<?php

namespace App\Jobs;

use App\Mail\CampaignNotification;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $userId,
        public string $type,
        public string $title,
        public string $body,
        public ?array $data = null,
        public bool $sendEmail = true,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Create in-app notification
        UserNotification::create([
            'user_id' => $this->userId,
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
        ]);

        // Send email if requested
        if ($this->sendEmail) {
            try {
                $user = User::find($this->userId);
                if ($user && $user->email) {
                    Mail::to($user->email)->send(
                        new CampaignNotification(
                            user: $user,
                            subject: $this->title,
                            body: $this->body,
                            data: $this->data,
                        )
                    );
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send email notification to user {$this->userId}: " . $e->getMessage());
            }
        }
    }
}

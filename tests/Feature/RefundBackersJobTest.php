<?php

namespace Tests\Feature;

use App\Jobs\RefundBackersJob;
use App\Models\Backing;
use App\Models\Campaign;
use App\Models\CampaignTier;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefundBackersJobTest extends TestCase
{
    use RefreshDatabase;

    protected User $creator;
    protected User $backer1;
    protected User $backer2;
    protected Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        // Create creator
        $this->creator = User::factory()->creator()->create([
            'balance' => 0,
        ]);

        // Create backers with zero balance initially
        $this->backer1 = User::factory()->backer()->create([
            'balance' => 0,
        ]);
        $this->backer2 = User::factory()->backer()->create([
            'balance' => 0,
        ]);

        // Create an expired campaign that has NOT reached target
        $this->campaign = Campaign::factory()
            ->expired()
            ->create([
                'user_id' => $this->creator->id,
                'target_amount' => 10000000,
                'collected_amount' => 3000000,
            ]);
    }

    /** @test */
    public function it_refunds_all_backers()
    {
        // Create completed backings
        $backing1 = Backing::factory()->completed()->create([
            'user_id' => $this->backer1->id,
            'campaign_id' => $this->campaign->id,
            'amount' => 1000000,
        ]);

        $backing2 = Backing::factory()->completed()->create([
            'user_id' => $this->backer2->id,
            'campaign_id' => $this->campaign->id,
            'amount' => 2000000,
        ]);

        // Dispatch the job
        RefundBackersJob::dispatch($this->campaign->id);

        // Assert: campaign status changed to failed
        $this->campaign->refresh();
        $this->assertEquals('failed', $this->campaign->status);

        // Assert: backer balances restored
        $this->backer1->refresh();
        $this->assertEquals(1000000, $this->backer1->balance);

        $this->backer2->refresh();
        $this->assertEquals(2000000, $this->backer2->balance);

        // Assert: backings status changed to refunded
        $backing1->refresh();
        $this->assertEquals('refunded', $backing1->status);

        $backing2->refresh();
        $this->assertEquals('refunded', $backing2->status);

        // Assert: refund transactions created
        $refunds = Transaction::where('type', 'refund')->get();
        $this->assertCount(2, $refunds);

        // Assert: notifications sent to both backers
        $backerNotification = UserNotification::where('user_id', $this->backer1->id)
            ->where('type', 'backing_refunded')
            ->first();
        $this->assertNotNull($backerNotification);
        $this->assertStringContainsString('Dikembalikan', $backerNotification->title);

        // Assert: notification sent to creator
        $creatorNotification = UserNotification::where('user_id', $this->creator->id)
            ->where('type', 'campaign_failed')
            ->first();
        $this->assertNotNull($creatorNotification);
        $this->assertStringContainsString('Gagal', $creatorNotification->title);
    }

    /** @test */
    public function it_restores_tier_quota_on_refund()
    {
        // Create a tier with quota
        $tier = CampaignTier::factory()->create([
            'campaign_id' => $this->campaign->id,
            'quota' => 10,
            'remaining_quota' => 5,
        ]);

        // Create backing with tier
        $backing = Backing::factory()->completed()->create([
            'user_id' => $this->backer1->id,
            'campaign_id' => $this->campaign->id,
            'tier_id' => $tier->id,
            'amount' => 1000000,
        ]);

        RefundBackersJob::dispatch($this->campaign->id);

        // Assert: tier quota restored
        $tier->refresh();
        $this->assertEquals(6, $tier->remaining_quota);
    }

    /** @test */
    public function it_does_not_refund_if_campaign_reached_target()
    {
        // Create a campaign that reached target
        $successfulCampaign = Campaign::factory()
            ->reachedTarget()
            ->expired()
            ->create([
                'user_id' => $this->creator->id,
            ]);

        Backing::factory()->completed()->create([
            'user_id' => $this->backer1->id,
            'campaign_id' => $successfulCampaign->id,
            'amount' => 1000000,
        ]);

        RefundBackersJob::dispatch($successfulCampaign->id);

        // Assert: campaign still active (should be handled by DisburseCampaignJob)
        $successfulCampaign->refresh();
        $this->assertEquals('active', $successfulCampaign->status);

        // Assert: no refunds processed
        $this->assertEquals(0, Transaction::where('type', 'refund')->count());
    }

    /** @test */
    public function it_does_not_refund_if_campaign_not_active()
    {
        $this->campaign->update(['status' => 'failed']);

        Backing::factory()->completed()->create([
            'user_id' => $this->backer1->id,
            'campaign_id' => $this->campaign->id,
            'amount' => 1000000,
        ]);

        RefundBackersJob::dispatch($this->campaign->id);

        // Assert: no refunds
        $this->assertEquals(0, Transaction::where('type', 'refund')->count());
    }

    /** @test */
    public function it_handles_campaign_with_no_backings()
    {
        // Campaign with no completed backings
        RefundBackersJob::dispatch($this->campaign->id);

        // Assert: campaign status changed to failed
        $this->campaign->refresh();
        $this->assertEquals('failed', $this->campaign->status);

        // Assert: no refund transactions
        $this->assertEquals(0, Transaction::where('type', 'refund')->count());
    }

    /** @test */
    public function it_only_refunds_completed_backings()
    {
        // Create pending backing (not completed)
        Backing::factory()->create([
            'user_id' => $this->backer1->id,
            'campaign_id' => $this->campaign->id,
            'amount' => 1000000,
            'status' => 'pending',
        ]);

        RefundBackersJob::dispatch($this->campaign->id);

        // Assert: pending backing was NOT refunded
        $this->assertEquals(0, Transaction::where('type', 'refund')->count());

        // Assert: pending backing status unchanged
        $backing = Backing::where('status', 'pending')->first();
        $this->assertNotNull($backing);
    }

    /** @test */
    public function it_does_not_refund_twice()
    {
        Backing::factory()->completed()->create([
            'user_id' => $this->backer1->id,
            'campaign_id' => $this->campaign->id,
            'amount' => 1000000,
        ]);

        // Dispatch twice
        RefundBackersJob::dispatch($this->campaign->id);
        RefundBackersJob::dispatch($this->campaign->id);

        // Assert: only one refund transaction (second dispatch should bail early)
        $this->assertEquals(1, Transaction::where('type', 'refund')->count());
    }
}

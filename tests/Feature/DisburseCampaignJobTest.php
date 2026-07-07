<?php

namespace Tests\Feature;

use App\Jobs\DisburseCampaignJob;
use App\Models\Campaign;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisburseCampaignJobTest extends TestCase
{
    use RefreshDatabase;

    protected User $creator;
    protected Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        // Create creator with zero balance
        $this->creator = User::factory()->creator()->create([
            'balance' => 0,
        ]);

        // Create an active, expired campaign that has reached target
        $this->campaign = Campaign::factory()
            ->reachedTarget()
            ->expired()
            ->create([
                'user_id' => $this->creator->id,
            ]);
    }

    /** @test */
    public function it_disburses_funds_to_creator()
    {
        $collected = $this->campaign->collected_amount;
        $expectedFee = round($collected * 0.05, 2);
        $expectedAmount = $collected - $expectedFee;

        // Dispatch and process the job (QUEUE_CONNECTION=sync)
        DisburseCampaignJob::dispatch($this->campaign->id);

        // Assert: campaign status changed to success
        $this->campaign->refresh();
        $this->assertEquals('success', $this->campaign->status);

        // Assert: creator balance increased
        $this->creator->refresh();
        $this->assertEquals(round($expectedAmount, 2), round((float) $this->creator->balance, 2));

        // Assert: disbursement transaction created
        $disbursement = Transaction::where('type', 'disbursement')
            ->where('user_id', $this->creator->id)
            ->first();
        $this->assertNotNull($disbursement);
        $this->assertEquals($expectedAmount, $disbursement->amount);
        $this->assertEquals('success', $disbursement->status);

        // Assert: platform fee transaction created
        $fee = Transaction::where('type', 'platform_fee')
            ->where('user_id', $this->creator->id)
            ->first();
        $this->assertNotNull($fee);
        $this->assertEquals($expectedFee, $fee->amount);
        $this->assertEquals('success', $fee->status);

        // Assert: notification sent to creator
        $notification = UserNotification::where('user_id', $this->creator->id)
            ->where('type', 'campaign_success')
            ->first();
        $this->assertNotNull($notification);
        $this->assertStringContainsStringIgnoringCase('berhasil', $notification->title);
    }

    /** @test */
    public function it_does_not_disburse_if_campaign_not_active()
    {
        // Change campaign to success status
        $this->campaign->update(['status' => 'success']);

        DisburseCampaignJob::dispatch($this->campaign->id);

        // Assert: no new transactions created
        $this->assertEquals(0, Transaction::where('type', 'disbursement')->count());
    }

    /** @test */
    public function it_does_not_disburse_if_campaign_has_not_reached_target()
    {
        // Create campaign that hasn't reached target
        $campaign = Campaign::factory()
            ->expired()
            ->create([
                'user_id' => $this->creator->id,
                'target_amount' => 10000000,
                'collected_amount' => 5000000,
            ]);

        DisburseCampaignJob::dispatch($campaign->id);

        // Assert: campaign still active
        $campaign->refresh();
        $this->assertEquals('active', $campaign->status);

        // Assert: no disbursement
        $this->assertEquals(0, Transaction::where('type', 'disbursement')->count());
    }

    /** @test */
    public function it_does_not_disburse_if_campaign_not_found()
    {
        DisburseCampaignJob::dispatch(99999);

        // Assert: no transactions created
        $this->assertEquals(0, Transaction::count());
    }

    /** @test */
    public function it_handles_zero_collected_amount()
    {
        $campaign = Campaign::factory()
            ->expired()
            ->create([
                'user_id' => $this->creator->id,
                'target_amount' => 1000000,
                'collected_amount' => 0,
            ]);

        // Force target "reached" by setting target same as collected
        $campaign->update([
            'target_amount' => 0,
            'collected_amount' => 0,
        ]);

        DisburseCampaignJob::dispatch($campaign->id);

        $campaign->refresh();
        $this->assertEquals('success', $campaign->status);

        $this->creator->refresh();
        $this->assertEquals(0, $this->creator->balance);

        // Assert: fee is also zero
        $disbursement = Transaction::where('type', 'disbursement')->first();
        $this->assertEquals(0, $disbursement->amount);
    }

    /** @test */
    public function it_does_not_disburse_twice()
    {
        // Dispatch once
        DisburseCampaignJob::dispatch($this->campaign->id);

        // Try to dispatch again
        DisburseCampaignJob::dispatch($this->campaign->id);

        // Assert: only one disbursement transaction
        $this->assertEquals(1, Transaction::where('type', 'disbursement')->count());
    }
}

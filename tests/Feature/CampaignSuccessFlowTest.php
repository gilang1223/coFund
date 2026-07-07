<?php

namespace Tests\Feature;

use App\Models\Backing;
use App\Models\Campaign;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\BackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignSuccessFlowTest extends TestCase
{
    use RefreshDatabase;

    protected BackingService $backingService;
    protected User $creator;
    protected User $backer;
    protected Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        $this->backingService = app(BackingService::class);

        // Create creator with zero balance
        $this->creator = User::factory()->creator()->create([
            'balance' => 0,
        ]);

        // Create backer with sufficient balance
        $this->backer = User::factory()->backer()->create([
            'balance' => 5000000,
        ]);

        // Create a campaign that's almost at target:
        // target = 1.000.000, collected = 900.000 → needs 100.000 more to succeed
        $this->campaign = Campaign::factory()->create([
            'user_id' => $this->creator->id,
            'target_amount' => 1000000,
            'collected_amount' => 900000,
            'status' => 'active',
            'deadline' => now()->addDays(10), // still active, not expired
        ]);
    }

    /** @test */
    public function it_completes_backing_and_triggers_campaign_success()
    {
        // Arrange: create a pending backing that will push campaign to target
        $pendingBacking = Backing::factory()->create([
            'user_id' => $this->backer->id,
            'campaign_id' => $this->campaign->id,
            'amount' => 100000, // 900.000 + 100.000 = 1.000.000 = target
            'status' => 'pending',
        ]);

        // Create the associated escrow transaction
        Transaction::create([
            'user_id' => $this->backer->id,
            'backing_id' => $pendingBacking->id,
            'type' => 'payment',
            'amount' => 100000,
            'status' => 'pending',
            'reference' => 'BACK-TEST-' . uniqid(),
        ]);

        // Act: complete the backing → triggers success + disbursement
        $completedBacking = $this->backingService->complete($pendingBacking->id);

        // Assert: backing was completed successfully
        $this->assertNotNull($completedBacking);
        $this->assertEquals('completed', $completedBacking->status);

        // Assert: backer's balance deducted
        $this->backer->refresh();
        $this->assertEquals(4900000, $this->backer->balance); // 5.000.000 - 100.000

        // Assert: payment transaction updated to success
        $paymentTx = Transaction::where('type', 'payment')
            ->where('backing_id', $completedBacking->id)
            ->first();
        $this->assertNotNull($paymentTx);
        $this->assertEquals('success', $paymentTx->status);

        // Assert: campaign status changed to success
        $this->campaign->refresh();
        $this->assertEquals('success', $this->campaign->status);
        $this->assertEquals(1000000, $this->campaign->collected_amount);

        // --- ASSERT DISBURSEMENT TO CREATOR ---

        $expectedFee = round(1000000 * 0.05, 2); // 5% = 50.000
        $expectedCreatorAmount = 1000000 - $expectedFee; // 950.000

        // Assert: creator balance increased (after 5% fee)
        $this->creator->refresh();
        $this->assertEquals($expectedCreatorAmount, $this->creator->balance);

        // Assert: disbursement transaction created
        $disbursement = Transaction::where('type', 'disbursement')
            ->where('user_id', $this->creator->id)
            ->first();
        $this->assertNotNull($disbursement);
        $this->assertEquals($expectedCreatorAmount, $disbursement->amount);
        $this->assertEquals('success', $disbursement->status);

        // Assert: platform fee transaction created
        $platformFee = Transaction::where('type', 'platform_fee')
            ->where('user_id', $this->creator->id)
            ->first();
        $this->assertNotNull($platformFee);
        $this->assertEquals($expectedFee, $platformFee->amount);
        $this->assertEquals('success', $platformFee->status);

        // --- ASSERT NOTIFICATIONS ---

        // Assert: notification sent to CREATOR (via DisburseCampaignJob)
        $creatorNotification = UserNotification::where('user_id', $this->creator->id)
            ->where('type', 'campaign_success')
            ->first();
        $this->assertNotNull($creatorNotification);
        $this->assertStringContainsStringIgnoringCase('berhasil', $creatorNotification->title);

        // Assert: notification sent to BACKER (via BackingService::complete())
        $backerNotification = UserNotification::where('user_id', $this->backer->id)
            ->where('type', 'campaign_success')
            ->first();
        $this->assertNotNull($backerNotification);
        $this->assertStringContainsStringIgnoringCase('berhasil', $backerNotification->title);
    }

    /** @test */
    public function it_adjusts_backing_amount_when_it_exceeds_target()
    {
        // Campaign: target=1.000.000, collected=900.000
        // Backer backs with 200.000 → total would be 1.100.000 → excess 100.000
        $pendingBacking = Backing::factory()->create([
            'user_id' => $this->backer->id,
            'campaign_id' => $this->campaign->id,
            'amount' => 200000,
            'status' => 'pending',
        ]);

        Transaction::create([
            'user_id' => $this->backer->id,
            'backing_id' => $pendingBacking->id,
            'type' => 'payment',
            'amount' => 200000,
            'status' => 'pending',
            'reference' => 'BACK-TEST-' . uniqid(),
        ]);

        $completedBacking = $this->backingService->complete($pendingBacking->id);

        $this->assertNotNull($completedBacking);

        // Backing amount should be adjusted to 100.000 (excess 100.000 refunded)
        $this->assertEquals(100000, $completedBacking->amount);

        // Backer balance: 5.000.000 - 100.000 (adjusted) = 4.900.000
        $this->backer->refresh();
        $this->assertEquals(4900000, $this->backer->balance);

        // Campaign status = success
        $this->campaign->refresh();
        $this->assertEquals('success', $this->campaign->status);
        $this->assertEquals(1000000, $this->campaign->collected_amount);
    }

    /** @test */
    public function it_sends_notifications_to_all_completed_backers()
    {
        // Create a second backer who already donated
        $backer2 = User::factory()->backer()->create([
            'balance' => 3000000,
        ]);

        // Create an existing completed backing directly (simulates a previous donation)
        Backing::factory()->completed()->create([
            'user_id' => $backer2->id,
            'campaign_id' => $this->campaign->id,
            'amount' => 500000,
        ]);

        // Now complete the final backing that pushes campaign to target
        $pendingBacking = Backing::factory()->create([
            'user_id' => $this->backer->id,
            'campaign_id' => $this->campaign->id,
            'amount' => 100000,
            'status' => 'pending',
        ]);
        Transaction::create([
            'user_id' => $this->backer->id,
            'backing_id' => $pendingBacking->id,
            'type' => 'payment',
            'amount' => 100000,
            'status' => 'pending',
            'reference' => 'BACK-TEST-' . uniqid(),
        ]);

        $this->backingService->complete($pendingBacking->id);

        // Both backers should have received notifications
        $backer1Notifications = UserNotification::where('user_id', $this->backer->id)
            ->where('type', 'campaign_success')
            ->count();
        $this->assertEquals(1, $backer1Notifications);

        $backer2Notifications = UserNotification::where('user_id', $backer2->id)
            ->where('type', 'campaign_success')
            ->count();
        $this->assertEquals(1, $backer2Notifications);

        // Creator also got notification
        $creatorNotifications = UserNotification::where('user_id', $this->creator->id)
            ->where('type', 'campaign_success')
            ->count();
        $this->assertEquals(1, $creatorNotifications);
    }

    /** @test */
    public function it_does_not_disburse_twice_when_multiple_backings_trigger_success()
    {
        // First backing pushes campaign to target
        $backing1 = Backing::factory()->create([
            'user_id' => $this->backer->id,
            'campaign_id' => $this->campaign->id,
            'amount' => 100000,
            'status' => 'pending',
        ]);
        Transaction::create([
            'user_id' => $this->backer->id,
            'backing_id' => $backing1->id,
            'type' => 'payment',
            'amount' => 100000,
            'status' => 'pending',
            'reference' => 'BACK1-' . uniqid(),
        ]);

        $this->backingService->complete($backing1->id);

        // Campaign is now 'success' - try to complete another pending backing
        $backing2 = Backing::factory()->create([
            'user_id' => $this->backer->id,
            'campaign_id' => $this->campaign->id,
            'amount' => 50000,
            'status' => 'pending',
        ]);
        Transaction::create([
            'user_id' => $this->backer->id,
            'backing_id' => $backing2->id,
            'type' => 'payment',
            'amount' => 50000,
            'status' => 'pending',
            'reference' => 'BACK2-' . uniqid(),
        ]);

        $result = $this->backingService->complete($backing2->id);

        // Second backing should fail because campaign is no longer active
        $this->assertNull($result);

        // Only one disbursement should exist
        $this->assertEquals(1, Transaction::where('type', 'disbursement')->count());
        $this->assertEquals(1, Transaction::where('type', 'platform_fee')->count());
    }

    /** @test */
    public function it_handles_exact_target_reached()
    {
        // Target and collected are both set to hit exactly
        $this->campaign->update([
            'target_amount' => 1000000,
            'collected_amount' => 900000,
        ]);

        $pendingBacking = Backing::factory()->create([
            'user_id' => $this->backer->id,
            'campaign_id' => $this->campaign->id,
            'amount' => 100000, // exactly fills the gap
            'status' => 'pending',
        ]);
        Transaction::create([
            'user_id' => $this->backer->id,
            'backing_id' => $pendingBacking->id,
            'type' => 'payment',
            'amount' => 100000,
            'status' => 'pending',
            'reference' => 'BACK-EXACT-' . uniqid(),
        ]);

        $completed = $this->backingService->complete($pendingBacking->id);

        $this->assertNotNull($completed);
        $this->assertEquals(100000, $completed->amount);

        $this->campaign->refresh();
        $this->assertEquals('success', $this->campaign->status);
        $this->assertEquals(1000000, $this->campaign->collected_amount);

        // Creator gets 950.000 (1.000.000 - 5%)
        $this->creator->refresh();
        $this->assertEquals(950000, $this->creator->balance);

        // Backer pays exact amount
        $this->backer->refresh();
        $this->assertEquals(4900000, $this->backer->balance);
    }
}

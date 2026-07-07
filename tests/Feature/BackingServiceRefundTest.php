<?php

namespace Tests\Feature;

use App\Models\Backing;
use App\Models\Campaign;
use App\Models\CampaignTier;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackingServiceRefundTest extends TestCase
{
    use RefreshDatabase;

    protected BackingService $backingService;
    protected User $backer;
    protected Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        $this->backingService = app(BackingService::class);

        $this->backer = User::factory()->backer()->create([
            'balance' => 5000000,
        ]);

        $this->campaign = Campaign::factory()->create([
            'target_amount' => 10000000,
            'collected_amount' => 0,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_refunds_a_pending_backing()
    {
        // Create a pending backing with transaction
        $backing = Backing::factory()->create([
            'user_id' => $this->backer->id,
            'campaign_id' => $this->campaign->id,
            'amount' => 1000000,
            'status' => 'pending',
        ]);

        Transaction::create([
            'user_id' => $this->backer->id,
            'backing_id' => $backing->id,
            'type' => 'payment',
            'amount' => 1000000,
            'status' => 'pending',
            'reference' => 'BACK-TEST-' . uniqid(),
        ]);

        $refunded = $this->backingService->refund($backing->id);

        $this->assertNotNull($refunded);
        $this->assertEquals('refunded', $refunded->status);

        $this->backer->refresh();
        $this->assertEquals(6000000, $this->backer->balance); // 5000000 + 1000000

        $transaction = $backing->transaction()->first();
        $this->assertEquals('failed', $transaction->status);
    }

    /** @test */
    public function it_does_not_refund_a_completed_backing()
    {
        $backing = Backing::factory()->completed()->create([
            'user_id' => $this->backer->id,
            'campaign_id' => $this->campaign->id,
            'amount' => 1000000,
        ]);

        $result = $this->backingService->refund($backing->id);

        $this->assertNull($result);
    }

    /** @test */
    public function it_does_not_refund_an_already_refunded_backing()
    {
        $backing = Backing::factory()->refunded()->create([
            'user_id' => $this->backer->id,
            'campaign_id' => $this->campaign->id,
            'amount' => 1000000,
        ]);

        $result = $this->backingService->refund($backing->id);

        $this->assertNull($result);
    }

    /** @test */
    public function it_restores_tier_quota_on_refund()
    {
        $tier = CampaignTier::factory()->create([
            'campaign_id' => $this->campaign->id,
            'quota' => 10,
            'remaining_quota' => 3,
        ]);

        $backing = Backing::factory()->create([
            'user_id' => $this->backer->id,
            'campaign_id' => $this->campaign->id,
            'tier_id' => $tier->id,
            'amount' => 1000000,
            'status' => 'pending',
        ]);

        Transaction::create([
            'user_id' => $this->backer->id,
            'backing_id' => $backing->id,
            'type' => 'payment',
            'amount' => 1000000,
            'status' => 'pending',
            'reference' => 'BACK-TEST-' . uniqid(),
        ]);

        $this->backingService->refund($backing->id);

        $tier->refresh();
        $this->assertEquals(4, $tier->remaining_quota);
    }

    /** @test */
    public function it_returns_null_for_nonexistent_backing()
    {
        $result = $this->backingService->refund(99999);

        $this->assertNull($result);
    }

    /** @test */
    public function it_updates_transaction_status_on_refund()
    {
        $backing = Backing::factory()->create([
            'user_id' => $this->backer->id,
            'campaign_id' => $this->campaign->id,
            'amount' => 500000,
            'status' => 'pending',
        ]);

        $tx = Transaction::create([
            'user_id' => $this->backer->id,
            'backing_id' => $backing->id,
            'type' => 'payment',
            'amount' => 500000,
            'status' => 'pending',
            'reference' => 'BACK-TEST-' . uniqid(),
        ]);

        $this->backingService->refund($backing->id);

        $tx->refresh();
        $this->assertEquals('failed', $tx->status);
    }
}

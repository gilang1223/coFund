<?php

namespace Tests\Feature;

use App\Models\Backing;
use App\Models\Campaign;
use App\Models\CampaignTier;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionServiceRefundTest extends TestCase
{
    use RefreshDatabase;

    protected TransactionService $transactionService;
    protected User $creator;
    protected User $backer1;
    protected User $backer2;
    protected Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transactionService = app(TransactionService::class);

        $this->creator = User::factory()->creator()->create(['balance' => 0]);
        $this->backer1 = User::factory()->backer()->create(['balance' => 0]);
        $this->backer2 = User::factory()->backer()->create(['balance' => 0]);

        $this->campaign = Campaign::factory()
            ->expired()
            ->create([
                'user_id' => $this->creator->id,
                'target_amount' => 10000000,
                'collected_amount' => 3000000,
                'status' => 'active',
            ]);
    }

    /** @test */
    public function it_processes_full_refund_for_all_backers()
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

        $result = $this->transactionService->processRefunds($this->campaign->id);

        $this->assertTrue($result);

        // Campaign status should be failed
        $this->campaign->refresh();
        $this->assertEquals('failed', $this->campaign->status);

        // Backer balances restored
        $this->backer1->refresh();
        $this->assertEquals(1000000, $this->backer1->balance);
        $this->backer2->refresh();
        $this->assertEquals(2000000, $this->backer2->balance);

        // Backing statuses updated
        $backing1->refresh();
        $this->assertEquals('refunded', $backing1->status);
        $backing2->refresh();
        $this->assertEquals('refunded', $backing2->status);

        // Refund transactions created
        $refunds = Transaction::where('type', 'refund')->get();
        $this->assertCount(2, $refunds);
    }

    /** @test */
    public function it_restores_tier_quota_on_refund()
    {
        $tier = CampaignTier::factory()->create([
            'campaign_id' => $this->campaign->id,
            'quota' => 10,
            'remaining_quota' => 5,
        ]);

        Backing::factory()->completed()->create([
            'user_id' => $this->backer1->id,
            'campaign_id' => $this->campaign->id,
            'tier_id' => $tier->id,
            'amount' => 1000000,
        ]);

        $this->transactionService->processRefunds($this->campaign->id);

        $tier->refresh();
        $this->assertEquals(6, $tier->remaining_quota);
    }

    /** @test */
    public function it_does_not_refund_if_campaign_reached_target()
    {
        $campaign = Campaign::factory()
            ->reachedTarget()
            ->expired()
            ->create([
                'user_id' => $this->creator->id,
                'status' => 'active',
            ]);

        $result = $this->transactionService->processRefunds($campaign->id);

        $this->assertFalse($result);
        $this->assertEquals(0, Transaction::where('type', 'refund')->count());
    }

    /** @test */
    public function it_does_not_refund_if_campaign_not_active_or_failed()
    {
        $this->campaign->update(['status' => 'success']);

        $result = $this->transactionService->processRefunds($this->campaign->id);

        $this->assertFalse($result);
        $this->assertEquals(0, Transaction::where('type', 'refund')->count());
    }

    /** @test */
    public function it_can_refund_from_failed_status()
    {
        $this->campaign->update(['status' => 'failed']);

        Backing::factory()->completed()->create([
            'user_id' => $this->backer1->id,
            'campaign_id' => $this->campaign->id,
            'amount' => 500000,
        ]);

        $result = $this->transactionService->processRefunds($this->campaign->id);

        $this->assertTrue($result);

        $this->backer1->refresh();
        $this->assertEquals(500000, $this->backer1->balance);
    }

    /** @test */
    public function it_handles_campaign_with_no_completed_backings()
    {
        // Create only pending backings
        Backing::factory()->create([
            'user_id' => $this->backer1->id,
            'campaign_id' => $this->campaign->id,
            'amount' => 1000000,
            'status' => 'pending',
        ]);

        $result = $this->transactionService->processRefunds($this->campaign->id);

        $this->assertTrue($result);
        $this->campaign->refresh();
        $this->assertEquals('failed', $this->campaign->status);
        $this->assertEquals(0, Transaction::where('type', 'refund')->count());
    }

    /** @test */
    public function it_returns_false_for_nonexistent_campaign()
    {
        $result = $this->transactionService->processRefunds(99999);

        $this->assertFalse($result);
    }

    /** @test */
    public function settle_campaign_returns_refund_for_expired_unreached_campaign()
    {
        $this->campaign->update([
            'status' => 'active',
            'collected_amount' => 1000000,
            'target_amount' => 10000000,
        ]);

        Backing::factory()->completed()->create([
            'user_id' => $this->backer1->id,
            'campaign_id' => $this->campaign->id,
            'amount' => 1000000,
        ]);

        $result = $this->transactionService->settleCampaign($this->campaign->id);

        $this->assertNotNull($result);
        $this->assertEquals('refund', $result['action']);
        $this->assertEquals('failed', $result['campaign_status']);

        $this->backer1->refresh();
        $this->assertEquals(1000000, $this->backer1->balance);
    }

    /** @test */
    public function settle_campaign_returns_null_if_campaign_not_expired()
    {
        $campaign = Campaign::factory()->create([
            'user_id' => $this->creator->id,
            'status' => 'active',
            'deadline' => now()->addDays(10),
            'target_amount' => 10000000,
            'collected_amount' => 1000000,
        ]);

        $result = $this->transactionService->settleCampaign($campaign->id);

        $this->assertNull($result);
    }

    /** @test */
    public function settle_campaign_returns_null_if_campaign_not_active()
    {
        $this->campaign->update(['status' => 'draft']);

        $result = $this->transactionService->settleCampaign($this->campaign->id);

        $this->assertNull($result);
    }
}

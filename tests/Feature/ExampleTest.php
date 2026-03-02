<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\NotificationRecipient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_public_complaint_form(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Sampaikan Keluhan Anda');
    }

    public function test_authenticated_user_can_access_dashboard_and_complaints(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/complaints')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/complaints/create')
            ->assertStatus(200);
    }

    public function test_capa_workflow_qa_to_manager_to_closed(): void
    {
        $qa = User::factory()->create([
            'role' => User::ROLE_QA,
            'is_active' => true,
        ]);
        $manager = User::factory()->create([
            'role' => User::ROLE_MANAGER,
            'is_active' => true,
        ]);

        $complaint = Complaint::query()->create([
            'ticket_number' => 'CMP-20260302-TST1',
            'customer_name' => 'Test Customer',
            'complaint_channel' => 'Phone',
            'complaint_date' => now()->toDateString(),
            'severity' => 'High',
            'status' => 'Investigating',
            'description' => 'Test complaint description',
        ]);

        $this->actingAs($qa)
            ->patch(route('complaints.capa.submit', $complaint), [
                'capa_root_cause' => 'Akar masalah',
                'capa_corrective_action' => 'Tindakan korektif',
                'capa_preventive_action' => 'Tindakan preventif',
                'capa_due_date' => now()->addDays(3)->toDateString(),
            ])->assertRedirect();

        $complaint->refresh();
        $this->assertSame(Complaint::CAPA_STATUS_SUBMITTED, $complaint->capa_status);

        $this->actingAs($manager)
            ->patch(route('complaints.capa.approve', $complaint), [])
            ->assertRedirect();

        $complaint->refresh();
        $this->assertSame(Complaint::CAPA_STATUS_APPROVED, $complaint->capa_status);

        $this->actingAs($qa)
            ->patch(route('complaints.capa.close', $complaint), [
                'resolution_summary' => 'Selesai ditindaklanjuti.',
            ])->assertRedirect();

        $complaint->refresh();
        $this->assertSame('Closed', $complaint->status);
        $this->assertSame(Complaint::CAPA_STATUS_CLOSED, $complaint->capa_status);
    }

    public function test_only_admin_can_access_master_data_page(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);
        $qa = User::factory()->create([
            'role' => User::ROLE_QA,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('master.index'))
            ->assertStatus(200);

        $this->actingAs($qa)
            ->get(route('master.index'))
            ->assertStatus(403);
    }

    public function test_authenticated_user_can_open_guide(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_VIEWER,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('guide.index'))
            ->assertStatus(200);
    }

    public function test_admin_can_manage_notification_recipient_master(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('master.notification_recipients.store'), [
                'name' => 'QA Alert',
                'email' => 'qa.alert@example.com',
                'is_active' => 1,
            ])->assertRedirect();

        $this->assertDatabaseHas('notification_recipients', [
            'email' => 'qa.alert@example.com',
            'is_active' => 1,
        ]);

        $recipient = NotificationRecipient::query()->where('email', 'qa.alert@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('master.notification_recipients.update', $recipient), [
                'name' => 'QA Alert Updated',
                'email' => 'qa.alert@example.com',
                'is_active' => 0,
            ])->assertRedirect();

        $this->assertDatabaseHas('notification_recipients', [
            'id' => $recipient->id,
            'is_active' => 0,
        ]);
    }
}

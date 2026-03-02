<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\ComplaintSeverity;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'CRM Admin',
                'email' => 'admin@crm.local',
                'phone' => '628111111111',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ],
            [
                'name' => 'QA Supervisor',
                'email' => 'qa@crm.local',
                'phone' => '628122222222',
                'password' => 'password',
                'role' => User::ROLE_QA,
                'is_active' => true,
            ],
            [
                'name' => 'Manager Area',
                'email' => 'manager@crm.local',
                'phone' => '628133333333',
                'password' => 'password',
                'role' => User::ROLE_MANAGER,
                'is_active' => true,
            ],
            [
                'name' => 'Customer Service',
                'email' => 'cs@crm.local',
                'phone' => '628144444444',
                'password' => 'password',
                'role' => User::ROLE_CS,
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        Brand::query()->upsert([
            ['name' => 'Nusantara Mild', 'code' => 'NMILD', 'description' => 'Sigaret Kretek Mesin'],
            ['name' => 'Rajawali Filter', 'code' => 'RFLTR', 'description' => 'Sigaret Kretek Tangan'],
            ['name' => 'Pratama Bold', 'code' => 'PBOLD', 'description' => 'SKM premium segment'],
        ], ['code'], ['name', 'description']);

        ComplaintCategory::query()->upsert([
            ['name' => 'Kualitas Produk', 'sla_label' => '24 jam', 'target_resolution_hours' => 24],
            ['name' => 'Kemasan Rusak', 'sla_label' => '48 jam', 'target_resolution_hours' => 48],
            ['name' => 'Distribusi', 'sla_label' => '72 jam', 'target_resolution_hours' => 72],
            ['name' => 'Layanan Sales', 'sla_label' => '48 jam', 'target_resolution_hours' => 48],
            ['name' => 'Legal & Kepatuhan', 'sla_label' => '24 jam', 'target_resolution_hours' => 24],
        ], ['name'], ['sla_label', 'target_resolution_hours']);

        ComplaintSeverity::query()->upsert([
            ['name' => 'Low', 'sort_order' => 1, 'is_active' => true],
            ['name' => 'Medium', 'sort_order' => 2, 'is_active' => true],
            ['name' => 'High', 'sort_order' => 3, 'is_active' => true],
            ['name' => 'Critical', 'sort_order' => 4, 'is_active' => true],
        ], ['name'], ['sort_order', 'is_active']);

        $customers = [
            ['name' => 'Toko Sumber Rejeki', 'phone' => '08123456789', 'email' => 'sumberrejeki@example.com', 'city' => 'Jakarta', 'address' => 'Jl. Melati No. 10', 'is_active' => true],
            ['name' => 'Distributor Maju Jaya', 'phone' => '08129876543', 'email' => 'majujaya@example.com', 'city' => 'Bandung', 'address' => 'Jl. Anggrek No. 7', 'is_active' => true],
        ];

        foreach ($customers as $customerData) {
            Customer::query()->updateOrCreate(
                ['name' => $customerData['name']],
                $customerData
            );
        }

        if (Complaint::query()->count() === 0) {
            $brand = Brand::query()->first();
            $category = ComplaintCategory::query()->first();
            $customer = Customer::query()->first();

            Complaint::query()->create([
                'ticket_number' => 'CMP-'.now()->format('Ymd').'-SEED',
                'customer_id' => $customer?->id,
                'customer_name' => $customer?->name ?: 'Toko Sumber Rejeki',
                'customer_phone' => $customer?->phone ?: '08123456789',
                'customer_email' => $customer?->email,
                'brand_id' => $brand?->id,
                'complaint_category_id' => $category?->id,
                'complaint_channel' => 'Sales Representative',
                'production_code' => 'BATCH-A12-01',
                'complaint_date' => now()->toDateString(),
                'severity' => 'High',
                'status' => 'Investigating',
                'assigned_to' => 'QA Supervisor',
                'target_resolution_date' => now()->addDays(2)->toDateString(),
                'description' => 'Ditemukan batang rokok patah dalam beberapa bungkus.',
            ]);
        }
    }
}
